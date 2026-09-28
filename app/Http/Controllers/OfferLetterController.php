<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\OfferLetter;
use App\Models\OfferLetterSetting;
use App\Models\OfferLetterTemplate;
use App\Models\OfferLetterSignature;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OfferLetterController extends Controller
{
    // ---- Sent-letter list ------------------------------------------------
    public function index(Request $request)
    {
        $letters = OfferLetter::with('creator')->latest()->paginate(25);
        return view('admin.offer_letters.index', compact('letters'));
    }

    // ---- Create / compose ------------------------------------------------
    public function create(Request $request)
    {
        $filter = $request->input('pool', 'selected'); // selected | approved
        $q = JobApplication::with(['job', 'candidate', 'candidateUser']);
        if ($filter === 'approved') {
            $q->where('status', 'Approved');
        } else {
            $filter = 'selected';
            $q->where('hiring_status', 'Selected');
        }
        $applications = $q->latest()->limit(500)->get()->map(function ($a) {
            return [
                'id'    => $a->id,
                'label' => trim(($a->candidate_name ?: 'Candidate')) . ' — ' . (optional($a->job)->title ?? 'Role')
                           . ' @ ' . (optional($a->job)->company_name ?? '—'),
            ];
        });

        $templates = OfferLetterTemplate::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.offer_letters.create', [
            'applications' => $applications,
            'templates'    => $templates,
            'filter'       => $filter,
        ]);
    }

    /** Step 2: load the chosen template filled with the candidate's data into the editor. */
    public function compose(Request $request)
    {
        $data = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'template_id'        => 'required|exists:offer_letter_templates,id',
        ]);
        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])->findOrFail($data['job_application_id']);
        $tpl = OfferLetterTemplate::findOrFail($data['template_id']);

        $tokens = $this->tokens($app);
        $body = strtr($tpl->body_html, $tokens);
        $subject = strtr((string) ($tpl->subject ?: 'Offer of Employment'), $tokens);

        return view('admin.offer_letters.compose', [
            'app'        => $app,
            'template'   => $tpl,
            'bodyHtml'   => $body,
            'subject'    => $subject,
            'tokens'     => $tokens,
            'signatures' => OfferLetterSignature::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /** Final: generate the PDF, save, email candidate + admin. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'template_id'        => 'nullable|exists:offer_letter_templates,id',
            'subject'            => 'required|string|max:255',
            'body_html'          => 'required|string',
            'signature_id'       => 'nullable|exists:offer_letter_signatures,id',
            'action'             => 'required|in:send,draft',
        ]);

        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])->findOrFail($data['job_application_id']);
        $email = $app->candidate->email ?? $app->candidateUser->email ?? null;

        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;

        $sigBlock = $this->signatureBlockHtml($sig);
        $finalBody = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;

        $pdf = $this->renderPdf($data['subject'], $finalBody);
        $fileName = 'offer-letters/offer_' . $app->id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($fileName, $pdf);

        $letter = OfferLetter::create([
            'job_application_id' => $app->id,
            'candidate_id'       => $app->candidate_id,
            'candidate_user_id'  => $app->candidate_user_id,
            'template_id'        => $data['template_id'] ?? null,
            'candidate_name'     => $app->candidate_name,
            'candidate_email'    => $email,
            'job_title'          => optional($app->job)->title,
            'company_name'       => optional($app->job)->company_name,
            'signatory_name'         => $sig?->name,
            'signatory_designation'  => $sig?->designation,
            'signatory_signature_path' => $sig?->signature_path,
            'subject'            => $data['subject'],
            'body_html'          => $finalBody,
            'pdf_path'           => $fileName,
            'status'             => 'draft',
            'created_by'         => Auth::id(),
        ]);

        if ($data['action'] === 'send') {
            if (!$email) {
                return redirect()->route('admin.offer-letters.index')
                    ->with('error', 'Offer letter generated but the candidate has no email on file, so it was not sent.');
            }
            $this->sendEmails($letter, $pdf, $email);
            $letter->update(['status' => 'sent', 'sent_at' => now()]);
            return redirect()->route('admin.offer-letters.index')->with('success', 'Offer letter sent to ' . $email . '.');
        }

        return redirect()->route('admin.offer-letters.index')->with('success', 'Offer letter saved as draft.');
    }

    /** Live preview of the current draft as an inline PDF (no save, no email). */
    public function previewPdf(Request $request)
    {
        $data = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'subject'            => 'required|string|max:255',
            'body_html'          => 'required|string',
            'signature_id'       => 'nullable|exists:offer_letter_signatures,id',
        ]);
        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;
        $sigBlock = $this->signatureBlockHtml($sig);
        $body = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;
        $pdf = $this->renderPdf($data['subject'], $body);
        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="offer-preview.pdf"',
        ]);
    }

    public function download(OfferLetter $offerLetter)
    {
        abort_unless($offerLetter->pdf_path && Storage::disk('public')->exists($offerLetter->pdf_path), 404);
        return Storage::disk('public')->download($offerLetter->pdf_path,
            'Offer_' . str_replace(' ', '_', (string) $offerLetter->candidate_name) . '.pdf');
    }

    // ---- Templates -------------------------------------------------------
    public function templates()
    {
        $templates = OfferLetterTemplate::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.offer_letters.templates', compact('templates'));
    }

    public function templateForm(?OfferLetterTemplate $offerTemplate = null)
    {
        return view('admin.offer_letters.template_form', ['tpl' => $offerTemplate]);
    }

    public function templateSave(Request $request, ?OfferLetterTemplate $offerTemplate = null)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'subject'   => 'nullable|string|max:255',
            'body_html' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($offerTemplate && $offerTemplate->exists) {
            $offerTemplate->update($data);
        } else {
            OfferLetterTemplate::create($data);
        }
        return redirect()->route('admin.offer-letters.templates')->with('success', 'Template saved.');
    }

    public function templateDelete(OfferLetterTemplate $offerTemplate)
    {
        $offerTemplate->delete();
        return back()->with('success', 'Template deleted.');
    }

    // ---- Settings (letterhead, signature) --------------------------------
    public function settings()
    {
        return view('admin.offer_letters.settings', ['settings' => OfferLetterSetting::current()]);
    }

    public function settingsUpdate(Request $request)
    {
        $s = OfferLetterSetting::current();
        $data = $request->validate([
            'director_name'        => 'nullable|string|max:255',
            'director_designation' => 'nullable|string|max:255',
            'company_address'      => 'nullable|string|max:2000',
            'company_footer'       => 'nullable|string|max:2000',
            'signature'            => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'logo'                 => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);
        $update = [
            'director_name'        => $data['director_name'] ?? $s->director_name,
            'director_designation' => $data['director_designation'] ?? $s->director_designation,
            'company_address'      => $data['company_address'] ?? $s->company_address,
            'company_footer'       => $data['company_footer'] ?? $s->company_footer,
        ];
        if ($request->hasFile('signature')) {
            $update['signature_path'] = $request->file('signature')->store('offer-branding', 'public');
        }
        if ($request->hasFile('logo')) {
            $update['logo_path'] = $request->file('logo')->store('offer-branding', 'public');
        }
        $s->update($update);
        return back()->with('success', 'Offer letter branding updated.');
    }

    // ---- Signature manager ----------------------------------------------
    public function signatures()
    {
        $signatures = OfferLetterSignature::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.offer_letters.signatures', compact('signatures'));
    }

    public function signatureForm(?OfferLetterSignature $offerSignature = null)
    {
        return view('admin.offer_letters.signature_form', ['sig' => $offerSignature]);
    }

    public function signatureSave(Request $request, ?OfferLetterSignature $offerSignature = null)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
            'signature'   => ($offerSignature && $offerSignature->exists ? 'nullable' : 'nullable') . '|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        $save = [
            'name'        => $data['name'],
            'designation' => $data['designation'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ];
        if ($request->hasFile('signature')) {
            $save['signature_path'] = $request->file('signature')->store('offer-branding', 'public');
        }
        if ($offerSignature && $offerSignature->exists) {
            $offerSignature->update($save);
        } else {
            OfferLetterSignature::create($save);
        }
        return redirect()->route('admin.offer-letters.signatures')->with('success', 'Signature saved.');
    }

    public function signatureDelete(OfferLetterSignature $offerSignature)
    {
        $offerSignature->delete();
        return back()->with('success', 'Signature deleted.');
    }

    // ---- Helpers ---------------------------------------------------------
    private function tokens(JobApplication $app): array
    {
        $s = OfferLetterSetting::current();
        $ctc = $app->final_ctc ? '₹' . number_format((float) $app->final_ctc, 0) : '';
        return [
            '{{candidate_name}}'   => $app->candidate_name ?: 'Candidate',
            '{{job_title}}'        => optional($app->job)->title ?? '',
            '{{designation}}'      => optional($app->job)->title ?? '',
            '{{company}}'          => optional($app->job)->company_name ?? '',
            '{{company_name}}'     => optional($app->job)->company_name ?? '',
            '{{location}}'         => optional($app->job)->location ?? '',
            '{{ctc}}'              => $ctc,
            '{{joining_date}}'     => $app->joining_date ? $app->joining_date->format('d M Y') : '________',
            '{{date}}'             => now()->format('d M Y'),
            '{{director_name}}'    => $s->director_name ?: 'Aman Yadav',
            '{{director_designation}}' => $s->director_designation ?: 'Director',
        ];
    }

    private function dataUri(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }
        $bytes = Storage::disk('public')->get($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'svg' ? 'image/svg+xml' : ($ext === 'png' ? 'image/png' : 'image/jpeg');
        return 'data:' . $mime . ';base64,' . base64_encode($bytes);
    }

    private function signatureBlockHtml(?OfferLetterSignature $sig): string
    {
        $imgUri = $sig ? $this->dataUri($sig->signature_path) : null;
        $name = $sig?->name ?: '';
        $desig = $sig?->designation ?: '';
        $img = $imgUri ? '<img src="' . $imgUri . '" style="height:54px;" alt="signature"><br>' : '';
        return '<div class="sigblock" style="margin-top:26px;">' . $img
            . '<div style="font-weight:bold;">Authorised Signatory</div>'
            . 'Name: ' . e($name) . '<br>'
            . ($desig ? 'Designation: ' . e($desig) . '<br>' : '')
            . 'Date: ' . now()->format('d/m/Y')
            . '</div>';
    }

    private function renderPdf(string $subject, string $bodyHtml): string
    {
        $s = OfferLetterSetting::current();
        $html = view('admin.offer_letters.pdf', [
            'subject'  => $subject,
            'bodyHtml' => $bodyHtml,
            'settings' => $s,
            'logo'     => $this->dataUri($s->logo_path),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function sendEmails(OfferLetter $letter, string $pdf, string $email): void
    {
        $fname = 'Offer_' . preg_replace('/\s+/', '_', (string) $letter->candidate_name) . '.pdf';
        try {
            Mail::send('emails.offer-letter', ['letter' => $letter], function ($m) use ($letter, $email, $pdf, $fname) {
                $m->to($email, $letter->candidate_name)
                  ->subject($letter->subject ?: 'Your Offer Letter — SimplyHiree')
                  ->attachData($pdf, $fname, ['mime' => 'application/pdf']);
            });
        } catch (\Throwable $e) { report($e); }

        try {
            $admins = array_filter([config('mail.from.address'), 'simplyhire1@gmail.com']);
            Mail::send('emails.offer-letter-admin', ['letter' => $letter], function ($m) use ($letter, $admins, $pdf, $fname) {
                $m->to($admins)->subject('Offer letter sent: ' . $letter->candidate_name . ' — ' . $letter->job_title)
                  ->attachData($pdf, $fname, ['mime' => 'application/pdf']);
            });
        } catch (\Throwable $e) { report($e); }
    }
}

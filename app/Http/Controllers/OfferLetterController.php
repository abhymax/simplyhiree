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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class OfferLetterController extends Controller
{
    // ---- Sent-letter list ------------------------------------------------
    public function index(Request $request)
    {
        $base = OfferLetter::query();
        $counts = [
            'all'   => (clone $base)->count(),
            'sent'  => (clone $base)->where('status', 'sent')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
        ];
        $companies = (clone $base)->whereNotNull('company_name')->where('company_name', '!=', '')
            ->distinct()->orderBy('company_name')->pluck('company_name');
        $roles = (clone $base)->whereNotNull('job_title')->where('job_title', '!=', '')
            ->distinct()->orderBy('job_title')->pluck('job_title');

        $q = OfferLetter::with('creator');
        if ($request->filled('company')) { $q->where('company_name', $request->input('company')); }
        if ($request->filled('role'))    { $q->where('job_title', $request->input('role')); }
        if ($request->filled('status'))  { $q->where('status', $request->input('status')); }
        if ($request->filled('search')) {
            $sv = $request->input('search');
            $q->where(fn ($x) => $x->where('candidate_name', 'like', "%{$sv}%")
                ->orWhere('candidate_email', 'like', "%{$sv}%")
                ->orWhere('serial_no', 'like', "%{$sv}%"));
        }
        $letters = $q->latest()->paginate(25)->withQueryString();

        return view('admin.offer_letters.index', compact('letters', 'counts', 'companies', 'roles'));
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

        $annual = $app->final_ctc ? (float) $app->final_ctc : null;
        $fields = [
            // Shown so the operator can see it; the definitive number is
            // reserved when the letter is saved, so two people composing at
            // once cannot end up sharing one.
            'ref_no'       => OfferLetter::reserveSerial(),
            'department'   => optional($app->job)->department ?? '',
            'reporting_to' => '',
            'monthly_ctc'  => $annual ? $this->inr($annual / 12) : '',
            'annual_ctc'   => $annual ? $this->inr($annual) : '',
        ];
        // Wrap each value in a tagged span. The compose screen's Offer Details
        // inputs write into these spans, so what the operator types ends up in
        // the saved body. Plain text here meant typing changed nothing.
        $bindable = fn (string $field, string $value) =>
            '<span data-field="' . $field . '">' . e($value !== '' ? $value : '________') . '</span>';

        $fieldTokens = [
            '{{ref_no}}'       => $bindable('ref_no', (string) $fields['ref_no']),
            '{{department}}'   => $bindable('department', (string) $fields['department']),
            '{{reporting_to}}' => $bindable('reporting_to', (string) $fields['reporting_to']),
            '{{monthly_ctc}}'  => $bindable('monthly_ctc', (string) $fields['monthly_ctc']),
            '{{annual_ctc}}'   => $bindable('annual_ctc', (string) $fields['annual_ctc']),
        ];

        $monthlyForBreakup = $annual ? $annual / 12 : 0;
        $tokens = array_merge($this->tokens($app), $fieldTokens, [
            '{{ctc_breakup}}' => $this->ctcBreakupTable($monthlyForBreakup),
        ]);
        $body = strtr($this->cleanPastedHtml($tpl->body_html), $tokens);
        $subject = strtr((string) ($tpl->subject ?: 'Offer of Employment'), $tokens);

        return view('admin.offer_letters.compose', [
            'app'        => $app,
            'template'   => $tpl,
            'bodyHtml'   => $body,
            'subject'    => $subject,
            'heading'    => $tpl->default_heading ?: 'OFFER LETTER',
            'tokens'     => $tokens,
            'fields'     => $fields,
            'signatures' => OfferLetterSignature::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Reopen a saved draft in the compose screen.
     *
     * The stored body already has the signature block substituted, so swap it
     * back to the {{signature_block}} token. Otherwise saving again would
     * append a second signature, and the signatory could never be changed.
     */
    public function edit(OfferLetter $offerLetter)
    {
        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->findOrFail($offerLetter->job_application_id);

        $body = $this->signatureBlockToToken((string) $offerLetter->body_html);

        // Re-read the structured values straight from the body's tagged spans,
        // so the Offer Details panel shows what was saved.
        $fields = [];
        foreach (['ref_no', 'department', 'reporting_to', 'monthly_ctc', 'annual_ctc', 'joining_date'] as $f) {
            preg_match('/<span data-field="' . $f . '">(.*?)<\/span>/s', $body, $m);
            $value = isset($m[1]) ? trim(strip_tags($m[1])) : '';
            $fields[$f] = ($value === '' || str_starts_with($value, '____')) ? '' : $value;
        }

        $template = $offerLetter->template_id
            ? OfferLetterTemplate::find($offerLetter->template_id)
            : OfferLetterTemplate::where('is_active', true)->first();

        return view('admin.offer_letters.compose', [
            'app'         => $app,
            'template'    => $template,
            'letter'      => $offerLetter,
            'isRevision'  => $offerLetter->status === 'sent',
            'joiningDate' => $app->joining_date?->format('Y-m-d'),
            'bodyHtml'   => $body,
            'subject'    => $offerLetter->subject,
            'heading'    => $offerLetter->heading ?: 'OFFER LETTER',
            'tokens'     => [],
            'fields'     => $fields,
            'signatures' => OfferLetterSignature::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /** Save changes to a draft, optionally sending it. */
    public function update(Request $request, OfferLetter $offerLetter)
    {
        $wasSent = $offerLetter->status === 'sent';

        $data = $request->validate([
            'subject'      => 'required|string|max:255',
            'heading'      => 'nullable|string|max:120',
            'body_html'    => 'required|string',
            'signature_id' => 'nullable|exists:offer_letter_signatures,id',
            'joining_date' => 'nullable|date',
            'action'       => 'required|in:send,draft',
        ]);

        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->findOrFail($offerLetter->job_application_id);
        $email = $app->candidate->email ?? $app->candidateUser->email ?? null;

        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;

        $data['body_html'] = $this->cleanPastedHtml($data['body_html']);

        // A revision keeps the letter's own number; it is the same document.
        if ($offerLetter->serial_no) {
            $data['body_html'] = $this->stampSerial($data['body_html'], $offerLetter->serial_no);
        }

        // Rescheduling: the application carries the real joining date, the
        // letter has it written into the text, so both have to move together.
        $oldJoining  = $app->joining_date;
        $newJoining  = filled($data['joining_date'] ?? null) ? Carbon::parse($data['joining_date']) : null;
        $rescheduled = $newJoining && (!$oldJoining || !$oldJoining->isSameDay($newJoining));
        $replacements = 0;

        if ($rescheduled) {
            [$data['body_html'], $replacements] =
                $this->replaceJoiningDate($data['body_html'], $oldJoining, $newJoining);
            $app->forceFill(['joining_date' => $newJoining])->save();
        }

        $sigBlock = $this->signatureBlockHtml($sig);
        $finalBody = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;

        $heading = trim((string) ($data['heading'] ?? '')) ?: 'OFFER LETTER';
        $pdf = $this->renderPdf($data['subject'], $finalBody, $heading, $offerLetter->serial_no);

        // Replace the previous PDF rather than leaving orphans behind.
        if ($offerLetter->pdf_path) {
            Storage::disk('public')->delete($offerLetter->pdf_path);
        }
        $fileName = 'offer-letters/offer_' . $app->id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($fileName, $pdf);

        $offerLetter->update([
            'subject'                  => $data['subject'],
            'heading'                  => $heading,
            'body_html'                => $finalBody,
            'pdf_path'                 => $fileName,
            'signatory_name'           => $sig?->name,
            'signatory_designation'    => $sig?->designation,
            'signatory_signature_path' => $sig?->signature_path,
        ]);

        $note = '';
        if ($rescheduled) {
            \App\Models\ApplicationActivity::log(
                $app,
                'offer_joining_date_changed',
                'Joining date moved from ' . ($oldJoining?->format('d M Y') ?? 'not set')
                    . ' to ' . $newJoining->format('d M Y') . ($wasSent ? ' (letter already sent)' : '')
            );

            $note = ' Joining date is now ' . $newJoining->format('d M Y') . '.';
            if ($replacements === 0) {
                $note .= ' The old date was not found in the letter text, so check the wording before sending.';
            }
        }

        if ($data['action'] === 'send') {
            if (!$email) {
                return redirect()->route('admin.offer-letters.index')
                    ->with('error', 'Changes saved, but the candidate has no email on file, so nothing was sent.' . $note);
            }
            $this->sendEmails($offerLetter->fresh(), $pdf, $email);
            $offerLetter->update(['status' => 'sent', 'sent_at' => now()]);

            return redirect()->route('admin.offer-letters.index')->with('success',
                ($wasSent ? 'Revised offer letter re-sent to ' : 'Offer letter sent to ') . $email . '.' . $note);
        }

        return redirect()->route('admin.offer-letters.index')->with('success',
            ($wasSent
                ? 'Letter revised. The candidate still holds the version already emailed to them, so use Save & resend when you want them to have this one.'
                : 'Draft updated.') . $note);
    }

    /** Final: generate the PDF, save, email candidate + admin. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'template_id'        => 'nullable|exists:offer_letter_templates,id',
            'subject'            => 'required|string|max:255',
            'heading'            => 'nullable|string|max:120',
            'body_html'          => 'required|string',
            'signature_id'       => 'nullable|exists:offer_letter_signatures,id',
            'action'             => 'required|in:send,draft',
        ]);

        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])->findOrFail($data['job_application_id']);
        $email = $app->candidate->email ?? $app->candidateUser->email ?? null;

        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;

        $data['body_html'] = $this->cleanPastedHtml($data['body_html']);
        $sigBlock = $this->signatureBlockHtml($sig);
        $finalBody = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;

        $heading = trim((string)($data['heading'] ?? '')) ?: 'OFFER LETTER';

        // The number is allocated and the row written inside one transaction.
        // reserveSerial() locks within it, and InnoDB keeps that lock until the
        // outermost commit, so a save running at the same moment waits until
        // this row exists rather than being handed the same number.
        $fileName = 'offer-letters/offer_' . $app->id . '_' . now()->format('Ymd_His') . '.pdf';

        [$letter, $pdf, $finalBody] = DB::transaction(function () use (
            $data, $app, $email, $sig, $heading, $finalBody, $fileName
        ) {
            $serial = OfferLetter::reserveSerial();
            $finalBody = $this->stampSerial($finalBody, $serial);

            $pdf = $this->renderPdf($data['subject'], $finalBody, $heading, $serial);

            $letter = OfferLetter::create([
            'serial_no'          => $serial,
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
            'heading'            => $heading,
            'body_html'          => $finalBody,
            'pdf_path'           => $fileName,
            'status'             => 'draft',
            'created_by'         => Auth::id(),
            ]);

            return [$letter, $pdf, $finalBody];
        });

        // Written after the row is committed, so a rolled back save leaves no file.
        Storage::disk('public')->put($fileName, $pdf);

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
            'heading'            => 'nullable|string|max:120',
            'body_html'          => 'required|string',
            'signature_id'       => 'nullable|exists:offer_letter_signatures,id',
        ]);
        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;
        $data['body_html'] = $this->cleanPastedHtml($data['body_html']);
        $sigBlock = $this->signatureBlockHtml($sig);
        $body = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;
        $pdf = $this->renderPdf($data['subject'], $body, trim((string)($data['heading'] ?? '')) ?: 'OFFER LETTER',
            OfferLetter::reserveSerial());
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
            'default_heading' => 'nullable|string|max:120',
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
    /**
     * Stand-in values so a template can be previewed on its own, before any
     * candidate is attached to it.
     */
    private function sampleTokens(): array
    {
        $s = OfferLetterSetting::current();

        return [
            '{{candidate_name}}'       => 'Satyam Bansal',
            '{{job_title}}'            => 'Senior Software Engineer',
            '{{designation}}'          => 'Senior Software Engineer',
            '{{company}}'              => 'Acme Technologies Pvt Ltd',
            '{{company_name}}'         => 'Acme Technologies Pvt Ltd',
            '{{location}}'             => 'Bengaluru',
            '{{ctc}}'                  => '₹12,00,000',
            '{{joining_date}}'         => now()->addWeeks(3)->format('d M Y'),
            '{{date}}'                 => now()->format('d M Y'),
            '{{director_name}}'        => $s->director_name ?: 'Aman Yadav',
            '{{director_designation}}' => $s->director_designation ?: 'Director',
        ];
    }

    /**
     * Preview a template by itself. Unlike previewPdf() there is no application
     * behind it, so the tokens are filled with sample values and the body is
     * taken from whatever is in the editor, saved or not.
     */
    public function templatePreviewPdf(Request $request)
    {
        $data = $request->validate([
            'subject'      => 'nullable|string|max:255',
            'heading'      => 'nullable|string|max:120',
            'body_html'    => 'required|string',
            'signature_id' => 'nullable|exists:offer_letter_signatures,id',
        ]);

        $sig = !empty($data['signature_id'])
            ? OfferLetterSignature::find($data['signature_id'])
            : OfferLetterSignature::where('is_active', true)->orderBy('sort_order')->orderBy('name')->first();

        $tokens = array_merge($this->sampleTokens(), [
            '{{ctc_breakup}}' => $this->ctcBreakupTable(100000.0),
        ]);

        $body = strtr($this->cleanPastedHtml($data['body_html']), $tokens);

        $sigBlock = $this->signatureBlockHtml($sig);
        $body = str_contains($body, '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $body)
            : $body . $sigBlock;

        $subject = strtr(trim((string) ($data['subject'] ?? '')) ?: 'Offer of Employment', $tokens);
        $heading = trim((string) ($data['heading'] ?? '')) ?: 'OFFER LETTER';

        $pdf = $this->renderPdf($subject, $body, $heading, 'SHPL/HR/OL/' . date('Y') . '/XXXX');

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="template-preview.pdf"',
        ]);
    }

    /**
     * Move the joining date inside an already-written letter.
     *
     * The date is substituted from {{joining_date}} when the letter is composed,
     * so by the time it is sent it is ordinary text. A tagged span is rewritten
     * directly; otherwise the old date is matched in the formats the templates
     * use. Returns the body and how many occurrences were changed, so the
     * caller can warn when a letter words the date some other way.
     *
     * @return array{0: string, 1: int}
     */
    private function replaceJoiningDate(string $html, ?Carbon $old, Carbon $new): array
    {
        $replacements = 0;
        $newText = $new->format('d M Y');

        // A tagged span is unambiguous, so prefer it.
        $html = preg_replace(
            '/(<span data-field="joining_date">)(.*?)(<\/span>)/s',
            '$1' . $newText . '$3',
            $html,
            -1,
            $spanHits
        ) ?? $html;
        $replacements += (int) $spanHits;

        if (!$old) {
            return [$html, $replacements];
        }

        // Otherwise match the written-out date, in the formats the templates use.
        $formats = ['d M Y', 'd-m-Y', 'd/m/Y', 'jS F Y', 'j F Y', 'F j, Y', 'd F Y', 'Y-m-d'];
        foreach (array_unique(array_map(fn ($f) => $old->format($f), $formats)) as $needle) {
            if ($needle === '' || !str_contains($html, $needle)) {
                continue;
            }
            $count = 0;
            $html = str_replace($needle, $new->format(
                $needle === $old->format('d M Y') ? 'd M Y' : $this->matchingFormat($old, $needle)
            ), $html, $count);
            $replacements += $count;
        }

        return [$html, $replacements];
    }

    /**
     * Put the reserved serial into the letter body, whether the template tags it
     * or writes it as plain text.
     */
    private function stampSerial(string $html, string $serial): string
    {
        $tagged = preg_replace(
            '/(<span data-field="ref_no">)(.*?)(<\/span>)/s',
            '$1' . e($serial) . '$3',
            $html,
            -1,
            $hits
        );

        if ($hits) {
            return $tagged ?? $html;
        }

        // Not tagged: replace a previously written number of the same shape.
        return preg_replace('~SHPL/[A-Z]+/[A-Z]+/\d{4}/\d+~', $serial, $html) ?? $html;
    }

    /** Which of the known formats produced this rendering of the old date. */
    private function matchingFormat(Carbon $old, string $rendered): string
    {
        foreach (['d M Y', 'd-m-Y', 'd/m/Y', 'jS F Y', 'j F Y', 'F j, Y', 'd F Y', 'Y-m-d'] as $f) {
            if ($old->format($f) === $rendered) {
                return $f;
            }
        }

        return 'd M Y';
    }

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

    /**
     * Strip editor/paste junk (Microsoft Word + copied web styling) so pasted
     * template content renders cleanly in the PDF:
     *  - removes Word conditional comments and o:p/w:/v:/xml tags
     *  - drops --tw-* (Tailwind) and mso-* declarations from style attributes
     *  - drops stray color / background declarations and <font color>/color attrs
     *  - keeps safe layout declarations (alignment, margins, font-weight, width)
     * Our own generated blocks (CTC table, signature) are added separately and
     * are unaffected.
     */
    private function cleanPastedHtml(?string $html): string
    {
        $html = (string) $html;
        if ($html === '') {
            return $html;
        }

        // Word conditional comments and namespaced tags
        $html = preg_replace('/<!--\[if[^\]]*\]>.*?<!\[endif\]-->/is', '', $html) ?? $html;
        $html = preg_replace('/<\/?(?:o:p|w:[a-z0-9]+|v:[a-z0-9]+|xml)[^>]*>/i', '', $html) ?? $html;

        // Clean inline style attributes
        $html = preg_replace_callback('/\sstyle="([^"]*)"/i', function ($m) {
            $keep = [];
            foreach (explode(';', $m[1]) as $decl) {
                $decl = trim($decl);
                if ($decl === '' || !str_contains($decl, ':')) {
                    continue;
                }
                [$prop, $val] = explode(':', $decl, 2);
                $prop = strtolower(trim($prop));
                if ($prop === '' || str_starts_with($prop, '--tw-') || str_starts_with($prop, 'mso-')) {
                    continue;
                }
                if (in_array($prop, ['color', 'background', 'background-color', 'background-image', 'font-family'], true)) {
                    continue;
                }
                $keep[] = $prop . ':' . trim($val);
            }
            return $keep ? ' style="' . implode(';', $keep) . '"' : '';
        }, $html) ?? $html;

        // <code>/<tt> force a monospace face in the PDF; keep the text, drop the tag.
        $html = preg_replace('/<\/?(?:code|tt)\b[^>]*>/i', '', $html) ?? $html;

        // Legacy color attributes and empty Word class names
        $html = preg_replace('/\scolor="[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace('/\s(?:bgcolor|background)="[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace('/\sclass="Mso[^"]*"/i', '', $html) ?? $html;

        return $html;
    }

    /**
     * Swap a rendered signature block back to the {{signature_block}} token.
     *
     * Parsed with DOM rather than a regex: the block contains a nested div and
     * is followed by the acceptance section, so pattern matching either stops
     * short and orphans a closing tag, or runs past the end of the block. Get
     * this wrong and the letter ends up with two signatures.
     */
    private function signatureBlockToToken(string $html): string
    {
        if (!str_contains($html, 'sigblock')) {
            return $html;
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $doc->loadHTML(
            '<?xml encoding="UTF-8"?><div id="olroot">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return $html;
        }

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' sigblock ')]");
        if (!$nodes || $nodes->length === 0) {
            return $html;
        }

        $first = true;
        foreach (iterator_to_array($nodes) as $node) {
            // Keep one token; drop any duplicates that crept in earlier.
            $replacement = $first
                ? $doc->createTextNode('{{signature_block}}')
                : $doc->createTextNode('');
            $node->parentNode->replaceChild($replacement, $node);
            $first = false;
        }

        $root = $xpath->query("//div[@id='olroot']")->item(0);
        if (!$root) {
            return $html;
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out !== '' ? $out : $html;
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

    /** Indian-style number grouping (e.g. 1,38,370). */
    private function inr($n): string
    {
        $n = (int) round((float) $n);
        $neg = $n < 0; $n = abs($n);
        $str = (string) $n;
        if (strlen($str) <= 3) return ($neg ? '-' : '') . $str;
        $last3 = substr($str, -3);
        $rest = substr($str, 0, -3);
        $rest = preg_replace('/\\B(?=(\\d{2})+(?!\\d))/', ',', $rest);
        return ($neg ? '-' : '') . $rest . ',' . $last3;
    }

    /** Build the salary breakup table HTML from a MONTHLY CTC (mirrors the client Excel). */
    private function ctcBreakupTable($monthlyCtc): string
    {
        $m = (float) $monthlyCtc;
        if ($m <= 0) {
            return '<div class="ctc-breakup"></div>';
        }
        $basicPct = 0.5; $hraPct = 0.4; $erPfPct = 0.12; $eePfPct = 0.12; $gratPct = 0.0481;
        $gross    = $m / (1 + ($basicPct * $erPfPct) + ($basicPct * $gratPct));
        $basic    = $gross * $basicPct;
        $hra      = $basic * $hraPct;
        $special  = $gross - $basic - $hra;
        $erPf     = $basic * $erPfPct;
        $erEsic   = $gross <= 21000 ? $gross * 0.0325 : 0;
        $gratuity = $basic * $gratPct;
        $total    = $gross + $erPf + $erEsic + $gratuity;
        $eePf     = $basic * $eePfPct;
        $eeEsic   = $gross <= 21000 ? $gross * 0.0075 : 0;
        $takehome = $gross - $eePf - $eeEsic;

        $row = function ($label, $mv, $yv, $strong = false) {
            $o = $strong ? '<b>' : ''; $c = $strong ? '</b>' : '';
            return '<tr><td>' . $o . $label . $c . '</td><td>' . $o . 'Rs. ' . $this->inr($mv) . $c . '</td><td>' . $o . 'Rs. ' . $this->inr($yv) . $c . '</td></tr>';
        };
        // <th> rather than a shaded first row: the heading stays bold now that
        // nothing in the letter body paints a background.
        $h  = '<div class="ctc-breakup"><table><tr><th>Salary Component</th><th>Monthly (Rs.)</th><th>Annual (Rs.)</th></tr>';
        $h .= $row('Basic Salary', $basic, $basic * 12);
        $h .= $row('HRA', $hra, $hra * 12);
        $h .= $row('Special Allowance', $special, $special * 12);
        $h .= $row('Gross Salary', $gross, $gross * 12, true);
        $h .= $row('Employer PF', $erPf, $erPf * 12);
        if ($erEsic > 0) { $h .= $row('Employer ESIC', $erEsic, $erEsic * 12); }
        $h .= $row('Gratuity', $gratuity, $gratuity * 12);
        $h .= $row('Total CTC', $total, $total * 12, true);
        $h .= '</table><p style="font-size:11px;color:#555;margin-top:4px;">Employee PF: Rs. ' . $this->inr($eePf) . '/month'
            . ($eeEsic > 0 ? ' &middot; Employee ESIC: Rs. ' . $this->inr($eeEsic) . '/month' : '')
            . ' &middot; Approx. take-home: Rs. ' . $this->inr($takehome) . '/month (before income tax &amp; other deductions).</p></div>';
        return $h;
    }

    private function renderPdf(
        string $subject,
        string $bodyHtml,
        string $heading = 'OFFER LETTER',
        ?string $serial = null
    ): string {
        $s = OfferLetterSetting::current();

        // No template currently writes {{ref_no}}, so the number is printed by
        // the layout. Suppressed when the body already shows it, to avoid two.
        $showSerial = $serial && !str_contains($bodyHtml, $serial);

        $html = view('admin.offer_letters.pdf', [
            'subject'  => $subject,
            'heading'  => $heading,
            'bodyHtml' => $bodyHtml,
            'settings' => $s,
            'logo'     => $this->dataUri($s->logo_path),
            'serialNo' => $showSerial ? $serial : null,
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

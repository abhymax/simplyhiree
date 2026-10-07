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
            $q->where(fn ($x) => $x->where('candidate_name', 'like', "%{$sv}%")->orWhere('candidate_email', 'like', "%{$sv}%"));
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
            'ref_no'       => 'SHPL/HR/OL/' . date('Y') . '/' . str_pad((int) ((OfferLetter::max('id') ?? 0) + 3001), 4, '0', STR_PAD_LEFT),
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
        if ($offerLetter->status === 'sent') {
            return redirect()->route('admin.offer-letters.index')
                ->with('error', 'This letter has already been sent, so it cannot be edited. Create a new one instead.');
        }

        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->findOrFail($offerLetter->job_application_id);

        $body = $this->signatureBlockToToken((string) $offerLetter->body_html);

        // Re-read the structured values straight from the body's tagged spans,
        // so the Offer Details panel shows what was saved.
        $fields = [];
        foreach (['ref_no', 'department', 'reporting_to', 'monthly_ctc', 'annual_ctc'] as $f) {
            preg_match('/<span data-field="' . $f . '">(.*?)<\/span>/s', $body, $m);
            $value = isset($m[1]) ? trim(strip_tags($m[1])) : '';
            $fields[$f] = ($value === '' || str_starts_with($value, '____')) ? '' : $value;
        }

        $template = $offerLetter->template_id
            ? OfferLetterTemplate::find($offerLetter->template_id)
            : OfferLetterTemplate::where('is_active', true)->first();

        return view('admin.offer_letters.compose', [
            'app'        => $app,
            'template'   => $template,
            'letter'     => $offerLetter,
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
        if ($offerLetter->status === 'sent') {
            return redirect()->route('admin.offer-letters.index')
                ->with('error', 'This letter has already been sent, so it cannot be edited.');
        }

        $data = $request->validate([
            'subject'      => 'required|string|max:255',
            'heading'      => 'nullable|string|max:120',
            'body_html'    => 'required|string',
            'signature_id' => 'nullable|exists:offer_letter_signatures,id',
            'action'       => 'required|in:send,draft',
        ]);

        $app = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->findOrFail($offerLetter->job_application_id);
        $email = $app->candidate->email ?? $app->candidateUser->email ?? null;

        $sig = !empty($data['signature_id']) ? OfferLetterSignature::find($data['signature_id']) : null;

        $data['body_html'] = $this->cleanPastedHtml($data['body_html']);
        $sigBlock = $this->signatureBlockHtml($sig);
        $finalBody = str_contains($data['body_html'], '{{signature_block}}')
            ? str_replace('{{signature_block}}', $sigBlock, $data['body_html'])
            : $data['body_html'] . $sigBlock;

        $heading = trim((string) ($data['heading'] ?? '')) ?: 'OFFER LETTER';
        $pdf = $this->renderPdf($data['subject'], $finalBody, $heading);

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

        if ($data['action'] === 'send') {
            if (!$email) {
                return redirect()->route('admin.offer-letters.index')
                    ->with('error', 'Draft updated but the candidate has no email on file, so it was not sent.');
            }
            $this->sendEmails($offerLetter->fresh(), $pdf, $email);
            $offerLetter->update(['status' => 'sent', 'sent_at' => now()]);
            return redirect()->route('admin.offer-letters.index')->with('success', 'Offer letter sent to ' . $email . '.');
        }

        return redirect()->route('admin.offer-letters.index')->with('success', 'Draft updated.');
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
        $pdf = $this->renderPdf($data['subject'], $finalBody, $heading);
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
            'heading'            => $heading,
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
        $pdf = $this->renderPdf($data['subject'], $body, trim((string)($data['heading'] ?? '')) ?: 'OFFER LETTER');
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
        $h  = '<div class="ctc-breakup"><table><tr><td>Salary Component</td><td>Monthly (Rs.)</td><td>Annual (Rs.)</td></tr>';
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

    private function renderPdf(string $subject, string $bodyHtml, string $heading = 'OFFER LETTER'): string
    {
        $s = OfferLetterSetting::current();
        $html = view('admin.offer_letters.pdf', [
            'subject'  => $subject,
            'heading'  => $heading,
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

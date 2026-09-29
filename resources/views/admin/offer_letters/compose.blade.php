<style>#editor, #editor * { color:#0f172a !important; background-color: transparent !important; }</style>
<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-4xl mx-auto">
  <a href="{{ route('admin.offer-letters.create') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Back</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-1">Compose Offer Letter</h1>
  <p class="text-blue-200/80 mb-5 text-sm">To: <b class="text-white">{{ $app->candidate_name }}</b>
     ({{ $app->candidate->email ?? $app->candidateUser->email ?? 'no email on file' }}) ·
     {{ optional($app->job)->title }} @ {{ optional($app->job)->company_name }}.
     The SimplyHiree letterhead &amp; director signature are added automatically.</p>

  <form method="POST" action="{{ route('admin.offer-letters.store') }}" onsubmit="document.getElementById('body_html').value = document.getElementById('editor').innerHTML;">
    @csrf
    <input type="hidden" name="job_application_id" value="{{ $app->id }}">
    <input type="hidden" name="template_id" value="{{ $template->id }}">
    <input type="hidden" name="body_html" id="body_html">

    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Subject *</label>
    <input type="text" name="subject" value="{{ $subject }}" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5 mb-4">

    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Signature *</label>
    @if($signatures->isEmpty())
      <div class="mb-4 rounded-xl border border-amber-400/40 bg-amber-500/15 p-3 text-amber-100 text-sm">No signatures yet. <a class="underline font-bold" href="{{ route('admin.offer-letters.signature.create') }}" target="_blank">Add a signature</a> (name, designation, image), then reload this page.</div>
    @else
      <select name="signature_id" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5 mb-4">
        <option value="">— Choose who signs —</option>
        @foreach($signatures as $sg)<option value="{{ $sg->id }}">{{ $sg->name }}@if($sg->designation) — {{ $sg->designation }}@endif</option>@endforeach
      </select>
      <p class="text-[11px] text-slate-300 -mt-3 mb-4">The chosen signature replaces the <code class="bg-white/10 text-cyan-200 px-1.5 py-0.5 rounded">@{{signature_block}}</code> marker in the letter.</p>
    @endif

    {{-- Structured fields — typing here updates the matching spots in the letter below --}}
    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 mb-4">
      <div class="text-xs font-bold text-cyan-300 uppercase mb-3">Offer details</div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="block text-[11px] font-bold text-slate-300 mb-1">Reference No.</label>
          <input type="text" data-olfield="ref_no" value="{{ $fields['ref_no'] ?? '' }}" class="w-full rounded-lg border border-white/20 bg-slate-800/80 text-white text-sm px-3 py-2">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 mb-1">Department</label>
          <input type="text" data-olfield="department" value="{{ $fields['department'] ?? '' }}" placeholder="e.g. Talent Acquisition" class="w-full rounded-lg border border-white/20 bg-slate-800/80 text-white text-sm px-3 py-2">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 mb-1">Reporting To</label>
          <input type="text" data-olfield="reporting_to" value="{{ $fields['reporting_to'] ?? '' }}" placeholder="e.g. Recruitment Manager" class="w-full rounded-lg border border-white/20 bg-slate-800/80 text-white text-sm px-3 py-2">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 mb-1">Monthly CTC (₹)</label>
          <input type="text" data-olfield="monthly_ctc" value="{{ $fields['monthly_ctc'] ?? '' }}" placeholder="e.g. 20,000" class="w-full rounded-lg border border-white/20 bg-slate-800/80 text-white text-sm px-3 py-2">
        </div>
        <div>
          <label class="block text-[11px] font-bold text-slate-300 mb-1">Annual CTC (₹)</label>
          <input type="text" data-olfield="annual_ctc" value="{{ $fields['annual_ctc'] ?? '' }}" placeholder="e.g. 2,40,000" class="w-full rounded-lg border border-white/20 bg-slate-800/80 text-white text-sm px-3 py-2">
        </div>
      </div>
      <p class="text-[11px] text-slate-400 mt-2">These fill the matching fields in the letter automatically. You can still fine-tune the body below.</p>
    </div>

    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Letter body (edit as needed)</label>
    <div class="flex flex-wrap gap-1 mb-2">
      @foreach(['bold'=>'B','italic'=>'I','underline'=>'U','insertUnorderedList'=>'• List','insertOrderedList'=>'1. List'] as $cmd=>$lbl)
        <button type="button" onclick="document.execCommand('{{ $cmd }}',false,null);document.getElementById('editor').focus();" class="px-2.5 py-1 rounded bg-white/10 text-slate-200 text-xs font-bold hover:bg-white/20">{{ $lbl }}</button>
      @endforeach
    </div>
    <div id="editor" contenteditable="true" class="bg-white text-slate-900 rounded-xl px-6 py-5 min-h-[420px] leading-relaxed" style="font-family:Georgia,serif;">{!! $bodyHtml !!}</div>

    <div class="flex justify-end gap-3 mt-5">
      <button type="button" onclick="previewOffer()" class="rounded-xl border border-cyan-300/40 bg-cyan-500/10 text-cyan-100 font-bold px-5 py-3 hover:bg-cyan-500/20"><i class="fa-solid fa-eye mr-1"></i>Preview PDF</button>
      <button type="submit" name="action" value="draft" class="rounded-xl border border-white/15 text-slate-100 font-bold px-5 py-3 hover:bg-white/10">Save draft</button>
      <button type="submit" name="action" value="send" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold px-6 py-3" onclick="return confirm('Generate the PDF and email it to the candidate now?');">Generate &amp; Send</button>
    </div>
  </form>

  {{-- Hidden form used by the Preview PDF button (opens in a new tab) --}}
  <form id="previewForm" method="POST" action="{{ route('admin.offer-letters.preview-pdf') }}" target="_blank" style="display:none">
    @csrf
    <input type="hidden" name="job_application_id" value="{{ $app->id }}">
    <input type="hidden" name="subject" id="pf_subject">
    <input type="hidden" name="body_html" id="pf_body">
    <input type="hidden" name="signature_id" id="pf_sig">
  </form>
  <script>
    // Live-bind the structured fields to the matching spans in the letter body.
    document.querySelectorAll('[data-olfield]').forEach(function (inp) {
      inp.addEventListener('input', function () {
        var f = inp.getAttribute('data-olfield');
        document.querySelectorAll('#editor [data-field="' + f + '"]').forEach(function (sp) {
          sp.textContent = inp.value && inp.value.trim() !== '' ? inp.value : '________';
        });
      });
    });
    function previewOffer(){
      document.getElementById('pf_body').value = document.getElementById('editor').innerHTML;
      document.getElementById('pf_subject').value = document.querySelector('input[name=subject]').value;
      var sel = document.querySelector('select[name=signature_id]');
      document.getElementById('pf_sig').value = sel ? sel.value : '';
      document.getElementById('previewForm').submit();
    }
  </script>
 </div></div>
</x-app-layout>

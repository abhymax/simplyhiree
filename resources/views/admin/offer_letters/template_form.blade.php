<style>#editor { background:#ffffff !important; color:#0f172a !important; } #editor * { color:#0f172a !important; background-color: transparent !important; }</style>
<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-3xl mx-auto">
  @include('admin.offer_letters._nav')
  <a href="{{ route('admin.offer-letters.templates') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Back to all templates</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-2">{{ $tpl && $tpl->exists ? 'Edit' : 'New' }} Template</h1>
  <p class="text-blue-200/80 text-xs mb-3">Placeholders you can use: <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{candidate_name}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{job_title}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{company}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{location}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{joining_date}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{date}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{ref_no}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{department}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{reporting_to}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{monthly_ctc}}</code> <code class="bg-white/10 text-cyan-200 px-1 rounded">@{{annual_ctc}}</code></p>
  <p class="text-blue-200/80 text-xs mb-5">Special blocks: <code class="bg-emerald-500/15 text-emerald-200 px-1 rounded">@{{ctc_breakup}}</code> auto-inserts the salary breakup table &middot; <code class="bg-emerald-500/15 text-emerald-200 px-1 rounded">@{{signature_block}}</code> is where the chosen signature goes.</p>
  @if($errors->any())<div class="mb-4 rounded-xl border border-rose-400/40 bg-rose-500/15 p-3 text-rose-100 text-sm"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ $tpl && $tpl->exists ? route('admin.offer-letters.template.update', $tpl) : route('admin.offer-letters.template.store') }}"
        onsubmit="document.getElementById('body_html').value=document.getElementById('editor').innerHTML;"
        class="rounded-3xl border border-white/15 bg-slate-900/60 p-6 space-y-4">
    @csrf
    @if($tpl && $tpl->exists) @method('PATCH') @endif
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Template name *</label>
      <input name="name" value="{{ old('name', $tpl->name ?? '') }}" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5"></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Default subject</label>
      <input name="subject" value="{{ old('subject', $tpl->subject ?? 'Offer of Employment') }}" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5"></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Default heading (PDF title)</label>
      <input name="default_heading" value="{{ old('default_heading', $tpl->default_heading ?? 'OFFER LETTER') }}" placeholder="e.g. OFFER LETTER, APPOINTMENT LETTER" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5"></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Body</label>
      <div class="flex flex-wrap gap-1 mb-2">@foreach(['bold'=>'B','italic'=>'I','underline'=>'U','insertUnorderedList'=>'• List'] as $cmd=>$lbl)<button type="button" onclick="document.execCommand('{{ $cmd }}',false,null);document.getElementById('editor').focus();" class="px-2.5 py-1 rounded bg-white/10 text-slate-200 text-xs font-bold">{{ $lbl }}</button>@endforeach</div>
      <input type="hidden" name="body_html" id="body_html">
      @php $editorHtml = old('body_html', ($tpl->body_html ?? null) ?: '<p>Dear {{candidate_name}},</p><p>We are pleased to offer you the position of <b>{{job_title}}</b> at {{company}}.</p>'); @endphp
      <div id="editor" contenteditable="true" class="bg-white text-slate-900 rounded-xl px-5 py-4 min-h-[320px]" style="font-family:Georgia,serif;">{!! $editorHtml !!}</div>
    </div>
    <label class="flex items-center gap-2 text-slate-200 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tpl->is_active ?? true))> Active</label>
    <div class="flex justify-end gap-3">
      <button type="button" onclick="previewTemplate()" class="rounded-xl border border-cyan-300/40 bg-cyan-500/10 text-cyan-100 font-bold px-5 py-3 hover:bg-cyan-500/20"><i class="fa-solid fa-eye mr-1"></i>Preview PDF</button>
      <button class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold px-6 py-3">Save Template</button>
    </div>
  </form>

  {{-- Previews whatever is in the editor right now, saved or not, with sample
       candidate details filled into the placeholders. --}}
  <form id="tplPreviewForm" method="POST" action="{{ route('admin.offer-letters.template.preview-pdf') }}" target="_blank" style="display:none">
    @csrf
    <input type="hidden" name="subject" id="pv_subject">
    <input type="hidden" name="heading" id="pv_heading">
    <input type="hidden" name="body_html" id="pv_body">
  </form>
  <script>
    function previewTemplate() {
      document.getElementById('pv_subject').value = document.querySelector('[name=subject]').value;
      document.getElementById('pv_heading').value = document.querySelector('[name=default_heading]').value;
      document.getElementById('pv_body').value    = document.getElementById('editor').innerHTML;
      document.getElementById('tplPreviewForm').submit();
    }
  </script>
 </div></div>
</x-app-layout>

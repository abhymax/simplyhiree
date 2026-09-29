<style>#editor { background:#ffffff !important; color:#0f172a !important; } #editor * { color:#0f172a !important; background-color: transparent !important; }</style>
<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-3xl mx-auto">
  <a href="{{ route('admin.offer-letters.templates') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Templates</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-2">{{ $tpl && $tpl->exists ? 'Edit' : 'New' }} Template</h1>
  <p class="text-blue-200/70 text-xs mb-5">Placeholders you can use: <code>@{{candidate_name}}</code> <code>@{{job_title}}</code> <code>@{{company}}</code> <code>@{{ctc}}</code> <code>@{{joining_date}}</code> <code>@{{location}}</code> <code>@{{date}}</code> <code>@{{director_name}}</code></p>
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
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Body</label>
      <div class="flex flex-wrap gap-1 mb-2">@foreach(['bold'=>'B','italic'=>'I','underline'=>'U','insertUnorderedList'=>'• List'] as $cmd=>$lbl)<button type="button" onclick="document.execCommand('{{ $cmd }}',false,null);document.getElementById('editor').focus();" class="px-2.5 py-1 rounded bg-white/10 text-slate-200 text-xs font-bold">{{ $lbl }}</button>@endforeach</div>
      <input type="hidden" name="body_html" id="body_html">
      @php $editorHtml = old('body_html', ($tpl->body_html ?? null) ?: '<p>Dear {{candidate_name}},</p><p>We are pleased to offer you the position of <b>{{job_title}}</b> at {{company}}.</p>'); @endphp
      <div id="editor" contenteditable="true" class="bg-white text-slate-900 rounded-xl px-5 py-4 min-h-[320px]" style="font-family:Georgia,serif;">{!! $editorHtml !!}</div>
    </div>
    <label class="flex items-center gap-2 text-slate-200 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $tpl->is_active ?? true))> Active</label>
    <div class="flex justify-end"><button class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold px-6 py-3">Save Template</button></div>
  </form>
 </div></div>
</x-app-layout>

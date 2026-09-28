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

    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Letter body (edit as needed)</label>
    <div class="flex flex-wrap gap-1 mb-2">
      @foreach(['bold'=>'B','italic'=>'I','underline'=>'U','insertUnorderedList'=>'• List','insertOrderedList'=>'1. List'] as $cmd=>$lbl)
        <button type="button" onclick="document.execCommand('{{ $cmd }}',false,null);document.getElementById('editor').focus();" class="px-2.5 py-1 rounded bg-white/10 text-slate-200 text-xs font-bold hover:bg-white/20">{{ $lbl }}</button>
      @endforeach
    </div>
    <div id="editor" contenteditable="true" class="bg-white text-slate-900 rounded-xl px-6 py-5 min-h-[420px] leading-relaxed" style="font-family:Georgia,serif;">{!! $bodyHtml !!}</div>

    <div class="flex justify-end gap-3 mt-5">
      <button type="submit" name="action" value="draft" class="rounded-xl border border-white/15 text-slate-100 font-bold px-5 py-3 hover:bg-white/10">Save draft</button>
      <button type="submit" name="action" value="send" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold px-6 py-3" onclick="return confirm('Generate the PDF and email it to the candidate now?');">Generate &amp; Send</button>
    </div>
  </form>
 </div></div>
</x-app-layout>

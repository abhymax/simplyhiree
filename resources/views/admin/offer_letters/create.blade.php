<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-3xl mx-auto">
  <a href="{{ route('admin.offer-letters.index') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Back</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-6">Create Offer Letter</h1>
  @if($templates->isEmpty())
    <div class="rounded-2xl border border-amber-400/40 bg-amber-500/15 p-4 text-amber-100">No templates yet. <a class="underline font-bold" href="{{ route('admin.offer-letters.template.create') }}">Create a template</a> first.</div>
  @else
  <form method="POST" action="{{ route('admin.offer-letters.compose') }}" class="rounded-3xl border border-white/15 bg-slate-900/60 p-6 space-y-5">
    @csrf
    <div>
      <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Candidate pool</label>
      <div class="flex gap-2 mb-3">
        <a href="{{ route('admin.offer-letters.create', ['pool'=>'selected']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $filter==='selected' ? 'bg-cyan-500 text-slate-950' : 'bg-white/10 text-slate-200' }}">Selected</a>
        <a href="{{ route('admin.offer-letters.create', ['pool'=>'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $filter==='approved' ? 'bg-cyan-500 text-slate-950' : 'bg-white/10 text-slate-200' }}">Approved</a>
      </div>
      <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Select candidate *</label>
      <select name="job_application_id" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5">
        <option value="">— Choose a candidate —</option>
        @foreach($applications as $a)<option value="{{ $a['id'] }}">{{ $a['label'] }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Select template *</label>
      <select name="template_id" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5">
        <option value="">— Choose a template —</option>
        @foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
      </select>
    </div>
    <div class="flex justify-end"><button class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold px-6 py-3">Continue →</button></div>
  </form>
  @endif
 </div></div>
</x-app-layout>

<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-2xl mx-auto">
  <a href="{{ route('admin.offer-letters.index') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Offer Letters</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-5">Offer Letter Branding</h1>
  @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-emerald-100">{{ session('success') }}</div>@endif
  <form method="POST" action="{{ route('admin.offer-letters.settings.update') }}" enctype="multipart/form-data" class="rounded-3xl border border-white/15 bg-slate-900/60 p-6 space-y-5">
    @csrf
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Director name</label>
        <input name="director_name" value="{{ $settings->director_name }}" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5"></div>
      <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Designation</label>
        <input name="director_designation" value="{{ $settings->director_designation }}" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5"></div>
    </div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Company address (letterhead, top-right)</label>
      <textarea name="company_address" rows="3" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5">{{ $settings->company_address }}</textarea></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Footer text</label>
      <textarea name="company_footer" rows="2" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5">{{ $settings->company_footer }}</textarea></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Logo / letterhead (PNG/JPG/SVG)</label>
        @if($settings->logo_path)<img src="{{ Storage::url($settings->logo_path) }}" class="h-12 mb-2 bg-white rounded p-1">@endif
        <input type="file" name="logo" accept="image/*" class="w-full text-slate-200 text-sm"></div>
      <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Director signature (transparent PNG)</label>
        @if($settings->signature_path)<img src="{{ Storage::url($settings->signature_path) }}" class="h-12 mb-2 bg-white rounded p-1">@endif
        <input type="file" name="signature" accept="image/*" class="w-full text-slate-200 text-sm"></div>
    </div>
    <div class="flex justify-end"><button class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold px-6 py-3">Save Branding</button></div>
  </form>
 </div></div>
</x-app-layout>

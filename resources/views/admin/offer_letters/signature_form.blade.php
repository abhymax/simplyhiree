<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-xl mx-auto">
  <a href="{{ route('admin.offer-letters.signatures') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Signatures</a>
  <h1 class="text-3xl font-extrabold text-white mt-3 mb-5">{{ $sig && $sig->exists ? 'Edit' : 'Add' }} Signature</h1>
  @if($errors->any())<div class="mb-4 rounded-xl border border-rose-400/40 bg-rose-500/15 p-3 text-rose-100 text-sm"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ $sig && $sig->exists ? route('admin.offer-letters.signature.update', $sig) : route('admin.offer-letters.signature.store') }}" enctype="multipart/form-data" class="rounded-3xl border border-white/15 bg-slate-900/60 p-6 space-y-4">
    @csrf
    @if($sig && $sig->exists) @method('PATCH') @endif
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Name *</label>
      <input name="name" value="{{ old('name', $sig->name ?? '') }}" required class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5" placeholder="e.g. Sachin Singh"></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Designation</label>
      <input name="designation" value="{{ old('designation', $sig->designation ?? '') }}" class="w-full rounded-xl border border-white/20 bg-slate-800/80 text-white px-3 py-2.5" placeholder="e.g. HR Manager"></div>
    <div><label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Signature image (transparent PNG)</label>
      @if($sig && $sig->signature_path)<img src="{{ Storage::url($sig->signature_path) }}" class="h-14 mb-2 bg-white rounded p-1">@endif
      <input type="file" name="signature" accept="image/*" class="w-full text-slate-200 text-sm"></div>
    <label class="flex items-center gap-2 text-slate-200 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sig->is_active ?? true))> Active</label>
    <div class="flex justify-end"><button class="rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold px-6 py-3">Save Signature</button></div>
  </form>
 </div></div>
</x-app-layout>

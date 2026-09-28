<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div><a href="{{ route('admin.offer-letters.index') }}" class="text-cyan-300 hover:text-white text-sm font-bold">← Offer Letters</a>
      <h1 class="text-3xl font-extrabold text-white mt-2">Signature Manager</h1>
      <p class="text-blue-200/80 text-sm mt-1">Signatories that can be selected when sending an offer letter.</p></div>
    <a href="{{ route('admin.offer-letters.signature.create') }}" class="rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-4 py-2.5">+ Add Signature</a>
  </div>
  @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-emerald-100">{{ session('success') }}</div>@endif
  <div class="rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden"><table class="w-full text-sm">
   <thead class="bg-white/5 text-blue-200/80 text-xs uppercase"><tr><th class="text-left px-5 py-3">Signature</th><th class="text-left px-5 py-3">Name</th><th class="text-left px-5 py-3">Designation</th><th class="text-left px-5 py-3">Active</th><th class="text-right px-5 py-3"></th></tr></thead>
   <tbody class="divide-y divide-white/5 text-white">
   @forelse($signatures as $sg)
     <tr class="hover:bg-white/5">
       <td class="px-5 py-3">@if($sg->signature_path)<img src="{{ Storage::url($sg->signature_path) }}" class="h-10 bg-white rounded p-1">@else<span class="text-blue-200/40 text-xs">no image</span>@endif</td>
       <td class="px-5 py-3 font-bold">{{ $sg->name }}</td><td class="px-5 py-3 text-blue-200/80">{{ $sg->designation }}</td>
       <td class="px-5 py-3">{{ $sg->is_active ? 'Yes' : 'No' }}</td>
       <td class="px-5 py-3 text-right">
         <a href="{{ route('admin.offer-letters.signature.edit', $sg) }}" class="text-cyan-300 font-bold text-xs mr-3">Edit</a>
         <form method="POST" action="{{ route('admin.offer-letters.signature.delete', $sg) }}" class="inline" onsubmit="return confirm('Delete this signature?');">@csrf @method('DELETE')<button class="text-rose-300 font-bold text-xs">Delete</button></form>
       </td></tr>
   @empty<tr><td colspan="5" class="px-5 py-10 text-center text-blue-200/60">No signatures yet.</td></tr>@endforelse
   </tbody></table></div>
 </div></div>
</x-app-layout>

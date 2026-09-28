<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-6xl mx-auto">
  <div class="mb-8 border-b border-white/10 pb-6">
    <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Offer Letters</h1>
    <p class="text-blue-200 mt-1">Generate and send branded offer letters to candidates.</p>
    <div class="flex flex-wrap gap-2 mt-4">
      <a href="{{ route('admin.offer-letters.create') }}" class="rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-4 py-2.5"><i class="fa-solid fa-plus mr-1"></i>Create Offer Letter</a>
      <a href="{{ route('admin.offer-letters.templates') }}" class="rounded-xl border border-white/20 bg-white/5 text-slate-100 font-bold px-4 py-2.5 hover:bg-white/10"><i class="fa-solid fa-file-lines mr-1"></i>Templates</a>
      <a href="{{ route('admin.offer-letters.signatures') }}" class="rounded-xl border border-white/20 bg-white/5 text-slate-100 font-bold px-4 py-2.5 hover:bg-white/10"><i class="fa-solid fa-signature mr-1"></i>Signatures</a>
      <a href="{{ route('admin.offer-letters.settings') }}" class="rounded-xl border border-white/20 bg-white/5 text-slate-100 font-bold px-4 py-2.5 hover:bg-white/10"><i class="fa-solid fa-image mr-1"></i>Branding</a>
    </div>
  </div>
  @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-emerald-100">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/15 p-4 text-rose-100">{{ session('error') }}</div>@endif
  <div class="rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden"><div class="overflow-x-auto">
   <table class="w-full text-sm"><thead class="bg-white/5 text-blue-200/80 text-xs uppercase tracking-wide"><tr>
     <th class="text-left px-5 py-3">Candidate</th><th class="text-left px-5 py-3">Role / Company</th>
     <th class="text-left px-5 py-3">Status</th><th class="text-left px-5 py-3">Sent</th><th class="text-right px-5 py-3"></th></tr></thead>
   <tbody class="divide-y divide-white/5 text-white">
   @forelse($letters as $l)
     <tr class="hover:bg-white/5">
       <td class="px-5 py-3"><div class="font-bold">{{ $l->candidate_name }}</div><div class="text-blue-200/60 text-xs">{{ $l->candidate_email }}</div></td>
       <td class="px-5 py-3">{{ $l->job_title }} <span class="text-blue-200/50 text-xs">@ {{ $l->company_name }}</span></td>
       <td class="px-5 py-3">@if($l->status==='sent')<span class="rounded-full border border-emerald-400/30 bg-emerald-500/15 text-emerald-200 px-2.5 py-1 text-xs font-bold">Sent</span>@else<span class="rounded-full border border-amber-400/30 bg-amber-500/15 text-amber-200 px-2.5 py-1 text-xs font-bold">Draft</span>@endif</td>
       <td class="px-5 py-3 text-blue-200/70 text-xs">{{ optional($l->sent_at ?? $l->created_at)->format('d M Y, h:i A') }}</td>
       <td class="px-5 py-3 text-right"><a href="{{ route('admin.offer-letters.download', $l) }}" class="rounded-lg border border-cyan-400/40 bg-cyan-500/15 text-cyan-200 text-xs font-bold px-3 py-1.5 hover:bg-cyan-500/25">Download PDF</a></td>
     </tr>
   @empty<tr><td colspan="5" class="px-5 py-12 text-center text-blue-200/60">No offer letters yet. Click “Create Offer Letter”.</td></tr>@endforelse
   </tbody></table></div></div>
  <div class="mt-5">{{ $letters->links() }}</div>
 </div></div>
</x-app-layout>

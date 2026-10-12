<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
 <div class="max-w-7xl mx-auto">

  <div class="mb-6 border-b border-white/10 pb-5">
    <h1 class="text-3xl font-extrabold text-white">Account Deletion Requests</h1>
    <p class="mt-1 text-blue-200 text-sm">People asking for their account and data to be removed.</p>
  </div>

  @if(session('success'))<div class="mb-5 rounded-2xl border border-emerald-400/40 bg-emerald-500/15 p-4 text-emerald-100">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="mb-5 rounded-2xl border border-rose-400/40 bg-rose-500/15 p-4 text-rose-100">{{ session('error') }}</div>@endif

  <div class="mb-5 grid grid-cols-3 gap-3">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><div class="text-xs font-bold uppercase text-blue-200">Total</div><div class="mt-1 text-2xl font-extrabold text-white">{{ $counts['all'] }}</div></div>
    <div class="rounded-2xl border border-amber-400/20 bg-amber-500/10 p-4"><div class="text-xs font-bold uppercase text-amber-300">Awaiting action</div><div class="mt-1 text-2xl font-extrabold text-white">{{ $counts['pending'] }}</div></div>
    <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><div class="text-xs font-bold uppercase text-emerald-300">Completed</div><div class="mt-1 text-2xl font-extrabold text-white">{{ $counts['done'] }}</div></div>
  </div>

  <form method="GET" class="mb-5 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email or reference…"
           class="min-w-[220px] flex-1 rounded-xl border border-white/20 bg-slate-900/40 px-4 py-2.5 text-white placeholder-slate-400">
    <select name="status" class="rounded-xl border border-white/20 bg-slate-900/40 px-4 py-2.5 text-white">
      <option value="">Any status</option>
      <option value="pending_verification" @selected(request('status')==='pending_verification')>Pending</option>
      <option value="completed" @selected(request('status')==='completed')>Completed</option>
      <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
    </select>
    <button class="rounded-xl bg-cyan-500 px-5 font-bold text-slate-950">Filter</button>
  </form>

  <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/40">
   <table class="w-full text-sm">
    <thead class="bg-white/5 text-xs uppercase tracking-wide text-blue-200/80">
      <tr>
        <th class="px-5 py-3 text-left">Requester</th>
        <th class="px-5 py-3 text-left">Account</th>
        <th class="px-5 py-3 text-left">What will be removed</th>
        <th class="px-5 py-3 text-left">Status</th>
        <th class="px-5 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-white/5 text-white">
    @forelse($requests as $r)
      @php $acct = $accounts[$r->id] ?? null; $pv = $previews[$r->id] ?? null; @endphp
      <tr class="align-top hover:bg-white/5">
        <td class="px-5 py-4">
          <div class="font-bold">{{ $r->name }}</div>
          <div class="text-xs text-blue-200/70">{{ $r->email }}</div>
          <div class="mt-0.5 text-[10px] uppercase tracking-wide text-slate-400">{{ $r->reference }} · {{ $r->account_type }}</div>
          @if($r->reason)<div class="mt-1 text-xs text-slate-300 italic">“{{ \Illuminate\Support\Str::limit($r->reason, 90) }}”</div>@endif
        </td>
        <td class="px-5 py-4">
          @if($acct)
            <span class="rounded-md border border-emerald-400/30 bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-200">Found · #{{ $acct->id }}</span>
            <div class="mt-1 text-xs text-slate-300">{{ $acct->name }}</div>
          @else
            <span class="rounded-md border border-slate-400/30 bg-slate-500/15 px-2 py-0.5 text-[10px] font-bold text-slate-300">No account</span>
          @endif
        </td>
        <td class="px-5 py-4 text-xs text-slate-300">
          @if($pv)
            {{ $pv['applications'] }} application(s), {{ $pv['interviews'] }} interview(s),
            {{ $pv['assessments'] }} assessment(s), {{ $pv['offer_letters'] }} offer letter(s),
            {{ $pv['conversations'] }} conversation(s), {{ $pv['files'] }} file(s)
          @else
            —
          @endif
        </td>
        <td class="px-5 py-4">
          @php $st = $r->status; @endphp
          <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase
            {{ $st === 'completed' ? 'border-emerald-400/30 bg-emerald-500/15 text-emerald-200'
               : ($st === 'rejected' ? 'border-slate-400/30 bg-slate-500/15 text-slate-300'
               : 'border-amber-400/30 bg-amber-500/15 text-amber-200') }}">{{ str_replace('_',' ',$st) }}</span>
        </td>
        <td class="px-5 py-4 text-right">
          @if($st !== 'completed' && auth()->user()->hasRole('Superadmin'))
            <details class="inline-block text-left">
              <summary class="cursor-pointer rounded-lg border border-rose-400/40 bg-rose-500/15 px-3 py-1.5 text-xs font-bold text-rose-200 list-none">Erase account</summary>
              <form method="POST" action="{{ route('admin.deletion-requests.erase', $r) }}"
                    class="mt-2 w-72 rounded-xl border border-rose-300/25 bg-rose-500/10 p-3"
                    onsubmit="return confirm('Permanently erase this account and all listed data? This cannot be undone.')">
                @csrf
                <p class="mb-2 text-[11px] text-rose-100">This permanently removes the account and everything listed. A backup file is written first.</p>
                <input type="text" name="confirm" required placeholder="Type ERASE to confirm"
                       class="w-full rounded-lg border border-white/20 bg-slate-800 px-2 py-1.5 text-xs text-white">
                <button class="mt-2 w-full rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-extrabold text-white hover:bg-rose-500">Erase permanently</button>
              </form>
            </details>
            <form method="POST" action="{{ route('admin.deletion-requests.reject', $r) }}" class="mt-2 inline-block">
              @csrf
              <button class="rounded-lg border border-white/20 bg-white/5 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-white/10">Reject</button>
            </form>
          @elseif($st === 'completed')
            <span class="text-xs text-slate-400">Done</span>
          @else
            <span class="text-xs text-slate-400">Superadmin only</span>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="px-5 py-12 text-center text-blue-200/60">No deletion requests. Anything submitted on the public form will appear here.</td></tr>
    @endforelse
    </tbody>
   </table>
  </div>

  <div class="mt-5">{{ $requests->links() }}</div>
 </div>
</div>
</x-app-layout>

@php
    $statusMap = [
        'pending' => ['Invited', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
        'verified' => ['Verified', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
        'in_progress' => ['In progress', 'bg-sky-500/15 text-sky-200 border-sky-400/30'],
        'passed' => ['Qualified', 'bg-emerald-500/15 text-emerald-200 border-emerald-400/30'],
        'failed' => ['Not qualified', 'bg-rose-500/15 text-rose-200 border-rose-400/30'],
        'expired' => ['Expired', 'bg-slate-500/15 text-slate-300 border-slate-400/30'],
    ];
    $chips = [
        'all' => 'All',
        'in_progress' => 'In progress',
        'passed' => 'Qualified',
        'failed' => 'Not qualified',
    ];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach($chips as $key => $label)
        <a href="{{ route($routes['index'], ['status' => $key]) }}"
           class="rounded-2xl border p-4 transition {{ $filter === $key ? 'border-white/40 bg-white/10' : 'border-white/10 bg-white/5 hover:bg-white/10' }}">
            <div class="text-xs font-bold uppercase tracking-wide text-blue-200">{{ $label }}</div>
            <div class="mt-1 text-2xl font-extrabold text-white">{{ $counts[$key] }}</div>
        </a>
    @endforeach
</div>

<form method="GET" class="mb-5 flex gap-3">
    <input type="hidden" name="status" value="{{ $filter }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate, email or job…"
           class="flex-1 rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-2.5 focus:ring-2 focus:ring-cyan-400">
    <button class="rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-5">Search</button>
</form>

<div class="rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/5 text-blue-200/80 text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left font-bold px-5 py-3">Candidate</th>
                    <th class="text-left font-bold px-5 py-3">Job</th>
                    @if($isAdmin)<th class="text-left font-bold px-5 py-3">Partner</th>@endif
                    <th class="text-left font-bold px-5 py-3">Progress</th>
                    <th class="text-left font-bold px-5 py-3">Status</th>
                    <th class="text-left font-bold px-5 py-3">Updated</th>
                    <th class="text-right font-bold px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-white">
                @forelse($sessions as $s)
                    @php
                        $sm = $statusMap[$s->status] ?? ['Unknown','bg-white/10 text-white border-white/20'];
                        $stageCount = optional($s->job)->assessmentStages ? $s->job->assessmentStages->count() : null;
                        $passedCount = $s->attempts->where('passed', true)->pluck('stage_order')->unique()->count();
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-5 py-3">
                            <div class="font-bold">{{ $s->candidate_name ?: (optional($s->candidate)->first_name ?? 'Candidate') }}</div>
                            <div class="text-blue-200/60 text-xs">{{ $s->maskedEmail() }}</div>
                        </td>
                        <td class="px-5 py-3">{{ optional($s->job)->title ?? '—' }}</td>
                        @if($isAdmin)<td class="px-5 py-3 text-blue-200/80">{{ optional($s->partner)->name ?? '—' }}</td>@endif
                        <td class="px-5 py-3 text-blue-200/80">{{ $passedCount }}{{ $stageCount !== null ? '/'.$stageCount : '' }} cleared</td>
                        <td class="px-5 py-3"><span class="inline-block rounded-full border px-2.5 py-1 text-xs font-bold {{ $sm[1] }}">{{ $sm[0] }}</span></td>
                        <td class="px-5 py-3 text-blue-200/70 text-xs">{{ $s->updated_at?->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route($routes['show'], $s) }}" class="rounded-lg border border-cyan-400/40 bg-cyan-500/15 text-cyan-200 text-xs font-bold px-3 py-1.5 hover:bg-cyan-500/25">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isAdmin ? 7 : 6 }}" class="px-5 py-10 text-center text-blue-200/60">No assessment results yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $sessions->links() }}</div>

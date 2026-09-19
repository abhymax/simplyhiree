@if(session('success'))
    <div class="mb-5 rounded-2xl border border-emerald-500/50 bg-emerald-500/20 text-emerald-100 font-bold px-5 py-3"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>
@endif

<div class="rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
        <h2 class="text-lg font-bold text-white">Questionnaires</h2>
        <a href="{{ route($routes['create']) }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-bold"><i class="fa-solid fa-plus mr-1"></i>Create Questionnaire</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-blue-950/50 text-cyan-300 uppercase text-xs tracking-wider border-b border-white/10">
                <tr>
                    <th class="px-5 py-3 text-left">Name</th>
                    <th class="px-5 py-3 text-left">Tag</th>
                    <th class="px-5 py-3 text-center">Questions</th>
                    <th class="px-5 py-3 text-center">Pass %</th>
                    <th class="px-5 py-3 text-center">Time</th>
                    <th class="px-5 py-3 text-center">Attempts</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    @if($isAdmin ?? false)<th class="px-5 py-3 text-left">Owner</th>@endif
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 text-slate-200">
                @forelse($assessments as $a)
                    <tr class="hover:bg-white/5">
                        <td class="px-5 py-4 font-bold text-white">{{ $a->name }}</td>
                        <td class="px-5 py-4">@if($a->tag)<span class="text-[11px] font-bold rounded-md border border-white/15 bg-white/5 px-2 py-0.5">{{ $a->tag }}</span>@else <span class="text-slate-500">—</span>@endif</td>
                        <td class="px-5 py-4 text-center">{{ $a->questions_count }}</td>
                        <td class="px-5 py-4 text-center">{{ $a->passing_percentage }}%</td>
                        <td class="px-5 py-4 text-center">{{ $a->time_limit_minutes ? $a->time_limit_minutes.'m' : '—' }}</td>
                        <td class="px-5 py-4 text-center">{{ $a->max_attempts }}</td>
                        <td class="px-5 py-4 text-center"><span class="text-[11px] font-bold {{ $a->status==='active' ? 'text-emerald-300' : 'text-amber-300' }}">{{ ucfirst($a->status) }}</span></td>
                        @if($isAdmin ?? false)<td class="px-5 py-4 text-xs text-slate-400">{{ $a->is_global ? 'Global (Admin)' : optional($a->owner)->name }}</td>@endif
                        <td class="px-5 py-4">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route($routes['edit'], $a) }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold"><i class="fa-solid fa-pen mr-1"></i>Edit</a>
                                <form method="POST" action="{{ route($routes['destroy'], $a) }}" onsubmit="return confirm('Delete this questionnaire? This cannot be undone.')">@csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white text-xs font-bold"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ ($isAdmin ?? false) ? 9 : 8 }}" class="px-5 py-12 text-center text-slate-400">No questionnaires yet. Click <span class="text-cyan-300 font-bold">Create Questionnaire</span> to build one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

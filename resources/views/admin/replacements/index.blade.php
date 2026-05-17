<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-rose-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="mb-6 border-b border-white/10 pb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 text-sm font-bold uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold text-white">Replacements</h1>
                <p class="text-blue-200 mt-1">Review and resolve replacement requests across all clients.</p>
            </div>

            @if(session('success'))
                <div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl font-bold">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('error') }}
                </div>
            @endif

            @php
                $colors = [
                    'window_open'       => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                    'replacement_given' => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                    'credit_pending'    => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                    'closed'            => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                ];
                $labels = [
                    'window_open'       => 'Window Open',
                    'replacement_given' => 'Replacement Given',
                    'credit_pending'    => 'Credit Pending',
                    'closed'            => 'Closed',
                ];
            @endphp

            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.replacements.index') }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
                @foreach($labels as $key => $label)
                    <a href="{{ route('admin.replacements.index', ['status' => $key]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') === $key ? $colors[$key] : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' }}">
                        {{ $label }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-rose-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-5 py-4">Candidate / Job</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Partner</th>
                                <th class="px-5 py-4">Requested</th>
                                <th class="px-5 py-4">Deadline</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($apps as $app)
                                <tr class="hover:bg-white/5 align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-bold">{{ $app->candidate_name ?? '—' }}</div>
                                        <div class="text-xs text-blue-200 mt-0.5">{{ $app->job?->title ?? '—' }}</div>
                                        @if($app->replacement_reason)
                                            <div class="mt-1 text-xs italic text-rose-100/80">"{{ \Illuminate\Support\Str::limit($app->replacement_reason, 100) }}"</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-blue-100">{{ $app->job?->user?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-blue-100">{{ $app->candidate?->partner?->name ?? '—' }}</td>
                                    <td class="px-5 py-4 text-blue-100 text-xs">{{ $app->replacement_requested_at?->format('d M, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-xs">
                                        @if($app->replacement_deadline)
                                            <span class="{{ $app->replacement_deadline->isPast() ? 'text-rose-300 font-bold' : 'text-amber-200' }}">
                                                {{ $app->replacement_deadline->format('d M, Y') }}
                                            </span>
                                            <div class="text-blue-300/70">{{ $app->replacement_deadline->diffForHumans() }}</div>
                                        @else <span class="text-blue-300/40">—</span> @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$app->replacement_status] ?? '' }}">
                                            {{ $labels[$app->replacement_status] ?? $app->replacement_status }}
                                        </span>
                                        @if($app->replacement_application_id)
                                            <div class="text-[10px] text-blue-300/70 mt-1">via app #{{ $app->replacement_application_id }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1.5">
                                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-xs text-cyan-300 hover:text-white underline">View</a>

                                            @if($app->replacement_status === 'window_open')
                                                {{-- Link an existing application as the replacement --}}
                                                <form method="POST" action="{{ route('admin.replacements.approve', $app->id) }}" class="flex flex-col items-end gap-1">
                                                    @csrf
                                                    @php
                                                        // candidates from the same partner for the same job, excluding self
                                                        $candidates = \App\Models\JobApplication::where('job_id', $app->job_id)
                                                            ->where('id', '!=', $app->id)
                                                            ->whereHas('candidate', fn ($q) => $q->where('partner_id', $app->candidate?->partner_id))
                                                            ->with('candidate')
                                                            ->get();
                                                    @endphp
                                                    <select name="replacement_application_id" required class="bg-slate-800 border border-white/10 rounded text-white text-xs px-2 py-1.5 max-w-[180px]">
                                                        <option value="">Pick replacement…</option>
                                                        @foreach($candidates as $c)
                                                            <option value="{{ $c->id }}">#{{ $c->id }} — {{ trim(($c->candidate->first_name ?? '').' '.($c->candidate->last_name ?? '')) ?: 'Candidate' }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="bg-blue-500 hover:bg-blue-400 text-white text-[11px] font-bold px-2.5 py-1 rounded">Approve Replacement</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.replacements.issue-credit', $app->id) }}" onsubmit="return confirm('Issue credit note (skip waiting)?');">
                                                    @csrf
                                                    <button type="submit" class="text-[11px] bg-rose-500 hover:bg-rose-400 text-white font-bold px-2.5 py-1 rounded">Issue Credit Now</button>
                                                </form>
                                            @endif

                                            @if($app->replacement_status !== 'closed')
                                                <form method="POST" action="{{ route('admin.replacements.close', $app->id) }}" onsubmit="return confirm('Manually close this case?');">
                                                    @csrf
                                                    <button type="submit" class="text-[11px] text-slate-300 hover:text-white underline">Close Case</button>
                                                </form>
                                            @endif

                                            @if($app->partnerCreditNote)
                                                <a href="{{ route('admin.credit-notes.index', ['partner_id' => $app->candidate?->partner_id]) }}" class="text-[10px] text-rose-300 hover:text-white underline">View credit note →</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-16 text-center text-blue-200">No replacement cases.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/10">{{ $apps->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

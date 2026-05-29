<x-app-layout>
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6 border-b border-white/10 pb-5">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Candidate Database</h1>
                <p class="text-blue-200 mt-1 text-sm">Search, filter and act on every candidate in your platform.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.candidates.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 text-emerald-200 hover:text-white border border-emerald-400/40 font-bold text-sm transition">
                    <i class="fa-solid fa-file-arrow-down"></i> Export CSV
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 rounded-xl text-sm">
                <i class="fa-solid fa-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Source tabs --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs uppercase tracking-wider text-slate-400 font-bold mr-1">Source:</span>
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold border bg-purple-500/20 text-purple-100 border-purple-400/40 shadow-lg">
                <i class="fa-solid fa-handshake"></i> Vendor-uploaded
                <span class="text-purple-200">{{ $vendorCount }}</span>
            </span>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold border bg-white/5 text-slate-300 border-white/15 hover:bg-white/10 hover:text-white transition">
                <i class="fa-solid fa-user-circle"></i> Direct registrations
                <span class="text-slate-400">{{ $directCount }}</span>
            </a>
            <span class="ml-auto text-xs text-slate-400">
                Showing <span class="text-white font-bold">{{ $candidates->total() }}</span> of {{ $vendorCount }} vendor candidates
            </span>
        </div>

        <style>
            /* Filter inputs — comfortable height */
            .cand-fld {
                height: 38px !important;
                line-height: 1.2 !important;
                width: 100%;
                padding: 0.4rem 0.7rem !important;
                font-size: 0.8125rem !important;
                background: rgba(15,23,42,0.7) !important;
                border: 1px solid rgba(255,255,255,0.18) !important;
                color: #fff !important;
                border-radius: 0.5rem !important;
                color-scheme: dark !important;
                box-sizing: border-box;
            }
            .cand-fld:focus { outline: none; border-color: #22d3ee !important; box-shadow: 0 0 0 1px rgba(34,211,238,0.4); }
            .cand-fld::placeholder { color: rgba(203,213,225,0.45); }

            /* Custom checkbox: cyan-tick on dark bg */
            .cand-cb {
                appearance: none;
                -webkit-appearance: none;
                width: 14px; height: 14px;
                background: rgba(15,23,42,0.7);
                border: 1px solid rgba(255,255,255,0.4);
                border-radius: 3px;
                cursor: pointer;
                position: relative;
                flex-shrink: 0;
            }
            .cand-cb:checked { background: #22d3ee; border-color: #22d3ee; }
            .cand-cb:checked::after {
                content: '✓';
                position: absolute;
                color: #0f172a;
                font-size: 11px;
                font-weight: 900;
                top: -3px; left: 1.5px;
            }
        </style>
        @php
            $fld    = 'cand-fld';
            $hsList = [
                'applied'   => ['Applied',             'fa-paper-plane',  'amber'],
                'screening' => ['Screening',           'fa-magnifying-glass', 'sky'],
                'approved'  => ['Approved',            'fa-check',        'emerald'],
                'interview' => ['Interview Scheduled', 'fa-video',        'indigo'],
                'selected'  => ['Selected',            'fa-user-check',   'cyan'],
                'joined'    => ['Joined',              'fa-trophy',       'emerald'],
                'rejected'  => ['Rejected',            'fa-xmark',        'rose'],
            ];
            $hsCurrent = request('hiring_workflow');
        @endphp

        {{-- Unified Filter Bar --}}
        <form method="GET" action="{{ route('admin.candidates.index') }}" class="mb-6 bg-slate-900/60 backdrop-blur-md border border-white/15 rounded-2xl p-5 shadow-xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                
                {{-- Search (Name, Email, Mobile) --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Name / Email / Mobile</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/50 text-xs pointer-events-none"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                               style="padding-left: 2.25rem !important;"
                               class="cand-fld w-full h-[38px] !max-w-none">
                    </div>
                </div>

                {{-- Skill --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Skill</label>
                    <input type="text" name="skill" value="{{ request('skill') }}" placeholder="e.g. Laravel"
                           class="cand-fld w-full h-[38px] !max-w-none">
                </div>

                {{-- Job Role --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Job Role / Designation</label>
                    <input type="text" name="job_role" value="{{ request('job_role') }}" placeholder="e.g. Developer"
                           class="cand-fld w-full h-[38px] !max-w-none">
                </div>

                {{-- Client --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Client</label>
                    <select name="client_id" class="cand-fld w-full h-[38px] !max-w-none">
                        <option value="" class="bg-slate-900">All Clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" class="bg-slate-900" {{ (string) request('client_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Vendor --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Vendor / Partner</label>
                    <select name="partner_id" class="cand-fld w-full h-[38px] !max-w-none">
                        <option value="" class="bg-slate-900">All Vendors</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}" class="bg-slate-900" {{ (string) request('partner_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Current Status</label>
                    <select name="hiring_workflow" class="cand-fld w-full h-[38px] !max-w-none">
                        <option value="" class="bg-slate-900">All Statuses</option>
                        @foreach($hsList as $k => [$label, $icon, $color])
                            <option value="{{ $k }}" class="bg-slate-900" {{ request('hiring_workflow') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="flex justify-end gap-2 mt-4 border-t border-white/10 pt-4">
                @if(request()->anyFilled(['search', 'skill', 'job_role', 'client_id', 'partner_id', 'hiring_workflow']))
                    <a href="{{ route('admin.candidates.index') }}" 
                       class="px-4 py-2 bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/40 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i> Clear Filters
                    </a>
                @endif
                <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-cyan-900/30">
                    <i class="fa-solid fa-filter"></i> Apply Filters
                </button>
            </div>
        </form>

        {{-- Results table --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-3xl shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Candidate</th>
                            <th class="px-5 py-4">Contact</th>
                            <th class="px-5 py-4">Recruitment</th>
                            <th class="px-5 py-4">Skills</th>
                            <th class="px-5 py-4">Location</th>
                            <th class="px-5 py-4">Source</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($candidates as $c)
                            @php
                                $name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: 'Candidate';
                                $initial = strtoupper(substr($name, 0, 1));
                                $code = $c->candidate_code ?? ('SH-CAN-' . str_pad((string) $c->id, 6, '0', STR_PAD_LEFT));
                                $totalExp = $c->total_experience_years ?? 0;
                                if ($c->total_experience_months) $totalExp .= 'y ' . $c->total_experience_months . 'm';
                                else $totalExp .= 'y';
                            @endphp
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.candidates.show', $c->id) }}" class="flex items-center gap-3 group">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white ring-1 ring-white/20 group-hover:ring-cyan-400 transition">{{ $initial }}</div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-cyan-300 transition">{{ $name }}</div>
                                            <div class="text-[10px] font-mono text-cyan-200">{{ $code }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-xs text-blue-100"><i class="fa-regular fa-envelope mr-1 text-cyan-300"></i>{{ $c->email ?? '—' }}</div>
                                    <div class="text-xs text-blue-200 mt-1"><i class="fa-solid fa-phone mr-1 text-emerald-300"></i>{{ $c->phone_number ?? '—' }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-blue-100">
                                    @if($c->current_company)
                                        <div><span class="text-slate-400">@</span> {{ \Illuminate\Support\Str::limit($c->current_company, 24) }}</div>
                                    @endif
                                    @if($c->current_designation)
                                        <div class="text-slate-300">{{ \Illuminate\Support\Str::limit($c->current_designation, 24) }}</div>
                                    @endif
                                    <div class="mt-1 flex gap-2">
                                        <span class="text-amber-300">{{ $totalExp }}</span>
                                        @if($c->expected_ctc)<span class="text-emerald-300">₹{{ number_format(((float) $c->expected_ctc) / 100000, 1) }}L</span>@endif
                                    </div>
                                    @if($c->notice_period)
                                        <div class="mt-0.5 text-[10px] text-slate-400">NP: {{ $c->notice_period }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($c->skills)
                                        <div class="flex flex-wrap gap-1 max-w-[200px]">
                                            @foreach(array_slice(array_filter(array_map('trim', preg_split('/[,;]+/', $c->skills))), 0, 4) as $skill)
                                                <span class="bg-cyan-500/15 text-cyan-200 border border-cyan-400/30 text-[10px] px-1.5 py-0.5 rounded">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-500 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-blue-100">
                                    <div><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i>{{ $c->location ?? '—' }}</div>
                                    @php $prefLoc = is_array($c->preferred_locations) ? implode(', ', $c->preferred_locations) : (string) $c->preferred_locations; @endphp
                                    @if($prefLoc)
                                        <div class="text-[10px] text-slate-400 mt-0.5">Pref: {{ \Illuminate\Support\Str::limit($prefLoc, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($c->partner)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-purple-500/20 border border-purple-400/40 text-purple-100 text-[11px] font-bold">
                                            <i class="fa-solid fa-handshake"></i> {{ \Illuminate\Support\Str::limit($c->partner->name, 14) }}
                                        </span>
                                    @else
                                        <span class="text-slate-500 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex gap-1.5">
                                        @if($c->resume_path)
                                            <a href="{{ asset('storage/' . $c->resume_path) }}" target="_blank"
                                               class="h-8 w-8 inline-flex items-center justify-center rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/40 transition" title="Resume">
                                                <i class="fa-solid fa-file-pdf text-xs"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.candidates.show', $c->id) }}"
                                           class="px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-lg transition">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <i class="fa-solid fa-users-slash text-5xl text-blue-200 mb-3"></i>
                                    <p class="text-white font-bold">No candidates match your filters.</p>
                                    <p class="text-blue-200 text-sm mt-1">Try widening your search or clearing filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10 bg-slate-900/60">
                <style>
                    nav[role="navigation"] p { color: #cbd5e1 !important; }
                    nav[role="navigation"] span.relative, nav[role="navigation"] a.relative {
                        background-color: rgba(255,255,255,.08) !important;
                        border-color: rgba(255,255,255,.15) !important;
                        color: white !important;
                    }
                    nav[role="navigation"] span[aria-current="page"] span {
                        background-color: #0ea5e9 !important;
                        border-color: #0ea5e9 !important;
                        color: white !important;
                    }
                </style>
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>
</x-app-layout>

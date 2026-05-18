<x-app-layout>
    <style>
        /* Force white calendar icon on date inputs */
        .date-white::-webkit-calendar-picker-indicator { filter: invert(1) brightness(1.5); cursor: pointer; }
        .date-white { color-scheme: dark; }
        /* Hide the default disclosure triangle on the Mark Selected details */
        details.admin-select-details > summary::-webkit-details-marker { display: none; }
        details.admin-select-details > summary { list-style: none; }
        /* Compact applications list */
        .apps-table thead th { padding-top: .75rem !important; padding-bottom: .75rem !important; }
        .apps-table tbody td { padding-top: .75rem !important; padding-bottom: .75rem !important; vertical-align: middle; }
        .apps-table .cand-avatar { width: 36px !important; height: 36px !important; font-size: .9rem !important; }
        .apps-table .cand-name { font-size: .95rem !important; }
        .apps-table .job-name { font-size: .95rem !important; }
        .apps-table .status-pill { padding: .35rem .7rem !important; font-size: .7rem !important; gap: .35rem !important; border-width: 1px !important; }
        .apps-table .action-btn { padding: .45rem .85rem !important; font-size: .75rem !important; }
        .apps-table .action-icon { width: 32px !important; height: 32px !important; }
    </style>
    {{-- FULL PAGE BLUE BACKGROUND WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- High Contrast Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-40 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-screen filter blur-[120px] opacity-40"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">All Applications</h1>
                    <p class="text-blue-100 mt-1 text-lg font-medium">Manage candidate pipeline</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-blue-600 border border-blue-400 text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3">
                        <span class="text-blue-100 text-xs font-bold uppercase tracking-wider">Total Count</span>
                        <span class="text-3xl font-black">{{ $applications->total() }}</span>
                    </div>
                </div>
            </div>

            {{-- MAIN CARD CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- FILTERS (compact single-line on xl+) --}}
                <div class="px-4 py-3 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.applications.index') }}" class="flex flex-wrap items-center gap-2">
                        @php
                            $fldClass = 'h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400';
                        @endphp

                        <div class="relative grow min-w-[180px]">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email"
                                class="{{ $fldClass }} w-full pl-9">
                        </div>

                        <select name="status" title="Status" class="{{ $fldClass }} min-w-[130px]">
                            <option value="" class="text-gray-400">All Statuses</option>
                            @foreach(['Pending Review', 'Approved', 'Rejected', 'Interview Scheduled', 'Selected', 'Joined'] as $status)
                                <option value="{{ $status }}" class="bg-slate-900" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>

                        <select name="job_id" title="Job Role" class="{{ $fldClass }} min-w-[140px] max-w-[200px]">
                            <option value="" class="text-gray-400">All Jobs</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" class="bg-slate-900" {{ request('job_id') == $job->id ? 'selected' : '' }}>{{ Str::limit($job->title, 22) }}</option>
                            @endforeach
                        </select>

                        <select name="partner_id" title="Partner" class="{{ $fldClass }} min-w-[140px] max-w-[200px]">
                            <option value="" class="text-gray-400">All Partners</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" class="bg-slate-900" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>{{ Str::limit($partner->name, 18) }}</option>
                            @endforeach
                        </select>

                        <select name="client_id" title="Client" class="{{ $fldClass }} min-w-[140px] max-w-[200px]">
                            <option value="" class="text-gray-400">All Clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" class="bg-slate-900" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ Str::limit($client->name, 18) }}</option>
                            @endforeach
                        </select>

                        <input type="date" name="date_from" value="{{ request('date_from') }}" max="{{ date('Y-m-d') }}" title="Updated/Approved from"
                            class="{{ $fldClass }} w-[160px] date-white">
                        <span class="text-white font-bold text-sm px-1">to</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" max="{{ date('Y-m-d') }}" title="Updated/Approved to"
                            class="{{ $fldClass }} w-[160px] date-white">

                        <select name="per_page" onchange="this.form.submit()" title="Per page" class="{{ $fldClass }}">
                            @foreach($allowedPerPage as $opt)
                                <option value="{{ $opt }}" class="bg-slate-900" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }}/page</option>
                            @endforeach
                        </select>

                        <button type="submit" title="Apply filters" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm shadow-md shadow-cyan-500/20 transition flex items-center gap-2">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>

                        @if(request()->anyFilled(['search', 'status', 'job_id', 'partner_id', 'per_page', 'date_from', 'date_to']))
                            <a href="{{ route('admin.applications.index') }}" title="Reset filters"
                                class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-lg flex items-center justify-center shadow-md">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Selection / Tracker Download bar --}}
                @if(session('error'))
                    <div class="mx-6 mt-4 px-4 py-3 bg-rose-500/20 border border-rose-500/40 text-rose-100 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mx-6 mt-4 px-4 py-3 bg-emerald-500/20 border border-emerald-500/40 text-emerald-100 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- TRACKER DOWNLOAD FORM (wraps table + action bar) --}}
                <form method="POST" action="{{ route('admin.applications.tracker-export') }}" id="tracker-form">
                    @csrf

                    {{-- Action bar --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-4 border-b border-white/10 bg-slate-900/40">
                        <div class="flex items-center gap-3 text-sm">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-blue-100 font-semibold">
                                <input type="checkbox" id="tracker-select-all" class="h-4 w-4 rounded border-white/30 bg-slate-800 text-cyan-500 focus:ring-cyan-400">
                                Select All On This Page
                            </label>
                            <span class="text-slate-300">|</span>
                            <span class="text-blue-100 font-semibold">Selected: <span id="tracker-count" class="text-cyan-300 font-bold">0</span></span>
                            <span class="text-slate-400 text-xs hidden md:inline">(Max 200 per export)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" id="tracker-submit" disabled
                                class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 disabled:bg-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition border border-emerald-400/40 disabled:border-white/10">
                                <i class="fa-solid fa-file-arrow-down"></i> Tracker Download
                            </button>
                        </div>
                    </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="apps-table min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-5 w-10"><span class="sr-only">Select</span></th>
                                <th class="px-6 py-5">Candidate</th>
                                <th class="px-6 py-5">Job Details</th>
                                <th class="px-6 py-5">Source</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($applications as $application)
                                @php
                                    $agencyCandidate = $application->candidate;
                                    $directCandidate = $application->candidateUser;
                                    $candidateName = trim(($agencyCandidate?->first_name ?? '') . ' ' . ($agencyCandidate?->last_name ?? ''));
                                    if ($candidateName === '') {
                                        $candidateName = $directCandidate?->name ?? 'N/A';
                                    }
                                    $candidateEmail = $agencyCandidate?->email ?? $directCandidate?->email ?? '';
                                    $sourcePartner = $agencyCandidate?->partner;
                                    $initial = strtoupper(substr($candidateName, 0, 1));
                                    $applicationCode = $application->application_code ?? ('SH-APP-' . str_pad((string) $application->id, 6, '0', STR_PAD_LEFT));
                                    $candidateCode = $agencyCandidate?->candidate_code ?? $directCandidate?->entity_code ?? 'SH-CND-NA';
                                    $jobCode = $application->job?->job_code ?? 'SH-JOB-NA';
                                @endphp
                                <tr class="group hover:bg-white/10 transition-all duration-200 transform hover:scale-[1.005] cursor-default border-l-4 border-transparent hover:border-cyan-400">
                                    <td class="px-4 py-5 align-top">
                                        <input type="checkbox" name="ids[]" value="{{ $application->id }}" class="tracker-row-cb h-4 w-4 rounded border-white/30 bg-slate-800 text-cyan-500 focus:ring-cyan-400">
                                    </td>
                                    {{-- Candidate --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="cand-avatar h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-white/20 shrink-0">
                                                {{ $initial !== '' ? $initial : 'U' }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="cand-name font-bold text-white leading-tight">{{ $candidateName }}</div>
                                                <div class="text-cyan-200 text-xs font-medium mt-0.5 truncate"><i class="fa-regular fa-envelope mr-1"></i> {{ $candidateEmail }}</div>
                                                <div class="text-[10px] text-slate-300 font-semibold tracking-wide mt-0.5">
                                                    {{ $applicationCode }} · {{ $candidateCode }} · {{ $application->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Job Details (Fixed High Visibility) --}}
                                    <td class="px-6 py-5">
                                        <div class="job-name font-bold text-white">{{ $application->job->title ?? 'Deleted Job' }}</div>
                                        <div class="text-[10px] text-slate-300 font-semibold tracking-wide mt-0.5">{{ $jobCode }}</div>
                                        <div class="font-bold text-xs mt-1 flex items-center gap-1.5" style="color: #fcd34d;">
                                            <i class="fa-solid fa-building text-amber-400"></i>
                                            {{ $application->job->company_name ?? 'Internal' }}
                                        </div>
                                    </td>

                                    {{-- Source --}}
                                    <td class="px-6 py-5">
                                        @if($sourcePartner)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-600 text-white text-[11px] font-bold shadow-sm">
                                                <i class="fa-solid fa-handshake"></i> {{ Str::limit($sourcePartner->name, 12) }}
                                            </span>
                                            <div class="text-[10px] text-slate-300 font-semibold tracking-wide mt-0.5">
                                                {{ $sourcePartner->entity_code ?? ('SH-PRT-' . str_pad((string) $sourcePartner->id, 6, '0', STR_PAD_LEFT)) }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-700 text-white text-[11px] font-bold border border-slate-500">
                                                <i class="fa-solid fa-globe"></i> Direct
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @php $status = strtolower($application->status); @endphp

                                        @if($status === 'pending review')
                                            <span class="status-pill inline-flex items-center gap-2 rounded-full bg-amber-500 text-black border-2 border-amber-300 text-xs font-extrabold shadow-lg shadow-amber-500/20 animate-pulse">
                                                <i class="fa-regular fa-clock"></i> Pending Review
                                            </span>
                                        @elseif($status === 'approved')
                                            <span class="status-pill inline-flex items-center gap-2 rounded-full bg-emerald-500 text-white border-2 border-emerald-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-check"></i> Approved
                                            </span>
                                            @if($application->auto_forwarded_at)
                                                <div class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider bg-violet-500/20 text-violet-200 border border-violet-400/40 px-2 py-0.5 rounded">
                                                    <i class="fa-solid fa-robot"></i> Auto-forwarded
                                                </div>
                                            @endif
                                        @elseif($status === 'rejected')
                                            <span class="status-pill inline-flex items-center gap-2 rounded-full bg-red-600 text-white border-2 border-red-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-xmark"></i> Rejected
                                            </span>
                                        @else
                                            <span class="status-pill inline-flex items-center gap-2 rounded-full bg-blue-600 text-white border-2 border-blue-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-circle-info"></i> {{ ucfirst($status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions (Bright Icons) --}}
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <div class="flex items-center justify-end gap-2">
                                                @php
                                                    $resumePath = $agencyCandidate?->resume_path ?? $directCandidate?->profile?->resume_path;
                                                @endphp
                                                @if($resumePath)
                                                    <a href="{{ asset('storage/' . $resumePath) }}" target="_blank" title="Download CV" class="action-icon inline-flex items-center justify-center bg-slate-700 hover:bg-cyan-600 text-white rounded-lg shadow-sm transition border border-slate-600 hover:border-cyan-400">
                                                        <i class="fa-solid fa-download text-xs"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.applications.show', $application->id) }}" class="action-btn inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-sm transition border border-indigo-400 whitespace-nowrap">
                                                    @if(strtolower($application->status) === 'pending review')
                                                        Review &amp; Decide
                                                    @else
                                                        View Details
                                                    @endif
                                                </a>
                                            </div>

                                            @if(strtolower($application->status) === 'approved' && !in_array($application->hiring_status, ['Selected', 'Joined']))
                                                <details class="admin-select-details" style="text-align: right;">
                                                    <summary style="list-style: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; background: #059669; color: #fff; padding: 0.375rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700; border: 1px solid #34d399; white-space: nowrap;">
                                                        <i class="fa-solid fa-user-check"></i> Mark Selected (on behalf of client)
                                                    </summary>
                                                    <form method="POST"
                                                          action="{{ route('admin.applications.adminSelect', $application->id) }}"
                                                          style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; width: 18rem; background: rgba(15,23,42,0.8); border: 1px solid rgba(52,211,153,0.4); padding: 0.75rem; border-radius: 0.5rem; text-align: left;">
                                                        @csrf
                                                        <label style="font-size: 10px; color: #a7f3d0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Joining Date *</label>
                                                        <input type="date" name="joining_date" required min="{{ date('Y-m-d') }}" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; color: #fff; font-size: 14px; padding: 6px;">
                                                        <label style="font-size: 10px; color: #a7f3d0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Final CTC (₹)</label>
                                                        <input type="number" name="final_ctc" min="0" step="0.01" placeholder="Optional" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; color: #fff; font-size: 14px; padding: 6px;">
                                                        <label style="font-size: 10px; color: #a7f3d0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Notes</label>
                                                        <textarea name="admin_notes" rows="2" maxlength="1000" placeholder="Optional" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; color: #fff; font-size: 12px; padding: 6px;"></textarea>
                                                        <div style="display: flex; gap: 0.5rem;">
                                                            <button type="submit" style="background: #10b981; color: #fff; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 4px; border: 0;">Confirm Select</button>
                                                        </div>
                                                    </form>
                                                </details>
                                            @endif

                                            @if($application->selected_by_admin_id)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider bg-purple-500/20 text-purple-200 border border-purple-400/40 px-2 py-0.5 rounded">
                                                    <i class="fa-solid fa-user-shield"></i> Selected by Superadmin
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-regular fa-folder-open text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No applications found.</p>
                                        <p class="text-blue-200 mt-2">Adjust filters or check back later.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </form>
                {{-- /TRACKER DOWNLOAD FORM --}}

                {{-- PAGINATION FIX (Forces White Text) --}}
                <div class="p-6 border-t border-white/10 bg-slate-900/80 backdrop-blur-md">
                    {{-- Force Laravel Pagination Styles --}}
                    <style>
                        /* Target the 'Showing 1 to 10' text */
                        nav[role="navigation"] div.hidden div p.text-sm {
                            color: #e2e8f0 !important; /* Light Slate */
                            font-size: 0.95rem;
                        }
                        /* Target the pagination buttons */
                        nav[role="navigation"] span.relative, nav[role="navigation"] a.relative {
                            background-color: rgba(255, 255, 255, 0.1) !important;
                            border-color: rgba(255, 255, 255, 0.2) !important;
                            color: white !important;
                            font-weight: 700;
                        }
                        /* Active Page */
                        nav[role="navigation"] span[aria-current="page"] span {
                            background-color: #0ea5e9 !important; /* Cyan-500 */
                            border-color: #0ea5e9 !important;
                            color: white !important;
                        }
                    </style>
                    {{ $applications->links() }} 
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const MAX = 200;
        const selectAll = document.getElementById('tracker-select-all');
        const submit    = document.getElementById('tracker-submit');
        const counter   = document.getElementById('tracker-count');
        const form      = document.getElementById('tracker-form');
        if (!form) return;
        const rowCbs    = () => Array.from(form.querySelectorAll('.tracker-row-cb'));

        const updateState = () => {
            const checked = rowCbs().filter(c => c.checked);
            counter.textContent = checked.length;
            submit.disabled = checked.length === 0;
            if (checked.length > MAX) {
                submit.disabled = true;
                counter.classList.add('text-rose-300');
                counter.classList.remove('text-cyan-300');
            } else {
                counter.classList.remove('text-rose-300');
                counter.classList.add('text-cyan-300');
            }
            if (selectAll) {
                const all = rowCbs();
                selectAll.checked = all.length > 0 && checked.length === all.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        };

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                rowCbs().forEach(c => c.checked = selectAll.checked);
                updateState();
            });
        }
        rowCbs().forEach(c => c.addEventListener('change', updateState));

        form.addEventListener('submit', (e) => {
            const checked = rowCbs().filter(c => c.checked);
            if (checked.length === 0) {
                e.preventDefault();
                alert('Select at least one candidate.');
                return;
            }
            if (checked.length > MAX) {
                e.preventDefault();
                alert('You can export at most ' + MAX + ' candidates at a time. You selected ' + checked.length + '.');
                return;
            }
        });

        updateState();
    });
    </script>
</x-app-layout>

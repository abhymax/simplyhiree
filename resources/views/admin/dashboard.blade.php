<x-app-layout title="Superadmin Dashboard">
    {{-- 
        FULL PAGE BLUE BACKGROUND WRAPPER 
        - 'min-h-screen' ensures it covers the whole page.
        - '-m-6' negative margins cancel out default padding from the layout if any exists.
    --}}
    <div class="min-h-screen bg-transparent text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-7 relative overflow-hidden">

        {{-- DECORATIVE BACKGROUND GLOWS --}}

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER SECTION --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-5 border-b border-white/10 pb-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                            Superadmin Control
                        </span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                        Overview
                    </h1>
                    @php
                        $hour = now()->hour;
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                        $welcomeLine = $hour < 12 ? 'A clear queue and strong decisions make a good start.' : ($hour < 17 ? 'The platform is moving. Let’s keep every hiring lane on track.' : 'One last look at the numbers before the day winds down.');
                    @endphp
                    <p class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-blue-100"><i class="fa-solid {{ $hour < 12 ? 'fa-sun text-amber-300' : ($hour < 17 ? 'fa-bolt text-cyan-300' : 'fa-moon text-violet-300') }}"></i><span class="text-white font-semibold">{{ $greeting }}, {{ Auth::user()->name }}.</span><span class="text-blue-200/90">{{ $welcomeLine }}</span></p>
                </div>
            </div>

            {{-- DAILY PULSE --}}
            <section class="mb-7" aria-labelledby="daily-pulse-heading">
                <div class="mb-3 flex items-center gap-2">
                    <span class="h-6 w-1 rounded-full bg-gradient-to-b from-cyan-300 to-blue-500"></span>
                    <h2 id="daily-pulse-heading" class="text-lg font-extrabold text-white">Daily Pulse</h2>
                    <span class="text-xs text-slate-400">Today's operations</span>
                </div>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <a href="{{ route('admin.interviews.today') }}" class="priority-card priority-card-interviews group relative overflow-hidden rounded-2xl border border-cyan-300/25 p-4">
                        <div class="relative z-10 flex h-full items-center justify-between gap-5">
                            <div><div class="mb-1.5 flex items-center gap-2 text-cyan-100"><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-300/15"><i class="fa-solid fa-video"></i></span><h3 class="font-extrabold">Interviews Today</h3></div><div class="flex items-baseline gap-3"><span class="text-3xl font-black text-white">{{ $todayInterviews }}</span><span class="text-sm text-cyan-100/80">scheduled today</span></div></div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-cyan-200/30 text-cyan-100 transition group-hover:translate-x-1"><i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                    </a>
                    <a href="{{ route('admin.billing.index') }}" class="priority-card priority-card-billing group relative overflow-hidden rounded-2xl border border-amber-300/25 p-4">
                        <div class="relative z-10 flex h-full items-center justify-between gap-5">
                            <div><div class="mb-1.5 flex items-center gap-2 text-amber-100"><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-300/15"><i class="fa-solid fa-file-invoice-dollar"></i></span><h3 class="font-extrabold">Invoices Due</h3></div><div class="flex items-baseline gap-3"><span class="text-3xl font-black text-white">{{ $dueInvoicesCount }}</span><span class="text-sm text-amber-100/80">pending payments</span></div></div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-amber-200/30 text-amber-100 transition group-hover:translate-x-1"><i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            </section>

            @role('Superadmin')
            {{-- MASTER OPERATIONAL DASHBOARD --}}
            <section class="master-dashboard-panel mb-7" aria-label="Operational overview">
                <div class="mb-3 flex justify-end">
                    <span class="text-xs font-bold uppercase text-blue-300">Updated {{ now()->format('h:i A') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('admin.clients.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-blue-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-blue-200">Total Clients</h3><i class="fa-solid fa-building text-blue-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $totalClients }}</p>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs"><span class="text-emerald-300">{{ $activeClients }} Active</span><span class="text-slate-300">{{ $inactiveClients }} Inactive</span><span class="text-amber-300">{{ $unpaidClients }} Unpaid</span></div>
                    </a>

                    <a href="{{ route('admin.partners.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-purple-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-purple-200">Total Vendors</h3><i class="fa-solid fa-handshake text-purple-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $totalPartners }}</p>
                        <div class="mt-3 flex gap-5 text-xs"><span class="text-emerald-300">{{ $activePartners }} Active</span><span class="text-rose-300" title="Restricted vendor accounts">{{ $blacklistedPartners }} Blacklisted</span></div>
                    </a>

                    <a href="{{ route('admin.reports.jobs') }}" class="master-metric-card min-h-28 rounded-2xl border border-cyan-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-cyan-200">Jobs</h3><i class="fa-solid fa-briefcase text-cyan-400"></i></div>
                        <div class="mt-3 flex items-end gap-6"><div><p class="text-3xl font-extrabold text-white">{{ $openJobs }}</p><p class="text-xs text-emerald-300">Open</p></div><div><p class="text-3xl font-extrabold text-white">{{ $closedJobs }}</p><p class="text-xs text-slate-300">Closed</p></div></div>
                    </a>

                    <a href="{{ route('admin.applications.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-indigo-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-indigo-200">Total Submissions</h3><i class="fa-solid fa-file-arrow-up text-indigo-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $totalSubmissions }}</p>
                        <p class="mt-3 text-xs text-amber-300">{{ $pendingApplications }} awaiting review</p>
                    </a>

                    <a href="{{ route('admin.interviews.today') }}" class="master-metric-card min-h-28 rounded-2xl border border-fuchsia-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-fuchsia-200">Interviews Scheduled</h3><i class="fa-solid fa-video text-fuchsia-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $scheduledInterviews }}</p>
                        <p class="mt-3 text-xs text-blue-300">{{ $todayInterviews }} scheduled today</p>
                        @if(($awaitingInterviewOutcome ?? 0) > 0)
                            <p class="mt-1 text-xs font-bold text-amber-300"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $awaitingInterviewOutcome }} past due &mdash; outcome not marked</p>
                        @endif
                    </a>

                    <a href="{{ route('admin.applications.index', ['joined_status' => 'Joined']) }}" class="master-metric-card min-h-28 rounded-2xl border border-emerald-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-emerald-200">Joinings This Month</h3><i class="fa-solid fa-user-check text-emerald-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $joiningsThisMonth }}</p>
                        <p class="mt-3 text-xs text-slate-300">Confirmed joined candidates</p>
                    </a>

                    <a href="{{ route('admin.billing.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-teal-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-teal-200">Revenue Generated</h3><i class="fa-solid fa-indian-rupee-sign text-teal-400"></i></div>
                        <p class="mt-3 text-2xl font-extrabold text-white">₹{{ number_format($revenueThisMonth, 2) }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs"><span class="text-slate-300">Quarter ₹{{ number_format($revenueThisQuarter, 2) }}</span><span class="text-slate-300">Year ₹{{ number_format($revenueThisYear, 2) }}</span></div>
                    </a>

                    <a href="{{ route('admin.billing.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-amber-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-amber-200">Outstanding Payments</h3><i class="fa-solid fa-file-invoice-dollar text-amber-400"></i></div>
                        <p class="mt-3 text-2xl font-extrabold text-white">₹{{ number_format($outstandingPayments, 2) }}</p>
                        <p class="mt-3 text-xs text-amber-300">{{ $dueInvoicesCount }} overdue invoices</p>
                    </a>

                    <a href="{{ route('admin.replacements.index') }}" class="master-metric-card min-h-28 rounded-2xl border border-rose-400/20 p-4 transition">
                        <div class="flex items-center justify-between"><h3 class="text-sm font-bold text-rose-200">Replacement Under Guarantee</h3><i class="fa-solid fa-rotate text-rose-400"></i></div>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $replacementUnderGuarantee }}</p>
                        <p class="mt-3 text-xs text-slate-300">Live guarantee-window counter</p>
                    </a>
                </div>
            </section>
            @endrole

            {{-- PLAN UPGRADE REQUESTS CARD --}}
            @if(($pendingPlanRequestsCount ?? 0) > 0 || ($pendingPlanRequests ?? collect())->isNotEmpty())
                @php $puMaxId = $pendingPlanRequests->max('id'); @endphp
                <div id="pu-notice"
                     data-max-id="{{ $puMaxId }}"
                     class="mt-8 bg-gradient-to-br from-cyan-900/40 to-purple-900/40 backdrop-blur-md border border-cyan-400/40 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="px-6 py-4 border-b border-cyan-400/20 flex items-center justify-between gap-3">
                        <h3 class="text-cyan-100 font-extrabold text-lg flex items-center gap-2">
                            <i class="fa-solid fa-rocket"></i> Plan Upgrade Requests
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full"
                                  style="background-color: #22d3ee !important; color: #0f172a !important;">{{ $pendingPlanRequestsCount }} pending</span>
                        </h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.plan-requests.index') }}" class="text-cyan-200 hover:text-white text-xs font-bold underline">View all →</a>
                            <button type="button"
                                onclick="dismissNotice('pu-notice','pu_dismissed_id', this.closest('#pu-notice').dataset.maxId)"
                                title="Dismiss until new requests arrive"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-cyan-500/20 hover:bg-cyan-500/40 text-cyan-100 hover:text-white border border-cyan-400/30">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="divide-y divide-cyan-400/10">
                        @foreach($pendingPlanRequests as $r)
                            <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="text-white font-bold">{{ $r->partner?->name ?? '—' }}
                                        <span class="text-cyan-100/80 text-sm font-normal">wants to switch from</span>
                                        <span class="text-white font-bold">{{ $r->current_plan }}</span>
                                        <i class="fa-solid fa-arrow-right text-slate-400 mx-1"></i>
                                        <span class="text-white font-bold">{{ $r->requested_plan }}</span>
                                    </div>
                                    <div class="text-cyan-100/70 text-xs mt-0.5">
                                        {{ $r->partner?->email }}
                                        @php $phone = $r->partner?->profile?->phone_number; @endphp
                                        @if($phone) · <a href="tel:{{ $phone }}" class="text-emerald-300 hover:text-white"><i class="fa-solid fa-phone mr-0.5"></i>{{ $phone }}</a>@endif
                                        · {{ $r->created_at->diffForHumans() }}
                                    </div>
                                    @if($r->notes)
                                        <div class="mt-1 text-cyan-100/80 text-sm italic">"{{ \Illuminate\Support\Str::limit($r->notes, 160) }}"</div>
                                    @endif
                                </div>
                                <a href="{{ route('admin.plan-requests.index') }}"
                                   class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap transition"
                                   style="background-color: #22d3ee !important; color: #0f172a !important;"
                                   onmouseover="this.style.backgroundColor='#67e8f9'"
                                   onmouseout="this.style.backgroundColor='#22d3ee'">
                                    <i class="fa-solid fa-headset"></i> Review &amp; Contact
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- VENDOR ASSIGNMENT REQUESTS CARD --}}
            @if(($pendingVendorAssignmentCount ?? 0) > 0 || ($pendingVendorAssignmentRequests ?? collect())->isNotEmpty())
                @php $vaMaxId = $pendingVendorAssignmentRequests->max('id'); @endphp
                <div id="va-notice"
                     data-max-id="{{ $vaMaxId }}"
                     class="mt-6 bg-gradient-to-br from-amber-900/40 to-orange-900/40 backdrop-blur-md border border-amber-400/40 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="px-6 py-4 border-b border-amber-400/20 flex items-center justify-between gap-3">
                        <h3 class="text-amber-100 font-extrabold text-lg flex items-center gap-2">
                            <i class="fa-solid fa-handshake"></i> Vendor Assignment Requests
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full"
                                  style="background-color: #fbbf24 !important; color: #0f172a !important;">{{ $pendingVendorAssignmentCount }} pending</span>
                        </h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.vendor-assignment-requests.index') }}" class="text-amber-200 hover:text-white text-xs font-bold underline">View all →</a>
                            <button type="button"
                                onclick="dismissNotice('va-notice','va_dismissed_id', this.closest('#va-notice').dataset.maxId)"
                                title="Dismiss until new requests arrive"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-500/20 hover:bg-amber-500/40 text-amber-100 hover:text-white border border-amber-400/30">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    <div class="divide-y divide-amber-400/10">
                        @foreach($pendingVendorAssignmentRequests as $r)
                            <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="text-white font-bold">{{ $r->client?->name ?? '—' }}
                                        <span class="text-amber-100/80 text-sm font-normal">wants</span>
                                        <span class="text-white font-bold">{{ $r->vendor_count }} vendor(s) assigned</span>
                                    </div>
                                    <div class="text-amber-100/70 text-xs mt-0.5">
                                        {{ $r->client?->email }}
                                        @if($r->industry_hint) · <i class="fa-solid fa-briefcase mr-0.5"></i>{{ $r->industry_hint }}@endif
                                        @if($r->location_hint) · <i class="fa-solid fa-location-dot mr-0.5"></i>{{ $r->location_hint }}@endif
                                        · {{ $r->created_at->diffForHumans() }}
                                    </div>
                                    @if($r->notes)
                                        <div class="mt-1 text-amber-100/80 text-sm italic">"{{ \Illuminate\Support\Str::limit($r->notes, 160) }}"</div>
                                    @endif
                                </div>
                                <a href="{{ route('admin.vendor-assignment-requests.show', $r) }}"
                                   class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap transition"
                                   style="background-color: #fbbf24 !important; color: #0f172a !important;"
                                   onmouseover="this.style.backgroundColor='#fcd34d'"
                                   onmouseout="this.style.backgroundColor='#fbbf24'">
                                    <i class="fa-solid fa-user-plus"></i> Assign Vendors
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- SECTION 2: QUICK ACTIONS (5 Items) --}}
            <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
                <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
            </h3>

            <div class="quick-actions-grid grid grid-cols-1 md:grid-cols-3 gap-3 mb-8">
                {{-- 1. Post Job --}}
                @can('view_pending_jobs')
                <a href="{{ route('admin.jobs.create') }}" class="quick-action-card quick-action-job group rounded-2xl p-4 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                    <div class="relative z-10">
                        <div class="h-10 w-10 bg-white/20 rounded-lg flex items-center justify-center mb-3 backdrop-blur-sm">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h4 class="font-bold text-lg">Post Job</h4>
                        <p class="text-blue-200 text-xs">Create vacancy</p>
                    </div>
                </a>
                @endcan

                {{-- 2. Add Client --}}
                @can('manage_clients')
                <a href="{{ route('admin.clients.create') }}" class="quick-action-card quick-action-client group rounded-2xl p-4 relative overflow-hidden"><div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                    <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h4 class="font-bold text-white">Add Client</h4>
                    <p class="text-slate-400 text-xs">Onboard company</p>
                </a>
                @endcan

                {{-- 3. Add Partner --}}
                @can('view_partner_data')
                <a href="{{ route('admin.partners.create') }}" class="quick-action-card quick-action-partner group rounded-2xl p-4 relative overflow-hidden"><div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                    <div class="h-10 w-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <h4 class="font-bold text-white">Add Partner</h4>
                    <p class="text-slate-400 text-xs">Register agency</p>
                </a>
                @endcan

                {{-- 4. Managers --}}
                @can('manage_sub_admins')
                <a href="{{ route('admin.sub_admins.index') }}" class="quick-action-card quick-action-managers group rounded-2xl p-4 relative overflow-hidden"><div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                    <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h4 class="font-bold text-white">Managers</h4>
                    <p class="text-slate-400 text-xs">Access Control</p>
                </a>
                @endcan

                {{-- 6. Broadcast --}}
                <a href="{{ route('admin.broadcasts.index') }}" class="quick-action-card quick-action-broadcast group rounded-2xl p-4 relative overflow-hidden"><div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                    <div class="h-10 w-10 bg-orange-500/20 text-orange-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h4 class="font-bold text-white">Broadcast</h4>
                    <p class="text-slate-400 text-xs">Message all vendors</p>
                </a>
            </div>

            {{-- SECTION 3: LIVE METRICS (5 Items) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-8">
                {{-- Pending Jobs --}}
                @can('view_pending_jobs')
                <a href="{{ route('admin.jobs.pending') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Pending Jobs</span>
                        <i class="fa-solid fa-clock text-amber-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $pendingJobs }}</div>
                </a>
                @endcan

                {{-- Review Apps --}}
                @can('view_application_data')
                <a href="{{ route('admin.applications.index', ['status' => 'Pending Review']) }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Review Apps</span>
                        <i class="fa-solid fa-file-contract text-rose-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $pendingApplications }}</div>
                </a>
                @endcan

                {{-- Total Clients --}}
                @can('manage_clients')
                <a href="{{ route('admin.clients.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Clients</span>
                        <i class="fa-solid fa-user-tie text-emerald-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $totalClients }}</div>
                </a>
                @endcan

                {{-- Total Partners --}}
                @can('view_partner_data')
                <a href="{{ route('admin.partners.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Partners</span>
                        <i class="fa-solid fa-handshake text-purple-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $totalPartners }}</div>
                </a>
                @endcan

                {{-- Total Candidates (direct + vendor-uploaded) --}}
                @can('view_candidate_data')
                <a href="{{ route('admin.candidates.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Candidates</span>
                        <i class="fa-solid fa-users text-blue-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $totalCandidates }}</div>
                    <div class="mt-2 pt-2 border-t border-white/10 flex justify-between text-[11px] font-bold">
                        <span class="text-cyan-300" title="Candidates who signed up themselves">
                            <i class="fa-solid fa-user-circle mr-1"></i>Direct {{ $directCandidates }}
                        </span>
                        <span class="text-purple-300" title="Candidates uploaded by partner agencies">
                            <i class="fa-solid fa-handshake mr-1"></i>Vendor {{ $vendorCandidates }}
                        </span>
                    </div>
                </a>
                @endcan
            </div>

            {{-- SECTION 4: CHARTS (Dark Mode Style) --}}
            @if(auth()->user()->can('view_billing_data') || auth()->user()->hasRole('Superadmin'))
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Growth Chart --}}
                <div class="lg:col-span-2 bg-white/10 backdrop-blur-md border border-white/10 p-8 rounded-3xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-white">Activity Growth</h3>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full bg-white/10 text-xs text-white border border-white/10">Weekly</span>
                        </div>
                    </div>
                    <div class="h-72 w-full">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>

                {{-- User Distribution --}}
                <div class="lg:col-span-1 bg-white/10 backdrop-blur-md border border-white/10 p-8 rounded-3xl flex flex-col justify-between">
                    <h3 class="text-lg font-bold text-white mb-4">User Ecosystem</h3>
                    <div class="h-48 w-full flex justify-center">
                        <canvas id="userDistChart"></canvas>
                    </div>
                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between text-sm border-b border-white/10 pb-2">
                            <span class="flex items-center text-slate-300"><span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span> Candidates</span>
                            <span class="font-bold text-white">{{ $totalCandidates }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-b border-white/10 pb-2">
                            <span class="flex items-center text-slate-300"><span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span> Clients</span>
                            <span class="font-bold text-white">{{ $totalClients }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="flex items-center text-slate-300"><span class="w-2 h-2 rounded-full bg-purple-400 mr-2"></span> Partners</span>
                            <span class="font-bold text-white">{{ $totalPartners }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <style>
        .has-admin-sidebar main section.master-dashboard-panel{background:transparent!important;border:0!important;box-shadow:none!important;backdrop-filter:none!important}
        .has-admin-sidebar main section.master-dashboard-panel:hover{transform:none!important;box-shadow:none!important;border-color:transparent!important}
        .master-metric-card{position:relative;overflow:hidden;box-shadow:0 10px 24px rgba(2,6,23,.16);transition:transform .24s ease,border-color .24s ease,box-shadow .24s ease,background-color .24s ease!important}
        .master-metric-card::after,.quick-action-card::after,.priority-card::after{content:"";position:absolute;inset:-30% auto -30% -46%;width:24%;transform:skewX(-20deg);background:linear-gradient(90deg,transparent,rgba(255,255,255,.30),transparent);opacity:0;pointer-events:none;transition:left .64s ease,opacity .22s ease}
        .master-metric-card:hover::after,.quick-action-card:hover::after,.priority-card:hover::after{left:122%;opacity:1}
        .master-metric-card:hover{transform:translateY(-4px) scale(1.008)!important;border-color:rgba(165,243,252,.42)!important;box-shadow:0 18px 36px rgba(2,6,23,.30),0 0 22px rgba(34,211,238,.10)}
        .master-metric-card i{transition:transform .24s ease,filter .24s ease}
        .master-metric-card:hover i{transform:scale(1.18) rotate(-4deg);filter:brightness(1.2)}
        .master-metric-card:nth-child(1){background:linear-gradient(125deg,rgba(6,182,212,.38),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(2){background:linear-gradient(125deg,rgba(168,85,247,.38),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(3){background:linear-gradient(125deg,rgba(251,146,60,.37),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(4){background:linear-gradient(125deg,rgba(244,114,182,.34),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(5){background:linear-gradient(125deg,rgba(129,140,248,.38),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(6){background:linear-gradient(125deg,rgba(52,211,153,.34),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(7){background:linear-gradient(125deg,rgba(45,212,191,.34),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(8){background:linear-gradient(125deg,rgba(250,204,21,.34),rgba(15,23,42,.78) 70%)!important}
        .master-metric-card:nth-child(9){background:linear-gradient(125deg,rgba(251,113,133,.34),rgba(15,23,42,.78) 70%)!important}
        .priority-card{min-height:7.5rem;box-shadow:0 12px 26px rgba(2,6,23,.20);transition:transform .24s ease,box-shadow .24s ease,border-color .24s ease}
        .priority-card:hover{transform:translateY(-4px);box-shadow:0 22px 42px rgba(2,6,23,.32)}
        .priority-card-interviews{background:linear-gradient(118deg,rgba(8,145,178,.38),rgba(30,41,59,.82) 68%)}
        .priority-card-billing{background:linear-gradient(118deg,rgba(180,83,9,.38),rgba(30,41,59,.82) 68%)}
        .quick-action-card{min-height:10.25rem;border:1px solid rgba(255,255,255,.18);background-color:rgba(15,23,42,.46);backdrop-filter:blur(18px);box-shadow:0 12px 26px rgba(2,6,23,.18);transition:transform .24s ease,box-shadow .24s ease,filter .24s ease,border-color .24s ease!important}
        .quick-action-card:hover{transform:translateY(-4px)!important;border-color:rgba(255,255,255,.34);box-shadow:0 20px 34px rgba(2,6,23,.28),0 0 18px rgba(148,163,184,.10);filter:brightness(1.04)}
        .quick-action-job{background-image:linear-gradient(135deg,rgba(59,130,246,.20),rgba(15,23,42,.58) 68%)!important}
        .quick-action-client{background-image:linear-gradient(135deg,rgba(45,212,191,.16),rgba(15,23,42,.58) 68%)!important}
        .quick-action-partner{background-image:linear-gradient(135deg,rgba(192,132,252,.16),rgba(15,23,42,.58) 68%)!important}
        .quick-action-managers{background-image:linear-gradient(135deg,rgba(244,114,182,.15),rgba(15,23,42,.58) 68%)!important}
        .quick-action-broadcast{background-image:linear-gradient(135deg,rgba(251,191,36,.15),rgba(15,23,42,.58) 68%)!important}
        @media(min-width:768px){.quick-actions-grid{grid-template-columns:repeat(auto-fit,minmax(180px,1fr))!important}}
        #admin-weather-widget{position:relative;overflow:hidden}
        #admin-weather-widget::after{content:"";position:absolute;inset:0 auto 0 -35%;width:22%;transform:skewX(-18deg);background:linear-gradient(90deg,transparent,rgba(255,255,255,.10),transparent);animation:weather-shine 5s ease-in-out infinite;pointer-events:none}
        #admin-weather-icon{animation:weather-float 2.8s ease-in-out infinite;transform-origin:center}
        #admin-weather-status{animation:weather-status-live 2.2s ease-in-out infinite}
        @keyframes weather-float{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-3px) scale(1.07)}}
        @keyframes weather-status-live{0%,100%{box-shadow:0 0 0 rgba(96,165,250,0)}50%{box-shadow:0 0 16px rgba(96,165,250,.24)}}
        @keyframes weather-shine{0%,65%{left:-35%;opacity:0}75%{opacity:1}100%{left:120%;opacity:0}}
        @media(prefers-reduced-motion:reduce){.master-metric-card:hover,.quick-action-card:hover,.priority-card:hover{transform:none!important}.master-metric-card i{transition:none}.master-metric-card::after,.quick-action-card::after,.priority-card::after{display:none}}
    </style>
    <script>
        document.addEventListener('DOMContentLoaded',function(){
            const timeEl=document.getElementById('admin-widget-time'),iconEl=document.getElementById('admin-weather-icon'),tempEl=document.getElementById('admin-weather-temp'),descEl=document.getElementById('admin-weather-desc'),statusEl=document.getElementById('admin-weather-status');
            const tick=()=>{const now=new Date();timeEl.textContent=now.toLocaleTimeString('en-IN',{hour:'numeric',minute:'2-digit',second:'2-digit',hour12:true})};
            const weather=(lat,lon)=>fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code`).then(r=>r.json()).then(data=>{const c=data.current;if(!c)throw new Error();const code=c.weather_code,rain=(code>=51&&code<=67)||(code>=80&&code<=82)||code===95,cloud=(code>=1&&code<=3)||(code>=45&&code<=48);const icon=code===0?'fa-sun text-amber-300':rain?'fa-cloud-rain text-blue-300':cloud?'fa-cloud text-slate-300':'fa-cloud-sun text-amber-200';iconEl.className=`fa-solid weather-live-icon ${icon} text-2xl w-8 text-center`;tempEl.textContent=`${Math.round(c.temperature_2m)}°C`;descEl.textContent=code===0?'Clear sky':rain?'Rain showers':cloud?'Cloudy':'Partly cloudy';statusEl.textContent=rain?'Take an umbrella':'Perfect weather';document.getElementById('admin-weather-widget').dataset.updatedAt=new Date().toISOString()}).catch(()=>{descEl.textContent='Unavailable';statusEl.textContent='Weather unavailable'});
            tick();setInterval(tick,1000);
            let weatherCoords={lat:28.61,lon:77.20};const refreshWeather=()=>weather(weatherCoords.lat,weatherCoords.lon);refreshWeather();if(navigator.geolocation)navigator.geolocation.getCurrentPosition(p=>{weatherCoords={lat:p.coords.latitude,lon:p.coords.longitude};refreshWeather()},()=>{}, {timeout:5000});setInterval(refreshWeather,600000);
        });
    </script>

    {{-- CHART SCRIPTS (Updated for Dark Mode) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark Mode Chart Configuration
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';

            const totalClients = {{ $totalClients }};
            const totalPartners = {{ $totalPartners }};
            const totalCandidates = {{ $totalCandidates }};

            // User Distribution
            const ctxDist = document.getElementById('userDistChart');
            if (ctxDist) {
                new Chart(ctxDist.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Candidates', 'Clients', 'Partners'],
                        datasets: [{
                            data: [totalCandidates, totalClients, totalPartners],
                            backgroundColor: ['#60a5fa', '#34d399', '#c084fc'], // Bright colors for dark bg
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        cutout: '75%', 
                        plugins: { legend: { display: false } } 
                    }
                });
            }

            // Growth Chart
            const ctxGrowth = document.getElementById('growthChart');
            if (ctxGrowth) {
                const ctx = ctxGrowth.getContext('2d');
                let gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(96, 165, 250, 0.5)'); // Blue-400
                gradient.addColorStop(1, 'rgba(96, 165, 250, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Platform Activity',
                            data: [12, 19, 15, 25, 22, 30, 45],
                            borderColor: '#60a5fa',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } }, 
                        scales: { 
                            y: { beginAtZero: true, grid: { borderDash: [4, 4], color: 'rgba(255,255,255,0.05)' } }, 
                            x: { grid: { display: false } } 
                        } 
                    }
                });
            }
        });
    </script>

    <script>
        // Dismiss notification cards (Vendor Assignment / Plan Upgrade) — remembers
        // the latest request id that was dismissed in localStorage. When new
        // requests come in (max-id > dismissed-id), the card reappears.
        function dismissNotice(elId, storageKey, maxId) {
            try { localStorage.setItem(storageKey, String(maxId || '0')); } catch (e) {}
            const el = document.getElementById(elId);
            if (el) el.style.display = 'none';
        }
        document.addEventListener('DOMContentLoaded', function () {
            ['va-notice|va_dismissed_id', 'pu-notice|pu_dismissed_id'].forEach(function (pair) {
                const [elId, key] = pair.split('|');
                const el = document.getElementById(elId);
                if (!el) return;
                const maxId = parseInt(el.dataset.maxId || '0', 10);
                let dismissed = 0;
                try { dismissed = parseInt(localStorage.getItem(key) || '0', 10); } catch (e) {}
                if (maxId && dismissed >= maxId) el.style.display = 'none';
            });
        });
    </script>
</x-app-layout>

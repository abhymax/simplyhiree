<x-app-layout title="Superadmin Dashboard">
    {{-- 
        FULL PAGE BLUE BACKGROUND WRAPPER 
        - 'min-h-screen' ensures it covers the whole page.
        - '-m-6' negative margins cancel out default padding from the layout if any exists.
    --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">

        {{-- DECORATIVE BACKGROUND GLOWS --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER SECTION --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                            Superadmin Control
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">
                        Overview
                    </h1>
                    <p class="text-blue-200 mt-2 text-lg">
                        Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.
                    </p>
                </div>
                <div class="mt-6 md:mt-0">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl flex items-center gap-4">
                        <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg shadow-lg">
                            <i class="fa-regular fa-calendar text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs text-blue-300 font-bold uppercase">Today's Date</p>
                            <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 1: DAILY PULSE (High Priority) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
                {{-- Interviews Card --}}
                <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                    <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition transform group-hover:scale-110 duration-500">
                            <i class="fa-solid fa-users-viewfinder text-9xl text-white"></i>
                        </div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6">
                                <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-video"></i></span>
                                <h3 class="font-bold text-xl text-white">Interviews Today</h3>
                            </div>
                            
                            <div class="flex items-baseline gap-4">
                                <span class="text-6xl font-black text-white tracking-tighter">{{ $todayInterviews }}</span>
                                <span class="text-blue-200 font-medium">Scheduled</span>
                            </div>

                            <div class="mt-8">
                                <a href="{{ route('admin.interviews.today') }}" class="inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg hover:shadow-white/20">
                                    {{ $todayInterviews > 0 ? 'View Schedule' : 'Open Interview Board' }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Financials Card --}}
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 relative overflow-hidden hover:bg-white/15 transition duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20">
                            <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                        </div>
                        @if($dueInvoicesCount > 0)
                            <span class="animate-pulse bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">Action Needed</span>
                        @else
                            <span class="bg-emerald-500/20 text-emerald-400 text-xs font-bold px-3 py-1 rounded-full">All Clear</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Invoices Due</p>
                        <p class="text-5xl font-extrabold text-white mt-2 mb-1">{{ $dueInvoicesCount }}</p>
                        <p class="text-slate-400 text-sm">Pending payments</p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-white/10">
                        <a href="{{ route('admin.billing.index') }}" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                            <span>Process Billing</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

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
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-12">
                {{-- 1. Post Job --}}
                @can('view_pending_jobs')
                <a href="{{ route('admin.jobs.create') }}" class="group bg-blue-600 rounded-2xl p-5 text-white shadow-lg hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
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
                <a href="{{ route('admin.clients.create') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                    <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h4 class="font-bold text-white">Add Client</h4>
                    <p class="text-slate-400 text-xs">Onboard company</p>
                </a>
                @endcan

                {{-- 3. Add Partner --}}
                @can('view_partner_data')
                <a href="{{ route('admin.partners.create') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                    <div class="h-10 w-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <h4 class="font-bold text-white">Add Partner</h4>
                    <p class="text-slate-400 text-xs">Register agency</p>
                </a>
                @endcan

                {{-- 4. Managers --}}
                @can('manage_sub_admins')
                <a href="{{ route('admin.sub_admins.index') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                    <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h4 class="font-bold text-white">Managers</h4>
                    <p class="text-slate-400 text-xs">Access Control</p>
                </a>
                @endcan

                {{-- 5. Reports --}}
                @can('view_billing_data')
                <a href="{{ route('admin.reports.jobs') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                    <div class="h-10 w-10 bg-teal-500/20 text-teal-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h4 class="font-bold text-white">Reports</h4>
                    <p class="text-slate-400 text-xs">View Analytics</p>
                </a>
                @endcan

                {{-- 6. Broadcast --}}
                <a href="{{ route('admin.broadcasts.index') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                    <div class="h-10 w-10 bg-orange-500/20 text-orange-400 rounded-lg flex items-center justify-center mb-3">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <h4 class="font-bold text-white">Broadcast</h4>
                    <p class="text-slate-400 text-xs">Message all vendors</p>
                </a>
            </div>

            {{-- SECTION 3: LIVE METRICS (5 Items) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-12">
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
                <a href="{{ route('admin.users.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
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

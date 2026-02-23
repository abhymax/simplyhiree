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
                <a href="{{ route('admin.applications.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
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

                {{-- Total Candidates --}}
                @can('view_candidate_data')
                <a href="{{ route('admin.users.index') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-400 text-xs font-bold uppercase">Candidates</span>
                        <i class="fa-solid fa-users text-blue-400"></i>
                    </div>
                    <div class="text-2xl font-extrabold text-white">{{ $totalUsers - ($totalClients + $totalPartners) }}</div>
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
                            <span class="font-bold text-white">{{ $totalUsers - ($totalClients + $totalPartners) }}</span>
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
            const totalCandidates = {{ $totalUsers }} - (totalClients + totalPartners);

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
</x-app-layout>

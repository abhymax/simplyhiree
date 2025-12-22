<x-app-layout title="Superadmin Dashboard">
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Superadmin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Overview</h1>
                    <p class="text-gray-500 mt-1">Welcome back, {{ Auth::user()->name }}. Your platform at a glance.</p>
                </div>
                <div class="text-right mt-4 md:mt-0">
                    <span class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-2xl text-sm font-medium shadow-sm flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-indigo-500"></i> {{ date('F j, Y') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                
                {{-- 1. POST JOB: Requires 'view_pending_jobs' or 'manage_clients' --}}
                @can('view_pending_jobs')
                <a href="{{ route('admin.jobs.create') }}" class="group relative bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-6 shadow-xl shadow-indigo-200 hover:shadow-2xl hover:shadow-indigo-300 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center text-white mb-4 backdrop-blur-md shadow-inner">
                                <i class="fa-solid fa-briefcase text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Post New Job</h3>
                            <p class="text-indigo-100 text-sm mt-1">Create vacancy for Client</p>
                        </div>
                        <div class="h-10 w-10 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-lg group-hover:translate-x-1 transition-transform">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                    </div>
                </a>
                @endcan

                {{-- 2. ADD CLIENT: Requires 'manage_clients' --}}
                @can('manage_clients')
                <a href="{{ route('admin.clients.create') }}" class="group relative bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:border-emerald-100 hover:shadow-emerald-100/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-12 w-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-user-tie text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Add New Client</h3>
                            <p class="text-gray-500 text-sm mt-1">Onboard a company</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-emerald-500 transition-colors"></i>
                    </div>
                </a>
                @endcan

                {{-- 3. ADD PARTNER: Requires 'view_partner_data' --}}
                @can('view_partner_data')
                <a href="{{ route('admin.partners.create') }}" class="group relative bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:border-purple-100 hover:shadow-purple-100/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-12 w-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-handshake text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Add New Partner</h3>
                            <p class="text-gray-500 text-sm mt-1">Register an agency</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-purple-500 transition-colors"></i>
                    </div>
                </a>
                @endcan

                {{-- 4. MANAGE SUB-ADMINS: Requires 'manage_sub_admins' --}}
                @can('manage_sub_admins')
                <a href="{{ route('admin.sub_admins.index') }}" class="group relative bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:border-blue-100 hover:shadow-blue-100/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-12 w-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-user-shield text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Managers</h3>
                            <p class="text-gray-500 text-sm mt-1">Manage sub-admins</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                    </div>
                </a>
                @endcan

                {{-- 5. REPORTS: Requires 'view_billing_data' (assuming billing covers reports) --}}
                @can('view_billing_data')
                <a href="{{ route('admin.reports.jobs') }}" class="group relative bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:border-teal-100 hover:shadow-teal-100/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-12 w-12 bg-teal-50 rounded-xl flex items-center justify-center text-teal-600 mb-4 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-list-check text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">All Jobs Report</h3>
                            <p class="text-gray-500 text-sm mt-1">Manage all job posts</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 group-hover:text-teal-500 transition-colors"></i>
                    </div>
                </a>
                @endcan
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-6 px-1">Live Metrics</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-10">
                
                @can('view_pending_jobs')
                <a href="{{ route('admin.jobs.pending') }}" class="block group">
                    <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl p-5 text-white shadow-lg shadow-orange-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-orange-50 text-xs font-bold uppercase tracking-wider">Pending Jobs</p>
                                <p class="text-3xl font-extrabold mt-1">{{ $pendingJobs }}</p>
                            </div>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="fa-solid fa-clock text-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endcan

                @can('view_application_data')
                <a href="{{ route('admin.applications.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-lg shadow-pink-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-rose-50 text-xs font-bold uppercase tracking-wider">Review Apps</p>
                                <p class="text-3xl font-extrabold mt-1">{{ $pendingApplications }}</p>
                            </div>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="fa-solid fa-file-circle-check text-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endcan

                @can('manage_clients')
                <a href="{{ route('admin.clients.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl p-5 text-white shadow-lg shadow-teal-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-teal-50 text-xs font-bold uppercase tracking-wider">Total Clients</p>
                                <p class="text-3xl font-extrabold mt-1">{{ $totalClients }}</p>
                            </div>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="fa-solid fa-user-tie text-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endcan

                @can('view_partner_data')
                <a href="{{ route('admin.partners.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-purple-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-purple-50 text-xs font-bold uppercase tracking-wider">Total Partners</p>
                                <p class="text-3xl font-extrabold mt-1">{{ $totalPartners }}</p>
                            </div>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="fa-solid fa-handshake text-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endcan

                @can('view_candidate_data')
                <a href="{{ route('admin.users.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl p-5 text-white shadow-lg shadow-blue-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-blue-50 text-xs font-bold uppercase tracking-wider">Total Candidates</p>
                                <p class="text-3xl font-extrabold mt-1">{{ $totalUsers - ($totalClients + $totalPartners) }}</p>
                            </div>
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                <i class="fa-solid fa-users text-xl"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endcan

            </div>
            
            {{-- CHARTS: Restricted to Managers with Reporting access or Superadmins --}}
            @if(auth()->user()->can('view_billing_data') || auth()->user()->hasRole('Superadmin'))
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Platform Growth</h3>
                        <div class="flex gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500 mt-1.5"></span>
                            <span class="text-sm text-gray-500">Activity Trend</span>
                        </div>
                    </div>
                    <div class="relative h-72 w-full">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">User Distribution</h3>
                    <div class="relative h-60 w-full flex justify-center">
                        <canvas id="userDistChart"></canvas>
                    </div>
                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span> Candidates</span>
                            <span class="font-bold text-gray-700">{{ $totalUsers - ($totalClients + $totalPartners) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Clients</span>
                            <span class="font-bold text-gray-700">{{ $totalClients }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span> Partners</span>
                            <span class="font-bold text-gray-700">{{ $totalPartners }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
    
    {{-- SCRIPTS REMAIN UNCHANGED --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data from controller
            const totalClients = {{ $totalClients }};
            const totalPartners = {{ $totalPartners }};
            // Calculate candidates: Total Users minus Clients and Partners
            const totalCandidates = {{ $totalUsers }} - (totalClients + totalPartners);

            // 1. Distribution Chart (Doughnut)
            const ctxDist = document.getElementById('userDistChart');
            if (ctxDist) {
                new Chart(ctxDist.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Candidates', 'Clients', 'Partners'],
                        datasets: [{
                            data: [totalCandidates, totalClients, totalPartners],
                            backgroundColor: ['#3b82f6', '#10b981', '#a855f7'],
                            borderWidth: 0,
                            hoverOffset: 4
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

            // 2. Growth Chart (Line)
            const ctxGrowth = document.getElementById('growthChart');
            if (ctxGrowth) {
                const ctx = ctxGrowth.getContext('2d');
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'New Activity',
                            data: [12, 19, 15, 25, 22, 30, 45], // Static data for demo
                            borderColor: '#3b82f6',
                            backgroundColor: gradient,
                            fill: true, 
                            tension: 0.4, 
                            pointRadius: 4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { display: false } }, 
                        scales: { 
                            y: { beginAtZero: true, grid: { borderDash: [2, 4], drawBorder: false } }, 
                            x: { grid: { display: false, drawBorder: false } } 
                        } 
                    }
                });
            }
        });
    </script>
</x-app-layout>
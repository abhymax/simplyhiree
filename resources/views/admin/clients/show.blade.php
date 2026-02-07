<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end">
                <div>
                    <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center text-emerald-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Client List
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg flex items-center gap-3">
                        {{ $user->name }}
                        <span class="text-lg bg-white/10 text-emerald-300 px-3 py-1 rounded-lg border border-white/10 font-mono align-middle">
                            {{ $user->client_code }}
                        </span>
                    </h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Client Profile & Activity Overview</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex gap-3">
                    <a href="{{ route('admin.clients.edit', $user->id) }}" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-3 rounded-xl font-bold transition shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-pen"></i> Edit Profile
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: STATS & JOBS --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- STATS GRID --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-emerald-500/20 border border-emerald-500/30 p-5 rounded-2xl shadow-lg backdrop-blur-md">
                            <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider">Total Jobs</p>
                            <p class="text-3xl font-black text-white">{{ $totalJobs }}</p>
                        </div>
                        <div class="bg-blue-500/20 border border-blue-500/30 p-5 rounded-2xl shadow-lg backdrop-blur-md">
                            <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Active Posts</p>
                            <p class="text-3xl font-black text-white">{{ $activeJobs }}</p>
                        </div>
                        <div class="bg-purple-500/20 border border-purple-500/30 p-5 rounded-2xl shadow-lg backdrop-blur-md">
                            <p class="text-purple-200 text-xs font-bold uppercase tracking-wider">Total Hires</p>
                            <p class="text-3xl font-black text-white">{{ $totalHires }}</p>
                        </div>
                    </div>

                    {{-- JOB HISTORY --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden">
                        <div class="p-6 border-b border-white/10 flex justify-between items-center bg-white/5">
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-briefcase text-emerald-400"></i> Recent Job Postings
                            </h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-900/50 text-emerald-300 uppercase font-bold text-xs">
                                    <tr>
                                        <th class="px-6 py-4">Job Title</th>
                                        <th class="px-6 py-4">Posted Date</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">View</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5 text-white">
                                    @forelse($jobs as $job)
                                        <tr class="hover:bg-white/5 transition">
                                            <td class="px-6 py-4 font-bold">{{ $job->title }}</td>
                                            <td class="px-6 py-4 text-slate-400">{{ $job->created_at->format('M d, Y') }}</td>
                                            <td class="px-6 py-4">
                                                @if($job->status === 'approved')
                                                    <span class="text-emerald-400 text-xs font-bold uppercase flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-xs font-bold uppercase">{{ $job->status }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('admin.jobs.show', $job->id) }}" class="text-emerald-400 hover:text-white transition">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">No jobs posted yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Pagination --}}
                        @if($jobs->hasPages())
                            <div class="p-4 border-t border-white/10">
                                {{ $jobs->links() }}
                            </div>
                        @endif
                    </div>

                </div>

                {{-- RIGHT COLUMN: PROFILE CARD --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- Profile Info --}}
                    <div class="bg-gradient-to-b from-slate-800 to-slate-900 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl">
                        <div class="flex flex-col items-center text-center mb-6">
                            <div class="h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-black text-3xl shadow-2xl ring-4 ring-slate-800 mb-4">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                            <p class="text-emerald-300 text-sm font-mono mt-1">{{ $user->client_code }}</p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="bg-blue-500/20 h-10 w-10 rounded-lg flex items-center justify-center text-blue-400">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">Email Address</p>
                                    <p class="text-white text-sm font-medium truncate w-48">{{ $user->email }}</p>
                                </div>
                            </div>

                            @if($user->phone)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="bg-purple-500/20 h-10 w-10 rounded-lg flex items-center justify-center text-purple-400">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">Phone Number</p>
                                    <p class="text-white text-sm font-medium">{{ $user->phone }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="bg-amber-500/20 h-10 w-10 rounded-lg flex items-center justify-center text-amber-400">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">Billable Cycle</p>
                                    <p class="text-white text-sm font-medium">{{ $user->billable_period_days }} Days</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/5">
                                <div class="bg-slate-500/20 h-10 w-10 rounded-lg flex items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-calendar"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">Joined On</p>
                                    <p class="text-white text-sm font-medium">{{ $user->created_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-white/10">
                            <span class="block text-xs font-bold text-slate-500 uppercase mb-3 text-center">Account Status</span>
                            @if($user->status === 'active')
                                <div class="w-full py-2 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 text-center rounded-lg font-bold text-sm">
                                    <i class="fa-solid fa-check-circle mr-2"></i> Active Account
                                </div>
                            @else
                                <div class="w-full py-2 bg-rose-500/20 border border-rose-500/50 text-rose-300 text-center rounded-lg font-bold text-sm">
                                    <i class="fa-solid fa-ban mr-2"></i> {{ ucfirst($user->status) }}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
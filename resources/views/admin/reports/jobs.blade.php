<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Chart/Graph Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Master Job Report</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Performance overview of all posted vacancies.</p>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <div class="bg-blue-600/20 backdrop-blur-md border border-blue-500/30 text-white px-6 py-3 rounded-2xl shadow-lg flex items-center gap-3">
                        <p class="text-blue-300 text-xs font-bold uppercase tracking-wider">Total Jobs</p>
                        <p class="text-3xl font-black text-white">{{ $jobs->total() }}</p>
                    </div>
                </div>
            </div>

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- FILTERS --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.reports.jobs') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        
                        {{-- Search (Col-4) --}}
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-white"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Job Title or Company..." 
                                    class="w-full pl-10 bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                            </div>
                        </div>

                        {{-- Status (Col-3) --}}
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Status</label>
                            <select name="status" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                                <option value="" class="text-gray-400">All Statuses</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Live / Approved</option>
                                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending</option>
                                <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        {{-- Client (Col-3) --}}
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Client</label>
                            <select name="client_id" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                                <option value="" class="text-gray-400">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" class="bg-slate-900" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Actions (Col-2) --}}
                        <div class="md:col-span-2 flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 px-4 rounded-xl font-bold shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-0.5 h-[44px] flex items-center justify-center gap-2">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                            <a href="{{ route('admin.reports.jobs') }}" class="bg-rose-500 hover:bg-rose-400 text-white p-2 rounded-xl transition h-[44px] w-[44px] flex items-center justify-center shadow-lg" title="Reset">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Job Details</th>
                                <th class="px-6 py-5 text-center">Performance Stats</th>
                                <th class="px-6 py-5 text-center">Current Status</th>
                                <th class="px-6 py-5 text-right">Management</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($jobs as $job)
                                <tr class="hover:bg-white/5 transition duration-200 group">
                                    
                                    {{-- Job Details --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col">
                                            <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-white text-lg hover:text-cyan-400 transition" target="_blank">
                                                {{ $job->title }} <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1 opacity-50"></i>
                                            </a>
                                            <div class="text-amber-300 font-bold text-sm mt-1 flex items-center gap-1.5" style="color: #fcd34d !important;">
                                                <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                                            </div>
                                            <span class="text-xs text-blue-300 mt-1 opacity-70">Posted {{ $job->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </td>
                                    
                                    {{-- Stats (Applicants/Joined) --}}
                                    <td class="px-6 py-5 text-center align-middle">
                                        <div class="flex flex-col items-center gap-2">
                                            @if($job->jobApplications->count() > 0)
                                                <a href="{{ route('admin.reports.jobs.applicants', $job->id) }}" 
                                                   class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-blue-600/20 text-blue-200 border border-blue-500/50 hover:bg-blue-600 hover:text-white transition group/btn shadow-md"
                                                   title="View all applicants">
                                                    {{ $job->jobApplications->count() }} Applicants
                                                    <i class="fa-solid fa-chevron-right ml-2 text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-white/5 text-slate-400 border border-white/5">
                                                    0 Applicants
                                                </span>
                                            @endif

                                            @php
                                                $joinedCount = $job->jobApplications->where('joined_status', 'Joined')->count();
                                            @endphp
                                            @if($joinedCount > 0)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm">
                                                    <i class="fa-solid fa-trophy"></i> {{ $joinedCount }} Hired
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5 text-center align-middle">
                                        @if($job->status === 'approved')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold shadow-lg shadow-emerald-500/10">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Live
                                            </span>
                                        @elseif($job->status === 'pending_approval')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/50 text-xs font-bold shadow-lg">
                                                <i class="fa-solid fa-clock"></i> Pending
                                            </span>
                                        @elseif($job->status === 'on_hold')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/50 text-xs font-bold shadow-lg">
                                                <i class="fa-solid fa-pause"></i> On Hold
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-700 text-slate-300 border border-slate-500 text-xs font-bold">
                                                {{ ucfirst($job->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right align-middle">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('jobs.show', $job->id) }}" target="_blank" class="h-9 w-9 rounded-lg bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow-md" title="View Public Page">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            @if($job->status === 'approved')
                                                <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="on_hold">
                                                    <button type="submit" class="h-9 w-9 rounded-lg bg-orange-600/20 text-orange-400 border border-orange-500/30 flex items-center justify-center hover:bg-orange-600 hover:text-white transition shadow-md" title="Put on Hold">
                                                        <i class="fa-solid fa-pause"></i>
                                                    </button>
                                                </form>
                                            @elseif($job->status === 'on_hold' || $job->status === 'closed')
                                                <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="h-9 w-9 rounded-lg bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition shadow-md" title="Re-Activate">
                                                        <i class="fa-solid fa-play"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.jobs.destroy', $job->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this job permanently?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="h-9 w-9 rounded-lg bg-rose-600/20 text-rose-400 border border-rose-500/30 flex items-center justify-center hover:bg-rose-600 hover:text-white transition shadow-md" title="Delete Job">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-solid fa-briefcase text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No jobs found.</p>
                                        <p class="text-blue-200 mt-2">Adjust your filters to see records.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="p-6 border-t border-white/10 bg-slate-900/80 backdrop-blur-md">
                    <style>
                        nav[role="navigation"] p { color: #e2e8f0 !important; font-weight: 600; }
                        nav[role="navigation"] span.relative, nav[role="navigation"] a.relative {
                            background-color: rgba(255, 255, 255, 0.1) !important;
                            border-color: rgba(255, 255, 255, 0.2) !important;
                            color: white !important;
                            font-weight: 700;
                        }
                        nav[role="navigation"] span[aria-current="page"] span {
                            background-color: #0ea5e9 !important;
                            border-color: #0ea5e9 !important;
                            color: white !important;
                        }
                    </style>
                    
                    {{ $jobs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
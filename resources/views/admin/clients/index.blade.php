<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Client Management</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage companies and external partners.</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex items-center gap-4">
                    {{-- Only show total count if $clients is a Paginator object --}}
                    @if(method_exists($clients, 'total'))
                    <div class="bg-emerald-500/20 border border-emerald-500/30 text-white px-5 py-2.5 rounded-xl shadow-lg flex items-center gap-3">
                        <span class="text-emerald-300 text-xs font-bold uppercase tracking-wider">Total Clients</span>
                        <span class="text-2xl font-black">{{ $clients->total() }}</span>
                    </div>
                    @endif
                    
                    <a href="{{ route('admin.clients.create') }}" class="inline-flex items-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-1">
                        <i class="fa-solid fa-plus mr-2"></i> Onboard Client
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md animate-bounce-short">
                    <i class="fa-solid fa-circle-check mr-3 text-2xl"></i> 
                    {{ session('success') }}
                </div>
            @endif

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- SEARCH & FILTERS --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name or email..." 
                                class="w-full pl-10 bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 font-medium h-12">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition">
                                Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.clients.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-xl transition" title="Clear">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-emerald-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Company / Client</th>
                                <th class="px-6 py-5">Contact Info</th>
                                <th class="px-6 py-5">Account Status</th>
                                <th class="px-6 py-5">Jobs Posted</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($clients as $client)
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    
                                    {{-- Company Name --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-1 ring-white/20">
                                                {{ substr($client->name ?? 'C', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-base">{{ $client->name ?? 'Unknown Client' }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5">
                                                    Joined 
                                                    {{ optional($client->created_at)->format('M Y') ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact Info --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-medium text-blue-100 flex items-center gap-2">
                                                <i class="fa-regular fa-envelope text-slate-400 text-xs"></i> {{ $client->email ?? 'No Email' }}
                                            </span>
                                            {{-- If phone exists --}}
                                            @if(!empty($client->phone))
                                            <span class="text-xs text-slate-400 flex items-center gap-2">
                                                <i class="fa-solid fa-phone text-slate-500 text-[10px]"></i> {{ $client->phone }}
                                            </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @if(($client->status ?? 'active') === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold shadow-lg shadow-emerald-500/10">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/50 text-xs font-bold">
                                                <i class="fa-solid fa-ban"></i> Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Jobs Count (Safe Check) --}}
                                    <td class="px-6 py-5">
                                        {{-- Safely check for jobs count or fall back to relationship count --}}
                                        @php
                                            $jobsCount = $client->jobs_count ?? ($client->jobs ? $client->jobs->count() : 0);
                                        @endphp
                                        <a href="{{ route('admin.reports.jobs', ['client_id' => $client->id]) }}" class="group/link flex items-center gap-2 w-fit">
                                            <span class="font-bold text-white text-lg">{{ $jobsCount }}</span>
                                            <span class="text-xs text-slate-400 group-hover/link:text-emerald-300 transition">Openings <i class="fa-solid fa-arrow-right ml-1 opacity-0 group-hover/link:opacity-100 transition-opacity"></i></span>
                                        </a>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.clients.edit', $client->id) }}" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10 shadow-md" title="Edit Client">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            
                                            <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Delete {{ $client->name }}? This will remove all their jobs.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-rose-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10 shadow-md" title="Delete Client">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-solid fa-briefcase text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No clients found.</p>
                                        <p class="text-blue-200 mt-2">Get started by onboarding your first company.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION (SAFE CHECK) --}}
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
                            background-color: #059669 !important; /* Emerald-600 */
                            border-color: #059669 !important;
                            color: white !important;
                        }
                    </style>
                    
                    {{-- Only show pagination if $clients is a Paginator instance --}}
                    @if(method_exists($clients, 'links'))
                        {{ $clients->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
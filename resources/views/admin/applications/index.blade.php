<x-app-layout>
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
                
                {{-- FILTERS --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.applications.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        
                        {{-- Search --}}
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-white"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email..." 
                                    class="w-full pl-10 bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium">
                            </div>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Status</label>
                            <select name="status" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium">
                                <option value="" class="text-gray-400">All Statuses</option>
                                @foreach(['Pending Review', 'Approved', 'Rejected', 'Interview Scheduled', 'Selected', 'Joined'] as $status)
                                    <option value="{{ $status }}" class="bg-slate-900" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Job Role --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Job Role</label>
                            <select name="job_id" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium">
                                <option value="" class="text-gray-400">All Jobs</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}" class="bg-slate-900" {{ request('job_id') == $job->id ? 'selected' : '' }}>{{ Str::limit($job->title, 20) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Partner --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Partner</label>
                            <select name="partner_id" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium">
                                <option value="" class="text-gray-400">All Partners</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" class="bg-slate-900" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>{{ Str::limit($partner->name, 20) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Actions --}}
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-cyan-600 hover:bg-cyan-500 text-white py-2 px-4 rounded-xl font-bold shadow-lg shadow-cyan-500/20 transition transform hover:-translate-y-0.5 text-sm h-[42px] flex items-center justify-center">
                                <i class="fa-solid fa-filter mr-2"></i> Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status', 'job_id', 'partner_id']))
                                <a href="{{ route('admin.applications.index') }}" class="bg-rose-500 hover:bg-rose-400 text-white p-2 rounded-xl transition h-[42px] w-[42px] flex items-center justify-center shadow-lg" title="Reset Filters">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
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
                                    {{-- Candidate --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-white/20">
                                                {{ $initial !== '' ? $initial : 'U' }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-lg leading-tight">{{ $candidateName }}</div>
                                                <div class="text-cyan-200 text-sm font-medium mt-0.5"><i class="fa-regular fa-envelope mr-1"></i> {{ $candidateEmail }}</div>
                                                <div class="text-blue-300 text-xs mt-1 opacity-80">{{ $application->created_at->format('M d, Y') }}</div>
                                                <div class="mt-1 text-[11px] text-slate-300 font-semibold tracking-wide">
                                                    {{ $applicationCode }} | {{ $candidateCode }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Job Details (Fixed High Visibility) --}}
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-white text-lg">{{ $application->job->title ?? 'Deleted Job' }}</div>
                                        <div class="text-[11px] text-slate-300 font-semibold tracking-wide mt-0.5">{{ $jobCode }}</div>
                                        
                                        {{-- COMPANY NAME: BRIGHT AMBER --}}
                                        <div class="text-amber-300 font-bold text-sm mt-1 flex items-center gap-1.5" style="color: #fcd34d !important;">
                                            <i class="fa-solid fa-building text-amber-400"></i> 
                                            {{ $application->job->company_name ?? 'Internal' }}
                                        </div>
                                    </td>

                                    {{-- Source --}}
                                    <td class="px-6 py-5">
                                        @if($sourcePartner)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-bold shadow-md">
                                                <i class="fa-solid fa-handshake"></i> {{ Str::limit($sourcePartner->name, 12) }}
                                            </span>
                                            <div class="text-[11px] text-slate-300 font-semibold tracking-wide mt-1">
                                                {{ $sourcePartner->entity_code ?? ('SH-PRT-' . str_pad((string) $sourcePartner->id, 6, '0', STR_PAD_LEFT)) }}
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700 text-white text-xs font-bold border border-slate-500">
                                                <i class="fa-solid fa-globe"></i> Direct
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @php $status = strtolower($application->status); @endphp

                                        @if($status === 'pending review')
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-500 text-black border-2 border-amber-300 text-xs font-extrabold shadow-lg shadow-amber-500/20 animate-pulse">
                                                <i class="fa-regular fa-clock"></i> Pending Review
                                            </span>
                                        @elseif($status === 'approved')
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500 text-white border-2 border-emerald-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-check"></i> Approved
                                            </span>
                                        @elseif($status === 'rejected')
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-600 text-white border-2 border-red-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-xmark"></i> Rejected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 text-white border-2 border-blue-400 text-xs font-extrabold shadow-lg">
                                                <i class="fa-solid fa-circle-info"></i> {{ ucfirst($status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions (Bright Icons) --}}
                                    <td class="px-6 py-5 text-right">
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md transition border border-indigo-400">
                                            @if(strtolower($application->status) === 'pending review')
                                                Review &amp; Decide
                                            @else
                                                View Details
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center">
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
</x-app-layout>

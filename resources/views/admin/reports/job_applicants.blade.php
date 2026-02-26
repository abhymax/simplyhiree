<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.reports.jobs') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Master Report
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Applicant Report</h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-blue-200 text-lg">Candidates for</span>
                        <span class="text-amber-300 font-bold text-lg border-b border-amber-300/30 pb-0.5">{{ $job->title }}</span>
                        <span class="text-blue-200 text-lg">at</span>
                        <span class="text-white font-bold text-lg">{{ $job->company_name }}</span>
                    </div>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <div class="flex flex-col items-stretch md:items-end gap-3">
                        <div class="bg-blue-600/20 backdrop-blur-md border border-blue-500/30 text-white px-5 py-2.5 rounded-xl shadow-lg flex items-center gap-3">
                            <p class="text-blue-300 text-xs font-bold uppercase tracking-wider">Total Applicants</p>
                            <p class="text-2xl font-black text-white">{{ $applications->total() }}</p>
                        </div>
                        <a href="{{ route('admin.reports.jobs.applicants.export', ['job' => $job->id]) }}"
                           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500/20 text-emerald-200 border border-emerald-400/40 hover:bg-emerald-500 hover:text-white transition font-bold shadow-lg shadow-emerald-900/30">
                            <i class="fa-solid fa-file-arrow-down"></i> Download Excel (CSV)
                        </a>
                    </div>
                </div>
            </div>

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Candidate Name</th>
                                <th class="px-6 py-5">Source (Partner)</th>
                                <th class="px-6 py-5">Applied Date</th>
                                <th class="px-6 py-5">Current Pipeline Status</th>
                                <th class="px-6 py-5 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($applications as $application)
                                @php
                                    $agencyCandidate = $application->candidate;
                                    $directCandidate = $application->candidateUser;
                                    $directProfile = $directCandidate?->profile;
                                    $candidateName = trim(($agencyCandidate?->first_name ?? '') . ' ' . ($agencyCandidate?->last_name ?? ''));
                                    if ($candidateName === '') {
                                        $candidateName = $directCandidate?->name ?? 'Unknown Candidate';
                                    }
                                    $candidateEmail = $agencyCandidate?->email ?? $directCandidate?->email ?? 'N/A';
                                    $candidatePhone = $agencyCandidate?->phone_number ?? $directProfile?->phone_number ?? 'N/A';
                                    $sourcePartner = $agencyCandidate?->partner;
                                    $initial = strtoupper(substr($candidateName, 0, 1));
                                @endphp
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    
                                    {{-- Candidate --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md ring-1 ring-white/20">
                                                {{ $initial !== '' ? $initial : 'C' }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-base">{{ $candidateName }}</div>
                                                <div class="text-xs text-cyan-200 mt-0.5 flex items-center gap-1"><i class="fa-regular fa-envelope"></i> {{ $candidateEmail }}</div>
                                                <div class="text-xs text-blue-300 mt-0.5 flex items-center gap-1"><i class="fa-solid fa-phone"></i> {{ $candidatePhone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    {{-- Source --}}
                                    <td class="px-6 py-5">
                                        @if($sourcePartner)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-500/20 text-purple-200 border border-purple-500/30 text-xs font-bold shadow-sm">
                                                <i class="fa-solid fa-handshake"></i> {{ $sourcePartner->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/5 text-slate-400 border border-white/10 text-xs font-bold">
                                                <i class="fa-solid fa-globe"></i> Direct
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-5">
                                        <span class="text-blue-100 font-medium">{{ $application->created_at->format('M d, Y') }}</span>
                                    </td>

                                    {{-- Status Pipeline --}}
                                    <td class="px-6 py-5">
                                        @php
                                            $adminStatus = strtolower($application->status);
                                            $clientStatus = $application->hiring_status;
                                        @endphp

                                        @if($adminStatus === 'pending review')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/50 text-xs font-bold animate-pulse">
                                                <i class="fa-regular fa-clock"></i> Pending Admin Review
                                            </span>
                                        @elseif($adminStatus === 'rejected')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-500/20 text-red-300 border border-red-500/50 text-xs font-bold">
                                                <i class="fa-solid fa-ban"></i> Rejected by Admin
                                            </span>
                                        @elseif($adminStatus === 'approved')
                                            @if($clientStatus == 'Interview Scheduled')
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/50 text-xs font-bold">
                                                    <i class="fa-solid fa-video"></i> Interview Scheduled
                                                </span>
                                            @elseif($clientStatus == 'Selected')
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/50 text-xs font-bold">
                                                    <i class="fa-solid fa-user-check"></i> Selected
                                                </span>
                                            @elseif($clientStatus == 'Joined')
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold shadow-lg shadow-emerald-500/20">
                                                    <i class="fa-solid fa-trophy"></i> Joined
                                                </span>
                                            @elseif($clientStatus == 'Client Rejected')
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/50 text-xs font-bold">
                                                    <i class="fa-solid fa-user-xmark"></i> Client Rejected
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-500/20 text-green-300 border border-green-500/50 text-xs font-bold">
                                                    <i class="fa-solid fa-building-user"></i> With Client (Reviewing)
                                                </span>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right">
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition border border-blue-500 hover:shadow-blue-500/30">
                                            View <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-solid fa-users-slash text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No applicants yet.</p>
                                        <p class="text-blue-200 mt-2">This job hasn't received any applications.</p>
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
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

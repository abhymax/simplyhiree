@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'overview' }" class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <a href="{{ route('partner.jobs') }}" class="inline-flex items-center text-blue-200 hover:text-white text-sm mb-6 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Jobs
        </a>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 md:p-8 shadow-2xl mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start gap-6">
                <div class="flex-1 w-full">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="bg-blue-500/20 border border-blue-400/30 text-blue-100 text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wide">
                            {{ $job->job_type }}
                        </span>
                        <span class="bg-indigo-500/20 border border-indigo-400/30 text-indigo-100 text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wide">
                            {{ $job->category->name ?? 'General' }}
                        </span>
                        @if($job->is_walkin)
                            <span class="bg-cyan-500/20 border border-cyan-400/30 text-cyan-100 text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wide">
                                Walk-in
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-2">
                        {{ $job->title }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-slate-100 text-sm md:text-base mt-3">
                        <span class="flex items-center font-medium">
                            <i class="fa-regular fa-building mr-2 text-blue-300"></i> {{ $job->company_name }}
                        </span>
                        <span class="flex items-center font-medium">
                            <i class="fa-solid fa-location-dot mr-2 text-blue-300"></i> {{ $job->location }}
                        </span>
                        @if($job->company_website)
                            <a href="{{ $job->company_website }}" target="_blank" class="flex items-center text-blue-200 hover:text-white underline transition">
                                <i class="fa-solid fa-link mr-2"></i> Company Website
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col gap-4 w-full lg:w-auto items-start lg:items-end">
                    <div class="bg-slate-900/50 rounded-2xl p-4 border border-white/10 text-center w-full sm:w-auto min-w-[220px]">
                        <p class="text-xs text-blue-200 uppercase font-bold tracking-wider mb-1">Partner Payout</p>
                        <p class="text-3xl font-bold text-emerald-300">₹{{ number_format($job->payout_amount) }}</p>
                        <p class="text-xs text-slate-300 mt-1">Credit: {{ $job->minimum_stay_days }} days</p>
                    </div>

                    <a href="{{ route('partner.candidates.check') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 text-sm font-bold rounded-xl text-white bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 shadow-lg transition">
                        <i class="fa-solid fa-user-plus mr-2"></i> Add New Candidate
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl mb-6 overflow-x-auto">
            <nav class="flex gap-8 px-5" aria-label="Tabs">
                <button @click="activeTab = 'overview'"
                    :class="{ 'border-blue-400 text-blue-100': activeTab === 'overview', 'border-transparent text-slate-300 hover:text-white': activeTab !== 'overview' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition">
                    <i class="fa-solid fa-file-lines mr-2"></i> Job Overview
                </button>

                <button @click="activeTab = 'matching'"
                    :class="{ 'border-indigo-400 text-indigo-100': activeTab === 'matching', 'border-transparent text-slate-300 hover:text-white': activeTab !== 'matching' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition flex items-center">
                    <i class="fa-solid fa-users-viewfinder mr-2"></i> Matching Candidates
                    <span class="ml-2 bg-indigo-500/20 text-indigo-100 py-0.5 px-2.5 rounded-full text-xs border border-indigo-400/30">{{ $matchingCandidates->count() }}</span>
                </button>

                <button @click="activeTab = 'applied'"
                    :class="{ 'border-emerald-400 text-emerald-100': activeTab === 'applied', 'border-transparent text-slate-300 hover:text-white': activeTab !== 'applied' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> Applied History
                    <span class="ml-2 bg-emerald-500/20 text-emerald-100 py-0.5 px-2.5 rounded-full text-xs border border-emerald-400/30">{{ $appliedApplications->count() }}</span>
                </button>
            </nav>
        </div>

        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                    <p class="text-xs text-blue-200 font-bold uppercase mb-1">Salary</p>
                    <p class="text-sm font-semibold text-white">{{ $job->salary ?? 'Not Disclosed' }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                    <p class="text-xs text-blue-200 font-bold uppercase mb-1">Openings</p>
                    <p class="text-sm font-semibold text-white">{{ $job->openings ?? 'Multiple' }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                    <p class="text-xs text-blue-200 font-bold uppercase mb-1">Experience</p>
                    <p class="text-sm font-semibold text-white">{{ $job->experienceLevel->name ?? 'Any' }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                    <p class="text-xs text-blue-200 font-bold uppercase mb-1">Education</p>
                    <p class="text-sm font-semibold text-white">{{ $job->educationLevel->name ?? 'Not Specified' }}</p>
                </div>
                <div class="bg-white/10 border border-white/10 p-4 rounded-2xl">
                    <p class="text-xs text-blue-200 font-bold uppercase mb-1">Deadline</p>
                    <p class="text-sm font-bold text-rose-200">{{ $job->application_deadline ? \Carbon\Carbon::parse($job->application_deadline)->format('d M, Y') : 'Urgent' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white/10 border border-white/10 p-6 rounded-2xl">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Job Description</h3>
                    <div class="text-slate-100 text-sm leading-relaxed whitespace-pre-line">
                        {{ $job->description }}
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white/10 border border-white/10 p-6 rounded-2xl">
                        <h3 class="text-md font-bold text-white mb-4">Required Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            @if($job->skills_required)
                                @foreach(explode(',', $job->skills_required) as $skill)
                                    <span class="bg-blue-500/20 text-blue-100 text-xs font-semibold px-3 py-1.5 rounded-md border border-blue-400/30">
                                        {{ trim($skill) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-slate-300 text-sm">No specific skills listed.</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white/10 border border-white/10 p-6 rounded-2xl">
                        <h3 class="text-md font-bold text-white mb-4">Additional Criteria</h3>
                        <ul class="space-y-3 text-sm text-slate-100">
                            <li class="flex justify-between gap-3">
                                <span class="text-slate-300">Age:</span>
                                <span class="font-medium">{{ $job->min_age ?? 18 }} - {{ $job->max_age ?? 60 }} Years</span>
                            </li>
                            <li class="flex justify-between gap-3">
                                <span class="text-slate-300">Gender:</span>
                                <span class="font-medium">{{ $job->gender_preference ?? 'Any' }}</span>
                            </li>
                            @if($job->is_walkin && $job->interview_slot)
                            <li class="pt-3 border-t border-white/10">
                                <span class="block text-cyan-200 font-bold text-xs uppercase mb-1">Walk-in Interview</span>
                                <span class="font-medium"><i class="fa-regular fa-clock mr-1"></i> {{ $job->interview_slot->format('d M, Y @ h:i A') }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'matching'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white/10 border border-white/10 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 bg-indigo-500/10 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-white">Recommended Candidates</h3>
                        <p class="text-sm text-indigo-100">Candidates from your pool matching job skills.</p>
                    </div>
                    <a href="{{ route('partner.candidates.check') }}" class="text-xs bg-white/10 text-indigo-100 font-bold px-3 py-1.5 rounded border border-white/20 hover:bg-white/20">
                        + Add Manual
                    </a>
                </div>

                @if($matchingCandidates->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
                        @foreach($matchingCandidates as $candidate)
                            <div class="bg-slate-900/40 border border-white/10 rounded-xl p-5 hover:border-indigo-300/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-white">{{ $candidate->first_name }} {{ $candidate->last_name }}</h4>
                                        <p class="text-xs text-slate-300">{{ $candidate->job_role_preference ?? 'No Role' }}</p>
                                    </div>
                                    <span class="bg-emerald-500/20 text-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-400/30">MATCH</span>
                                </div>

                                <div class="text-xs text-slate-200 space-y-1 mb-4">
                                    <p><i class="fa-solid fa-location-dot w-4 text-slate-300"></i> {{ $candidate->location }}</p>
                                    <p><i class="fa-solid fa-briefcase w-4 text-slate-300"></i> {{ $candidate->experience_status }}</p>
                                    <p><i class="fa-solid fa-graduation-cap w-4 text-slate-300"></i> {{ $candidate->education_level }}</p>
                                </div>

                                <div class="pt-3 border-t border-white/10">
                                    <form action="{{ route('partner.jobs.submit', $job->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="candidate_ids[]" value="{{ $candidate->id }}">
                                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white text-sm font-bold py-2.5 rounded-lg transition">
                                            Apply Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="bg-white/10 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 border border-white/10">
                            <i class="fa-solid fa-users-slash text-slate-300 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-white">No Auto-Matches Found</h3>
                        <p class="text-slate-300 text-sm mt-1">Try adding a new candidate specifically for this job.</p>
                        <a href="{{ route('partner.candidates.check') }}" class="mt-4 inline-block text-indigo-200 font-bold hover:text-white">Add Candidate</a>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="activeTab === 'applied'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white/10 border border-white/10 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-white/10 bg-emerald-500/10">
                    <h3 class="text-lg font-bold text-white">Application History</h3>
                    <p class="text-sm text-emerald-100">Candidates you already submitted for this role.</p>
                </div>

                @if($appliedApplications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-slate-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-200 uppercase tracking-wider">Candidate</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-200 uppercase tracking-wider">Applied Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-200 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-200 uppercase tracking-wider">Client Feedback</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @foreach($appliedApplications as $app)
                                    <tr class="hover:bg-white/5">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-white">{{ $app->candidate->first_name }} {{ $app->candidate->last_name }}</div>
                                            <div class="text-xs text-slate-300">{{ $app->candidate->email ?? $app->candidate->phone_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-200">
                                            {{ $app->created_at->format('d M, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $app->status === 'Approved' ? 'bg-emerald-500/20 text-emerald-100 border border-emerald-400/30' : '' }}
                                                {{ $app->status === 'Rejected' ? 'bg-rose-500/20 text-rose-100 border border-rose-400/30' : '' }}
                                                {{ $app->status === 'Pending Review' ? 'bg-amber-500/20 text-amber-100 border border-amber-400/30' : '' }}">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-200">
                                            {{ $app->hiring_status ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-slate-300 italic">
                        No candidates have applied to this job yet.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
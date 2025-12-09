@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'overview' }" class="min-h-screen bg-gray-50 pb-12">
    
    <div class="bg-blue-900 shadow-lg border-b border-blue-800 text-white" style="background-color: #1e3a8a;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <a href="{{ route('partner.jobs') }}" class="inline-flex items-center text-gray-300 hover:text-white text-sm mb-6 transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Jobs
            </a>

            <div class="flex flex-col lg:flex-row justify-between items-start gap-6">
                <div class="flex-1 w-full">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded border border-blue-500 uppercase tracking-wide">
                            {{ $job->job_type }}
                        </span>
                        <span class="bg-indigo-600 text-white text-xs font-bold px-2.5 py-1 rounded border border-indigo-500 uppercase tracking-wide">
                            {{ $job->jobCategory->name ?? 'General' }}
                        </span>
                        @if($job->is_walkin)
                            <span class="bg-pink-600 text-white text-xs font-bold px-2.5 py-1 rounded border border-pink-500 uppercase tracking-wide animate-pulse">
                                Walk-in
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-2">
                        {{ $job->title }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-white text-sm md:text-base mt-3">
                        <span class="flex items-center font-medium">
                            <i class="fa-regular fa-building mr-2 text-blue-300"></i> {{ $job->company_name }}
                        </span>
                        <span class="flex items-center font-medium">
                            <i class="fa-solid fa-location-dot mr-2 text-blue-300"></i> {{ $job->location }}
                        </span>
                        @if($job->company_website)
                            <a href="{{ $job->company_website }}" target="_blank" class="flex items-center text-blue-300 hover:text-white underline transition">
                                <i class="fa-solid fa-link mr-2"></i> Company Website
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col gap-4 w-full lg:w-auto items-start lg:items-end">
                    
                    <div class="bg-blue-800/50 rounded-lg p-4 border border-blue-500 text-center w-full sm:w-auto min-w-[200px]">
                        <p class="text-xs text-blue-200 uppercase font-bold tracking-wider mb-1">Partner Payout</p>
                        <p class="text-3xl font-bold text-green-400">₹{{ number_format($job->payout_amount) }}</p>
                        <p class="text-xs text-blue-200 mt-1">Credit: {{ $job->minimum_stay_days }} days</p>
                    </div>

                    <a href="{{ route('partner.candidates.check') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent text-sm font-bold rounded-lg text-blue-900 bg-white hover:bg-blue-50 shadow-lg transform hover:-translate-y-0.5 transition duration-150">
                        <i class="fa-solid fa-user-plus mr-2 text-blue-600"></i> Add New Candidate
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'overview'" 
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition">
                    <i class="fa-solid fa-file-lines mr-2"></i> Job Overview
                </button>

                <button @click="activeTab = 'matching'" 
                    :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'matching', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'matching' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition flex items-center">
                    <i class="fa-solid fa-users-viewfinder mr-2"></i> Matching Candidates
                    <span class="ml-2 bg-indigo-100 text-indigo-600 py-0.5 px-2.5 rounded-full text-xs">{{ $matchingCandidates->count() }}</span>
                </button>

                <button @click="activeTab = 'applied'" 
                    :class="{ 'border-green-500 text-green-600': activeTab === 'applied', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'applied' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> Applied History
                    <span class="ml-2 bg-green-100 text-green-600 py-0.5 px-2.5 rounded-full text-xs">{{ $appliedApplications->count() }}</span>
                </button>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Salary</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $job->salary ?? 'Not Disclosed' }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Openings</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $job->openings ?? 'Multiple' }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Experience</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $job->experienceLevel->name ?? 'Any' }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Education</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $job->educationLevel->name ?? 'Not Specified' }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Deadline</p>
                    <p class="text-sm font-bold text-red-600">{{ $job->application_deadline ? \Carbon\Carbon::parse($job->application_deadline)->format('d M, Y') : 'Urgent' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Job Description</h3>
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                        {{ $job->description }}
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-md font-bold text-gray-900 mb-4">Required Skills</h3>
                        <div class="flex flex-wrap gap-2">
                            @if($job->skills_required)
                                @foreach(explode(',', $job->skills_required) as $skill)
                                    <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-md border border-blue-100">
                                        {{ trim($skill) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-gray-500 text-sm">No specific skills listed.</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-md font-bold text-gray-900 mb-4">Additional Criteria</h3>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex justify-between">
                                <span class="text-gray-500">Age:</span>
                                <span class="font-medium">{{ $job->min_age ?? 18 }} - {{ $job->max_age ?? 60 }} Years</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500">Gender:</span>
                                <span class="font-medium">{{ $job->gender_preference ?? 'Any' }}</span>
                            </li>
                            @if($job->is_walkin && $job->interview_slot)
                            <li class="pt-3 border-t border-gray-100">
                                <span class="block text-pink-600 font-bold text-xs uppercase mb-1">Walk-in Interview</span>
                                <span class="font-medium"><i class="fa-regular fa-clock mr-1"></i> {{ $job->interview_slot->format('d M, Y @ h:i A') }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'matching'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-indigo-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-indigo-900">Recommended Candidates</h3>
                        <p class="text-sm text-indigo-600">Candidates from your pool matching job skills.</p>
                    </div>
                    <a href="{{ route('partner.candidates.check') }}" class="text-xs bg-white text-indigo-700 font-bold px-3 py-1.5 rounded border border-indigo-200 hover:bg-indigo-50">
                        + Add Manual
                    </a>
                </div>

                @if($matchingCandidates->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
                        @foreach($matchingCandidates as $candidate)
                            <div class="group relative bg-white border border-gray-200 rounded-lg p-5 hover:border-indigo-400 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $candidate->first_name }} {{ $candidate->last_name }}</h4>
                                        <p class="text-xs text-gray-500">{{ $candidate->job_role_preference ?? 'No Role' }}</p>
                                    </div>
                                    <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded">MATCH</span>
                                </div>
                                
                                <div class="text-xs text-gray-600 space-y-1 mb-4">
                                    <p><i class="fa-solid fa-location-dot w-4 text-gray-400"></i> {{ $candidate->location }}</p>
                                    <p><i class="fa-solid fa-briefcase w-4 text-gray-400"></i> {{ $candidate->experience_status }}</p>
                                    <p><i class="fa-solid fa-graduation-cap w-4 text-gray-400"></i> {{ $candidate->education_level }}</p>
                                </div>

                                <div class="pt-3 border-t border-gray-100">
                                    <form action="{{ route('partner.jobs.submit', $job->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="candidate_ids[]" value="{{ $candidate->id }}">
                                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 rounded transition">
                                            Apply Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-users-slash text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No Auto-Matches Found</h3>
                        <p class="text-gray-500 text-sm mt-1">Try adding a new candidate specifically for this job.</p>
                        <a href="{{ route('partner.candidates.check') }}" class="mt-4 inline-block text-indigo-600 font-bold hover:underline">Add Candidate</a>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="activeTab === 'applied'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-green-50">
                    <h3 class="text-lg font-bold text-green-900">Application History</h3>
                    <p class="text-sm text-green-700">Candidates you have already submitted for this role.</p>
                </div>

                @if($appliedApplications->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Candidate</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Applied Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Client Feedback</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($appliedApplications as $app)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-gray-900">{{ $app->candidate->first_name }} {{ $app->candidate->last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $app->candidate->email ?? $app->candidate->phone_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $app->created_at->format('d M, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $app->status === 'Approved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $app->status === 'Rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $app->status === 'Pending Review' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                            {{ $app->hiring_status ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-gray-500 italic">
                        No candidates have applied to this job yet.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
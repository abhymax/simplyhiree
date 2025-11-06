
@extends('layouts.app')

@section('content')
<div class="bg-gray-100 py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Top Search and Filters -->
        <form action="{{ route('partner.jobs') }}" method="GET">
             <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center space-x-4">
                    <input type="text" name="search" placeholder="Search by Title, Skills, Company" class="form-input w-full rounded-md border-gray-300" value="{{ request('search') }}">
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-md">SEARCH</button>
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <div class="font-semibold text-gray-700">Filters:</div>
                    <select name="location" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                    <select name="job_type" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Job Type</option>
                        @foreach($job_types as $job_type)
                            <option value="{{ $job_type }}" {{ request('job_type') == $job_type ? 'selected' : '' }}>{{ $job_type }}</option>
                        @endforeach
                    </select>
                    <select name="experience_level_id" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Experience</option>
                        @foreach($experienceLevels as $level)
                            <option value="{{ $level->id }}" {{ request('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                     <select name="education_level_id" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Education</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level->id }}" {{ request('education_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('partner.jobs') }}" class="text-blue-600 font-semibold text-sm">Reset All</a>
                </div>
            </div>
        </form>

        <!-- Jobs Table -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <table class="min-w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Job Name</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Details</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Application Statistics</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Payout</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($jobs as $job)
                        <tr>
                            <!-- Job Name Column -->
                            <td class="py-4 px-6 align-top">
                                <div class="flex items-start space-x-3">
                                    <svg class="h-5 w-5 text-gray-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <div>
                                        <div class="font-bold text-blue-600 text-md">{{ $job->title }}
                                             @if($job->is_walkin)
                                                <span class="ml-2 bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-0.5 rounded">Walkin</span>
                                            @endif
                                            @if($job->job_type_tags)
                                                @foreach($job->job_type_tags as $tag)
                                                     @if(strtolower($tag) === 'new')
                                                        <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $tag }}</span>
                                                     @endif
                                                @endforeach
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $job->category ?? 'N/A' }}</p>
                                        <p class="font-semibold text-gray-800 mt-1">{{ $job->company_name }} - <span class="font-normal">{{ $job->location }}</span></p>
                                        <p class="text-sm text-gray-500 mt-2 font-semibold">{{ $job->openings ?? 'N/A' }} openings</p>
                                        @if($job->is_walkin && $job->interview_slot)
                                        <div class="flex items-center mt-1">
                                            <span class="h-2 w-2 bg-green-500 rounded-full mr-2"></span>
                                            <p class="text-sm text-gray-700 font-semibold">Slot: {{ $job->interview_slot->format('g:i A') }}</p>
                                        </div>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-2">Posted on : {{ $job->created_at->format('jS M y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Details Column -->
                            <td class="py-4 px-6 align-top">
                                <p class="font-bold text-gray-800">{{ $job->salary }}</p>
                                <p class="text-sm text-gray-600">{{ $job->educationLevel->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">Exp: {{ $job->experienceLevel->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ $job->min_age ?? 'N/A' }} - {{ $job->max_age ?? 'N/A' }} yrs age</p>
                                <p class="text-sm text-gray-600">{{ $job->gender_preference ?? 'Any' }}</p>
                            </td>

                            <!-- Application Statistics Column -->
                            <td class="py-4 px-6 align-top">
                                <div class="space-y-2">
                                    @foreach(['applied', 'screened', 'turned_up', 'selected', 'joined'] as $status)
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-700 w-20">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-gray-400 h-2.5 rounded-full" style="width: {{ $job->stats->applied > 0 ? ($job->stats->$status / $job->stats->applied) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600 ml-2 font-semibold">{{ $job->stats->$status }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Payout Column -->
                            <td class="py-4 px-6 align-top">
                                <p class="font-bold text-lg text-gray-800">₹{{ number_format($job->payout_amount, 0) }}/-</p>
                                <p class="text-sm text-gray-600">Paid on {{ $job->minimum_stay_days + 1 }}th day of joining</p>
                                <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="text-blue-600 font-semibold text-sm hover:underline mt-2 inline-block">View & Apply</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 px-6 text-center text-gray-500">
                                No jobs match your current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <!-- Pagination -->
            <div class="px-6 py-4">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

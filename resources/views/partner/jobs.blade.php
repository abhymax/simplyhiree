
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
                    <select name="experience" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Experience</option>
                        @foreach($experiences as $experience)
                            <option value="{{ $experience }}" {{ request('experience') == $experience ? 'selected' : '' }}>{{ $experience }}</option>
                        @endforeach
                    </select>
                     <select name="education" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm">
                        <option value="">Education</option>
                        @foreach($educations as $education)
                            <option value="{{ $education }}" {{ request('education') == $education ? 'selected' : '' }}>{{ $education }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('partner.jobs') }}" class="text-blue-600 font-semibold text-sm">Reset All</a>
                </div>
            </div>
        </form>

        <!-- Jobs Table -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">All Jobs ({{ $jobs->total() }})</h2>
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($jobs as $job)
                    <div class="grid grid-cols-12 gap-x-6 p-6">
                        <!-- Job Name Column -->
                        <div class="col-span-12 md:col-span-4">
                            <div class="flex items-start space-x-3">
                                <div>
                                    <h3 class="font-bold text-blue-600 text-lg">{{ $job->title }}
                                        @if($job->job_type_tags)
                                            @foreach($job->job_type_tags as $tag)
                                                <span class="ml-2 bg-cyan-100 text-cyan-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">{{ $tag }}</span>
                                            @endforeach
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ $job->category ?? 'N/A' }}</p>
                                    <p class="font-semibold text-gray-800 mt-1">{{ $job->company_name }} - <span class="font-normal">{{ $job->location }}</span></p>
                                    <p class="text-sm text-gray-500 mt-2"><span class="font-bold text-green-600">{{ $job->openings ?? 'N/A' }} openings</span></p>
                                    <p class="text-xs text-gray-400 mt-2">Posted on : {{ $job->created_at->format('jS M y') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Details Column -->
                        <div class="col-span-12 md:col-span-2 mt-4 md:mt-0">
                            <p class="font-bold text-gray-800">{{ $job->salary }}</p>
                            <p class="text-sm text-gray-600">{{ $job->education_level }}</p>
                            <p class="text-sm text-gray-600">Exp: {{ $job->experience_required }}</p>
                            <p class="text-sm text-gray-600">{{ $job->min_age ?? 'N/A' }} - {{ $job->max_age ?? 'N/A' }} yrs age</p>
                            <p class="text-sm text-gray-600">{{ $job->gender_preference ?? 'Any' }}</p>
                        </div>

                        <!-- Application Statistics Column -->
                        <div class="col-span-12 md:col-span-3 mt-4 md:mt-0">
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
                        </div>

                        <!-- Payout Column -->
                        <div class="col-span-12 md:col-span-3 mt-4 md:mt-0 md:text-right">
                            <p class="font-bold text-xl text-gray-800">₹{{ number_format($job->payout_amount, 0) }}/-</p>
                            <p class="text-sm text-gray-600">Paid on {{ $job->minimum_stay_days + 1 }}th day of joining</p>
                            <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="text-blue-600 font-semibold text-sm hover:underline mt-2 inline-block">View & Apply</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        No jobs match your current filters.
                    </div>
                @endforelse
            </div>
             <!-- Pagination -->
            <div class="px-6 py-4">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

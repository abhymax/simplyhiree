@extends('layouts.app')

@section('content')
<div class="bg-gray-100 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <form action="{{ route('partner.jobs') }}" method="GET">
             <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center space-x-4">
                    <input type="text" name="search" placeholder="Search by Title, Skills, Company" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ request('search') }}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md transition duration-150">SEARCH</button>
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <div class="font-semibold text-gray-700">Filters:</div>
                    <select name="location" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                    <select name="job_type" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Job Type</option>
                        @foreach($job_types as $job_type)
                            <option value="{{ $job_type }}" {{ request('job_type') == $job_type ? 'selected' : '' }}>{{ $job_type }}</option>
                        @endforeach
                    </select>
                    <select name="experience_level_id" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Experience</option>
                        @foreach($experienceLevels as $level)
                            <option value="{{ $level->id }}" {{ request('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                     <select name="education_level_id" onchange="this.form.submit()" class="form-select rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Education</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level->id }}" {{ request('education_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('partner.jobs') }}" class="text-blue-600 font-semibold text-sm hover:underline">Reset All</a>
                </div>
            </div>
        </form>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
            <table class="min-w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Job Designation</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Requirements</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Applications</th>
                        <th class="py-3 px-6 text-left text-sm font-bold uppercase">Payout & Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6 align-top">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1"><i class="fa-solid fa-briefcase text-gray-400 text-lg"></i></div>
                                    <div>
                                        <a href="{{ route('partner.jobs.show', $job->id) }}" class="font-bold text-blue-600 text-md hover:text-blue-800 hover:underline block">
                                            {{ $job->title }}
                                            @if($job->is_walkin)
                                                <span class="ml-2 bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-0.5 rounded">Walkin</span>
                                            @endif
                                        </a>
                                        <p class="text-sm text-gray-600">{{ $job->jobCategory->name ?? 'N/A' }}</p>
                                        <p class="font-semibold text-gray-800 mt-1">{{ $job->company_name }}</p>
                                        <p class="text-sm text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i> {{ $job->location }}</p>
                                        
                                        <p class="text-xs text-gray-400 mt-2">Posted: {{ $job->created_at->format('d M, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 align-top text-sm">
                                <div class="space-y-1">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-500">Openings:</span>
                                        <span class="font-bold text-gray-800">{{ $job->openings ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-500">Exp:</span>
                                        <span class="text-gray-700">{{ $job->experienceLevel->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-500">Edu:</span>
                                        <span class="text-gray-700">{{ $job->educationLevel->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-500">Age:</span>
                                        <span class="text-gray-700">
                                            @if($job->min_age && $job->max_age)
                                                {{ $job->min_age }} - {{ $job->max_age }} yrs
                                            @else
                                                Any
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-500">Salary:</span>
                                        <span class="font-bold text-gray-800">{{ $job->salary ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-6 align-top">
                                <div class="space-y-2">
                                    @foreach(['applied', 'screened', 'turned_up', 'selected', 'joined'] as $status)
                                    <div class="flex items-center">
                                        <span class="text-xs text-gray-500 w-24 uppercase font-bold">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 flex-grow mx-2">
                                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $job->stats->applied > 0 ? ($job->stats->$status / $job->stats->applied) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-700 font-bold w-6 text-right">{{ $job->stats->$status }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-4 px-6 align-top text-center">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Payout</p>
                                <p class="font-bold text-xl text-green-600 mt-1">₹{{ number_format($job->payout_amount, 0) }}</p>
                                <p class="text-xs text-gray-400 mb-3">after {{ $job->minimum_stay_days ?? 0 }} days</p>
                                
                                <div class="space-y-2">
                                    <a href="{{ route('partner.jobs.show', $job->id) }}" class="block w-full border border-indigo-600 text-indigo-600 text-sm font-bold px-4 py-2 rounded shadow-sm hover:bg-indigo-50 transition">
                                        View Details
                                    </a>
                                    
                                    <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="block w-full bg-indigo-600 text-white text-sm font-bold px-4 py-2 rounded shadow hover:bg-indigo-700 transition">
                                        Apply Now
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 px-6 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                <p>No approved jobs match your current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
             <div class="px-6 py-4 border-t border-gray-200">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
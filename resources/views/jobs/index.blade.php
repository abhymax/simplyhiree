@extends('layouts.app')

@section('content')
<div class="bg-gray-100 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900">Find Your Next Opportunity</h1>
            <p class="mt-4 text-xl text-gray-600">Browse thousands of jobs from top companies and agencies.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-lg shadow-sm sticky top-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Filter Jobs</h2>
                    
                    <form action="{{ route('jobs.index') }}" method="GET">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Job title, skill, or company" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <select name="location" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                            <select name="job_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Types</option>
                                @foreach($jobTypes as $type)
                                    <option value="{{ $type }}" {{ request('job_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                            <select name="experience_level_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Any Experience</option>
                                @foreach($experienceLevels as $level)
                                    <option value="{{ $level->id }}" {{ request('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col space-y-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                                Apply Filters
                            </button>
                            @if(request()->anyFilled(['search', 'location', 'category_id', 'job_type', 'experience_level_id']))
                                <a href="{{ route('jobs.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded transition">
                                    Clear All
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-6">
                @forelse($jobs as $job)
                    <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition duration-200 border border-transparent hover:border-blue-100 relative group">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition">
                                    <a href="{{ route('jobs.show', $job->id) }}">
                                        {{ $job->title }}
                                    </a>
                                </h3>
                                <p class="text-gray-600 font-medium">{{ $job->company_name }}</p>
                                
                                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-location-dot mr-1.5"></i> {{ $job->location }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-briefcase mr-1.5"></i> {{ $job->job_type }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-money-bill mr-1.5"></i> {{ $job->salary ?? 'Not Disclosed' }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fa-regular fa-clock mr-1.5"></i> Posted {{ $job->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                @if($job->skills_required)
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach(array_slice(explode(',', $job->skills_required), 0, 4) as $skill)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                                                {{ trim($skill) }}
                                            </span>
                                        @endforeach
                                        @if(count(explode(',', $job->skills_required)) > 4)
                                            <span class="text-xs text-gray-500 self-center">+ more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="hidden sm:block">
                                <a href="{{ route('jobs.show', $job->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <i class="fa-solid fa-search fa-2x"></i>
                        </div>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No jobs found</h3>
                        <p class="mt-1 text-gray-500">Try adjusting your search or filters to find what you're looking for.</p>
                        <div class="mt-6">
                            <a href="{{ route('jobs.index') }}" class="text-blue-600 hover:text-blue-500 font-medium">
                                Clear all filters
                            </a>
                        </div>
                    </div>
                @endforelse

                <div class="mt-6">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
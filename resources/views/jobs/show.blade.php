@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        @if((isset($isOwner) && $isOwner) || (isset($isAdmin) && $isAdmin))
            <div class="bg-white border-l-4 border-indigo-500 shadow-md sm:rounded-lg mb-6 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fa-solid fa-gear mr-2 text-indigo-500"></i> Job Management
                            </h3>
                            <p class="text-sm text-gray-500">Manage visibility and status of this job posting.</p>
                        </div>
                        
                        <div class="mt-2 md:mt-0">
                            <span class="text-sm font-semibold mr-2">Current Status:</span>
                            @if($job->status === 'approved')
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Live / Visible
                                </span>
                            @elseif($job->status === 'pending_approval')
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-clock mr-1"></i> Pending Approval
                                </span>
                            @elseif($job->status === 'on_hold')
                                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-pause mr-1"></i> On Hold
                                </span>
                            @elseif($job->status === 'closed')
                                <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-eye-slash mr-1"></i> Closed / Hidden
                                </span>
                            @elseif($job->status === 'rejected')
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-ban mr-1"></i> Rejected
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg flex flex-wrap gap-3 items-center">
                        @php
                            // Determine route prefix based on user role
                            $prefix = (isset($isAdmin) && $isAdmin) ? 'admin' : 'client';
                        @endphp

                        @if($job->status !== 'approved')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-bold hover:bg-green-700 transition shadow-sm flex items-center">
                                    <i class="fa-solid fa-play mr-2"></i> Make Live
                                </button>
                            </form>
                        @endif

                        @if($job->status !== 'on_hold')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="on_hold">
                                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-bold hover:bg-yellow-600 transition shadow-sm flex items-center">
                                    <i class="fa-solid fa-pause mr-2"></i> Put On Hold
                                </button>
                            </form>
                        @endif

                        @if($job->status !== 'closed')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-bold hover:bg-gray-700 transition shadow-sm flex items-center">
                                    <i class="fa-solid fa-eye-slash mr-2"></i> Close / Hide
                                </button>
                            </form>
                        @endif

                        <div class="flex-grow"></div>

                        <form action="{{ route($prefix . '.jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this job permanently? This action cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 border border-red-200 rounded-md text-sm font-bold hover:bg-red-200 transition flex items-center">
                                <i class="fa-solid fa-trash-can mr-2"></i> Delete Job
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8">
                <div class="flex flex-col md:flex-row justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $job->title }}</h1>
                        <p class="text-xl text-gray-600 mt-1">{{ $job->company_name }}</p>
                        <div class="flex flex-wrap items-center mt-3 text-sm text-gray-500 gap-4">
                            <span class="flex items-center"><i class="fa-solid fa-location-dot mr-2 text-indigo-500"></i> {{ $job->location }}</span>
                            <span class="flex items-center"><i class="fa-solid fa-briefcase mr-2 text-indigo-500"></i> {{ $job->job_type }}</span>
                            <span class="flex items-center"><i class="fa-solid fa-money-bill mr-2 text-indigo-500"></i> {{ $job->salary ?? 'Not Disclosed' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-0">
                        @auth
                            @if($hasApplied)
                                <button disabled class="bg-gray-100 text-gray-500 border border-gray-200 font-bold py-3 px-8 rounded-lg cursor-not-allowed flex items-center">
                                    <i class="fa-solid fa-check mr-2"></i> Applied
                                </button>
                            @elseif(auth()->user()->hasRole('candidate'))
                                <form action="{{ route('jobs.apply', $job->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition transform hover:-translate-y-0.5">
                                        Apply Now
                                    </button>
                                </form>
                            @elseif((isset($isOwner) && $isOwner))
                                <span class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg border border-indigo-100 font-medium">
                                    You posted this job
                                </span>
                            @else
                                <div class="text-sm text-gray-500 bg-gray-100 p-2 rounded">
                                    Login as Candidate to Apply
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition">
                                Login to Apply
                            </a>
                        @endauth
                    </div>
                </div>

                <hr class="my-8 border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="md:col-span-2 space-y-8">
                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-l-4 border-indigo-500 pl-3">Job Description</h3>
                            <div class="prose max-w-none text-gray-700 leading-relaxed">
                                {!! nl2br(e($job->description)) !!}
                            </div>
                        </section>

                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-l-4 border-indigo-500 pl-3">Required Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                @if($job->skills_required)
                                    @foreach(explode(',', $job->skills_required) as $skill)
                                        <span class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-sm font-medium border border-indigo-100">
                                            {{ trim($skill) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-500 italic">No specific skills listed.</span>
                                @endif
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 shadow-sm">
                            <h3 class="font-bold text-gray-900 mb-6 flex items-center">
                                <i class="fa-solid fa-list-check mr-2 text-gray-400"></i> Job Overview
                            </h3>
                            
                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-500">Category</span>
                                    <span class="font-semibold text-gray-800">{{ $job->jobCategory->name ?? $job->category ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-500">Experience</span>
                                    <span class="font-semibold text-gray-800">{{ $job->experienceLevel->name ?? $job->experience_required ?? 'Any' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-500">Education</span>
                                    <span class="font-semibold text-gray-800">{{ $job->educationLevel->name ?? $job->education_level ?? 'Any' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-500">Posted On</span>
                                    <span class="font-semibold text-gray-800">{{ $job->created_at->format('M d, Y') }}</span>
                                </div>
                                
                                @if($job->application_deadline)
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-500">Deadline</span>
                                        <span class="text-red-600 font-bold">
                                            {{ \Carbon\Carbon::parse($job->application_deadline)->format('M d, Y') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(!empty($job->job_type_tags))
                            <div class="bg-white border border-gray-200 p-4 rounded-xl">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Tags</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($job->job_type_tags as $tag)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">
                                            #{{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($job->company_website)
                            <a href="{{ $job->company_website }}" target="_blank" class="block w-full text-center border-2 border-gray-200 text-gray-600 font-bold py-3 rounded-xl hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition">
                                Visit Website <i class="fa-solid fa-external-link-alt ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
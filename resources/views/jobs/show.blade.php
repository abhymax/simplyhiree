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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $job->title }}</h1>
                        <p class="text-xl text-gray-600 mt-1">{{ $job->company_name }}</p>
                        <div class="flex items-center mt-2 text-sm text-gray-500 space-x-4">
                            <span><i class="fa-solid fa-location-dot mr-1"></i> {{ $job->location }}</span>
                            <span><i class="fa-solid fa-briefcase mr-1"></i> {{ $job->job_type }}</span>
                            <span><i class="fa-solid fa-money-bill mr-1"></i> {{ $job->salary ?? 'Not Disclosed' }}</span>
                        </div>
                    </div>
                    
                    @auth
                        @if($hasApplied)
                            <button disabled class="bg-gray-400 text-white font-bold py-2 px-6 rounded cursor-not-allowed">
                                Already Applied
                            </button>
                        @elseif(auth()->user()->hasRole('candidate'))
                            <form action="{{ route('jobs.apply', $job->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                    Apply Now
                                </button>
                            </form>
                        @else
                            <div class="text-sm text-gray-500 bg-gray-100 p-2 rounded">
                                Login as Candidate to Apply
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                            Login to Apply
                        </a>
                    @endauth
                </div>

                <hr class="my-6 border-gray-200">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="md:col-span-2 space-y-6">
                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Job Description</h3>
                            <div class="prose max-w-none text-gray-700">
                                {!! nl2br(e($job->description)) !!}
                            </div>
                        </section>

                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Required Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $job->skills_required) as $skill)
                                    <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ trim($skill) }}
                                    </span>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="font-bold text-gray-900 mb-4">Job Overview</h3>
                            
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Category</span>
                                    <span class="font-medium">{{ $job->category->name ?? 'General' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Experience Required</span>
                                    <span class="font-medium">{{ $job->experienceLevel->name ?? $job->experience_required }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Education</span>
                                    <span class="font-medium">{{ $job->educationLevel->name ?? $job->education_level }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Posted Date</span>
                                    <span class="font-medium">{{ $job->created_at->format('M d, Y') }}</span>
                                </div>
                                @if($job->application_deadline)
                                <div>
                                    <span class="text-gray-500 block">Deadline</span>
                                    <span class="text-red-600 font-medium">{{ \Carbon\Carbon::parse($job->application_deadline)->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($job->company_website)
                            <a href="{{ $job->company_website }}" target="_blank" class="block w-full text-center border border-gray-300 text-gray-700 font-medium py-2 rounded hover:bg-gray-50">
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
@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="{{ route('admin.jobs.pending') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Pending Jobs
            </a>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold leading-6 text-gray-900">{{ $job->title }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $job->company_name }} • {{ $job->location }}</p>
                </div>
                <span class="px-3 py-1 text-xs font-bold rounded-full 
                    {{ $job->status === 'approved' ? 'bg-green-100 text-green-800' : 
                       ($job->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                </span>
            </div>
            
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Client / Poster</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $job->user->name ?? 'Internal' }} ({{ $job->user->email ?? 'N/A' }})</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Job Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $job->job_type }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $job->salary ?? 'Not Disclosed' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Requirements</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <ul class="list-disc pl-5">
                                <li><strong>Experience:</strong> {{ $job->experienceLevel->name ?? 'Any' }}</li>
                                <li><strong>Education:</strong> {{ $job->educationLevel->name ?? 'Any' }}</li>
                                <li><strong>Openings:</strong> {{ $job->openings }}</li>
                            </ul>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Full Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-line">{{ $job->description }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Skills Required</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $job->skills_required ?? 'None Listed' }}</dd>
                    </div>
                </dl>
            </div>
            
            {{-- Action Buttons --}}
            @if($job->status === 'pending_approval')
            <div class="px-4 py-5 bg-gray-50 border-t border-gray-200 flex justify-end space-x-4">
                <form action="{{ route('admin.jobs.approve', $job) }}" method="POST" class="flex items-center space-x-2">
                    @csrf
                    <input type="number" name="payout_amount" placeholder="Payout (₹)" class="w-32 rounded-md border-gray-300 shadow-sm text-sm" required>
                    <input type="number" name="minimum_stay_days" placeholder="Min. Stay" class="w-24 rounded-md border-gray-300 shadow-sm text-sm" required>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Approve & Live
                    </button>
                </form>
                
                <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reject
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
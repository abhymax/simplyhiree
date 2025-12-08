@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="{{ route('admin.jobs.pending') }}" class="text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Pending Jobs
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Manage Partner Visibility</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Control which partners can see and apply for <strong>{{ $job->title }}</strong> ({{ $job->company_name }}).
                    </p>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                        <div>
                            <span class="block text-gray-500 font-bold">Location</span>
                            {{ $job->location }}
                        </div>
                        <div>
                            <span class="block text-gray-500 font-bold">Experience Required</span>
                            {{ $job->experienceLevel->name ?? 'Not Specified' }}
                        </div>
                        <div>
                            <span class="block text-gray-500 font-bold">Education</span>
                            {{ $job->educationLevel->name ?? 'Not Specified' }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.jobs.exclusions.update', $job->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Exclude Partners</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Selected partners will <strong>NOT</strong> see this job in their dashboard.
                        </p>

                        @if($allPartners->isEmpty())
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-yellow-700">
                                <p>No partners found in the system.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border p-4 rounded-lg max-h-96 overflow-y-auto">
                                @foreach($allPartners as $partner)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="partner_{{ $partner->id }}" 
                                                   name="excluded_partners[]" 
                                                   type="checkbox" 
                                                   value="{{ $partner->id }}"
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                   {{ in_array($partner->id, $excludedPartnerIds) ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="partner_{{ $partner->id }}" class="font-medium text-gray-700">
                                                {{ $partner->name }}
                                            </label>
                                            <p class="text-gray-500 text-xs">{{ $partner->email }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Update Exclusions
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
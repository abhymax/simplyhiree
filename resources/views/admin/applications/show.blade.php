<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Details') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.applications.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to All Applications
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white shadow sm:rounded-lg p-6 border-t-4 border-indigo-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</h1>
                                <p class="text-gray-500"><i class="fa-solid fa-envelope mr-1"></i> {{ $application->candidate->email }}</p>
                                <p class="text-gray-500"><i class="fa-solid fa-phone mr-1"></i> {{ $application->candidate->phone_number }}</p>
                            </div>
                            <span class="bg-gray-100 text-gray-800 text-xs font-bold px-3 py-1 rounded-full uppercase">
                                ID: #{{ $application->id }}
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4 border-t pt-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Current Location</p>
                                <p class="text-gray-800">{{ $application->candidate->location ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Gender</p>
                                <p class="text-gray-800">{{ $application->candidate->gender ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Experience</p>
                                <p class="text-gray-800">{{ $application->candidate->experience_status ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Expected CTC</p>
                                <p class="text-gray-800">{{ $application->candidate->expected_ctc ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Key Skills</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $application->candidate->skills) as $skill)
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded text-xs border border-indigo-100">{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Resume / CV</h3>
                        @if($application->candidate->resume_path)
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-file-pdf text-red-500 text-3xl mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Download Resume</p>
                                        <p class="text-xs text-gray-500">Click to view file</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $application->candidate->resume_path) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 text-sm">
                                    Download
                                </a>
                            </div>
                        @else
                            <p class="text-gray-500 italic">No resume uploaded.</p>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-1 space-y-6">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Application Status</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Admin Approval</p>
                                <span class="inline-block mt-1 px-3 py-1 text-sm font-bold rounded-full 
                                    {{ strtolower($application->status) == 'approved' ? 'bg-green-100 text-green-800' : (strtolower($application->status) == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 uppercase">Client Progress</p>
                                <span class="inline-block mt-1 px-3 py-1 text-sm font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $application->hiring_status ?? 'Pending Client Action' }}
                                </span>
                            </div>

                            @if($application->interview_at)
                            <div class="p-3 bg-blue-50 rounded border border-blue-100">
                                <p class="text-xs text-blue-800 font-bold"><i class="fa-solid fa-calendar mr-1"></i> Interview Scheduled</p>
                                <p class="text-sm text-blue-900 mt-1">{{ \Carbon\Carbon::parse($application->interview_at)->format('M d, Y - h:i A') }}</p>
                            </div>
                            @endif

                            @if($application->joining_date)
                            <div class="p-3 bg-green-50 rounded border border-green-100">
                                <p class="text-xs text-green-800 font-bold"><i class="fa-solid fa-flag-checkered mr-1"></i> Joining Date</p>
                                <p class="text-sm text-green-900 mt-1">{{ \Carbon\Carbon::parse($application->joining_date)->format('M d, Y') }}</p>
                            </div>
                            @endif
                        </div>

                        @if(strtolower($application->status) === 'pending review')
                        <div class="mt-6 pt-6 border-t border-gray-100 flex gap-2">
                             <form action="{{ route('admin.applications.approve', $application->id) }}" method="POST" class="w-1/2">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded shadow hover:bg-green-700 text-sm font-bold">Approve</button>
                            </form>
                            <form action="{{ route('admin.applications.reject', $application->id) }}" method="POST" class="w-1/2">
                                @csrf
                                <button type="submit" class="w-full bg-white border border-red-300 text-red-600 py-2 rounded shadow hover:bg-red-50 text-sm font-bold">Reject</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold leading-tight">My Candidates</h2>
                        <p class="text-gray-600 mt-1">Manage your talent pool and their applications.</p>
                    </div>
                    <a href="{{ route('partner.candidates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out">
                        <i class="fa-solid fa-plus mr-2"></i> Add New Candidate
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Contact Info
                                </th>
                                <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Role / Skills
                                </th>
                                <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Experience
                                </th>
                                <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Resume
                                </th>
                                <th class="px-6 py-3 border-b border-gray-200 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($candidates as $candidate)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $candidate->first_name }} {{ $candidate->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Added: {{ $candidate->created_at->format('M d, Y') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fa-regular fa-envelope mr-1 text-gray-400"></i> {{ $candidate->email ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <i class="fa-solid fa-phone mr-1 text-gray-400"></i> {{ $candidate->phone_number }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $candidate->job_role_preference ?? 'Not Specified' }}
                                        </div>
                                        @if($candidate->skills)
                                            <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                                {{ Str::limit($candidate->skills, 40) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $candidate->experience_status === 'Experienced' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $candidate->experience_status ?? 'Fresher' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($candidate->resume_path)
                                            <a href="{{ asset('storage/'.$candidate->resume_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900 flex items-center">
                                                <i class="fa-regular fa-file-pdf mr-1"></i> View
                                            </a>
                                        @else
                                            <span class="text-gray-400">No Resume</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('partner.candidates.edit', $candidate->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded transition hover:bg-indigo-100">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                                            <p class="text-lg">No candidates found.</p>
                                            <p class="text-sm mb-4">Start by adding your first candidate to the pool.</p>
                                            <a href="{{ route('partner.candidates.create') }}" class="text-blue-600 hover:underline">
                                                Add Candidate Now
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $candidates->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
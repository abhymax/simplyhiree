@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="mb-6 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-circle-check mr-3 text-2xl"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Partner Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">
                    My Candidates
                </h1>
                <p class="text-blue-200 mt-2 text-sm md:text-base">
                    Manage your talent pool and update candidate profiles.
                </p>
            </div>

            <a href="{{ route('partner.candidates.create') }}" class="mt-5 md:mt-0 inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-bold px-5 py-3 rounded-xl transition shadow-lg">
                <i class="fa-solid fa-plus"></i> Add New Candidate
            </a>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900/60 text-blue-100 border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Contact Info</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Role / Skills</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Experience</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Resume</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @forelse($candidates as $candidate)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm font-semibold text-white">
                                        {{ $candidate->first_name }} {{ $candidate->last_name }}
                                    </div>
                                    <div class="text-xs text-slate-300">
                                        Added: {{ $candidate->created_at->format('M d, Y') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    <div class="text-sm text-slate-100">
                                        <i class="fa-regular fa-envelope mr-1 text-blue-300"></i> {{ $candidate->email ?? 'N/A' }}
                                    </div>
                                    <div class="text-sm text-slate-300 mt-1">
                                        <i class="fa-solid fa-phone mr-1 text-blue-300"></i> {{ $candidate->phone_number }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <div class="text-sm text-white">
                                        {{ $candidate->job_role_preference ?? 'Not Specified' }}
                                    </div>
                                    @if($candidate->skills)
                                        <div class="text-xs text-slate-300 mt-1 truncate max-w-xs">
                                            {{ Str::limit($candidate->skills, 40) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap align-top">
                                    @php
                                        $isExperienced = ($candidate->experience_status ?? 'Fresher') === 'Experienced';
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $isExperienced ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/30' : 'bg-blue-500/20 text-blue-200 border border-blue-400/30' }}">
                                        {{ $candidate->experience_status ?? 'Fresher' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap align-top text-sm">
                                    @if($candidate->resume_path)
                                        <a href="{{ asset('storage/'.$candidate->resume_path) }}" target="_blank" class="text-blue-200 hover:text-white inline-flex items-center">
                                            <i class="fa-regular fa-file-pdf mr-1"></i> View
                                        </a>
                                    @else
                                        <span class="text-slate-400">No Resume</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right align-top text-sm font-medium">
                                    <a href="{{ route('partner.candidates.edit', $candidate->id) }}" class="inline-flex items-center border border-blue-300/40 text-blue-100 px-3 py-1.5 rounded-lg transition hover:bg-blue-500/20">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-slate-300">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-400"></i>
                                        <p class="text-lg text-white">No candidates found.</p>
                                        <p class="text-sm mb-4 text-slate-300">Start by adding your first candidate to the pool.</p>
                                        <a href="{{ route('partner.candidates.create') }}" class="text-blue-200 hover:text-white font-semibold">
                                            Add Candidate Now
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/10 text-slate-100">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
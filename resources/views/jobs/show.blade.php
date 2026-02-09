@extends('layouts.app')

@section('content')
<style>
    .fx-card {
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .fx-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(14, 165, 233, 0.22);
        border-color: rgba(255, 255, 255, 0.30);
    }
    .fx-btn {
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .fx-btn:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 24px rgba(59, 130, 246, 0.35);
        filter: brightness(1.04);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[22rem] h-[22rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="mb-4 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-500/20 border border-rose-400/40 text-rose-100 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if((isset($isOwner) && $isOwner) || (isset($isAdmin) && $isAdmin))
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl mb-6 overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-white/10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-white"><i class="fa-solid fa-gear mr-2 text-cyan-300"></i> Job Management</h3>
                            <p class="text-sm text-blue-100">Manage visibility and status of this job posting.</p>
                        </div>

                        <div>
                            <span class="text-sm font-semibold mr-2 text-blue-100">Current Status:</span>
                            @if($job->status === 'approved')
                                <span class="bg-emerald-500/20 text-emerald-100 px-3 py-1 rounded-full text-sm font-bold border border-emerald-400/40">Live / Visible</span>
                            @elseif($job->status === 'pending_approval')
                                <span class="bg-amber-500/20 text-amber-100 px-3 py-1 rounded-full text-sm font-bold border border-amber-400/40">Pending Approval</span>
                            @elseif($job->status === 'on_hold')
                                <span class="bg-orange-500/20 text-orange-100 px-3 py-1 rounded-full text-sm font-bold border border-orange-400/40">On Hold</span>
                            @elseif($job->status === 'closed')
                                <span class="bg-slate-500/20 text-slate-100 px-3 py-1 rounded-full text-sm font-bold border border-slate-400/40">Closed / Hidden</span>
                            @elseif($job->status === 'rejected')
                                <span class="bg-rose-500/20 text-rose-100 px-3 py-1 rounded-full text-sm font-bold border border-rose-400/40">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-white/5">
                    @php
                        $prefix = (isset($isAdmin) && $isAdmin) ? 'admin' : 'client';
                    @endphp

                    <div class="flex flex-wrap gap-3 items-center">
                        @if($job->status !== 'approved')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="fx-btn px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-sm font-bold">
                                    Make Live
                                </button>
                            </form>
                        @endif

                        @if($job->status !== 'on_hold')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="on_hold">
                                <button type="submit" class="fx-btn px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-md text-sm font-bold">
                                    Put On Hold
                                </button>
                            </form>
                        @endif

                        @if($job->status !== 'closed')
                            <form action="{{ route($prefix . '.jobs.status.update', $job->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="fx-btn px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-md text-sm font-bold">
                                    Close / Hide
                                </button>
                            </form>
                        @endif

                        <div class="flex-grow"></div>

                        @if(isset($isAdmin) && $isAdmin)
                            <form action="{{ route('admin.jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this job permanently? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="fx-btn px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-md text-sm font-bold">
                                    Delete Job
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-300 bg-slate-800/60 border border-white/10 px-3 py-2 rounded-lg">
                                Delete is disabled for client accounts.
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-8 border-b border-white/10">
                <div class="flex flex-col md:flex-row justify-between items-start gap-5">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white">{{ $job->title }}</h1>
                        <p class="text-xl text-blue-100 mt-1">{{ $job->company_name }}</p>
                        <div class="flex flex-wrap items-center mt-3 text-sm text-blue-100 gap-4">
                            <span class="flex items-center"><i class="fa-solid fa-location-dot mr-2 text-cyan-300"></i> {{ $job->location }}</span>
                            <span class="flex items-center"><i class="fa-solid fa-briefcase mr-2 text-cyan-300"></i> {{ $job->job_type }}</span>
                            <span class="flex items-center"><i class="fa-solid fa-money-bill mr-2 text-cyan-300"></i> {{ $job->salary ?? 'Not Disclosed' }}</span>
                        </div>
                    </div>

                    <div class="mt-2 md:mt-0">
                        @auth
                            @if($hasApplied)
                                <button disabled class="bg-slate-700 text-slate-300 border border-slate-600 font-bold py-3 px-8 rounded-lg cursor-not-allowed flex items-center">
                                    <i class="fa-solid fa-check mr-2"></i> Applied
                                </button>
                            @elseif(auth()->user()->hasRole('candidate'))
                                <form action="{{ route('jobs.apply', $job->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="fx-btn bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-3 px-8 rounded-lg shadow-md">
                                        Apply Now
                                    </button>
                                </form>
                            @elseif((isset($isOwner) && $isOwner))
                                <span class="bg-indigo-500/20 text-indigo-100 px-4 py-2 rounded-lg border border-indigo-400/30 font-medium">
                                    You posted this job
                                </span>
                            @else
                                <div class="text-sm text-slate-300 bg-slate-800/70 p-2 rounded">
                                    Login as Candidate to Apply
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="fx-btn bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-3 px-8 rounded-lg shadow-md">
                                Login to Apply
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <div class="md:col-span-2 space-y-8">
                        <section class="fx-card bg-white/5 border border-white/10 rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4 border-l-4 border-cyan-400 pl-3">Job Description</h3>
                            <div class="text-blue-100 leading-relaxed whitespace-pre-line">
                                {!! nl2br(e($job->description)) !!}
                            </div>
                        </section>

                        <section class="fx-card bg-white/5 border border-white/10 rounded-2xl p-6">
                            <h3 class="text-lg font-bold text-white mb-4 border-l-4 border-cyan-400 pl-3">Required Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                @if($job->skills_required)
                                    @foreach(explode(',', $job->skills_required) as $skill)
                                        <span class="bg-indigo-500/20 text-indigo-100 px-4 py-1.5 rounded-full text-sm font-medium border border-indigo-400/30">
                                            {{ trim($skill) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-300 italic">No specific skills listed.</span>
                                @endif
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6">
                        <div class="fx-card bg-white/5 border border-white/10 p-6 rounded-2xl">
                            <h3 class="font-bold text-white mb-6 flex items-center">
                                <i class="fa-solid fa-list-check mr-2 text-cyan-300"></i> Job Overview
                            </h3>

                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between items-center py-2 border-b border-white/10">
                                    <span class="text-blue-200">Category</span>
                                    <span class="font-semibold text-white">{{ $job->jobCategory->name ?? $job->category ?? 'General' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-white/10">
                                    <span class="text-blue-200">Experience</span>
                                    <span class="font-semibold text-white">{{ $job->experienceLevel->name ?? $job->experience_required ?? 'Any' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-white/10">
                                    <span class="text-blue-200">Education</span>
                                    <span class="font-semibold text-white">{{ $job->educationLevel->name ?? $job->education_level ?? 'Any' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-white/10">
                                    <span class="text-blue-200">Posted On</span>
                                    <span class="font-semibold text-white">{{ $job->created_at->format('M d, Y') }}</span>
                                </div>

                                @if($job->application_deadline)
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-blue-200">Deadline</span>
                                        <span class="text-rose-200 font-bold">
                                            {{ \Carbon\Carbon::parse($job->application_deadline)->format('M d, Y') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(!empty($job->job_type_tags))
                            <div class="fx-card bg-white/5 border border-white/10 p-4 rounded-2xl">
                                <h4 class="text-xs font-bold text-cyan-300 uppercase mb-3">Tags</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($job->job_type_tags as $tag)
                                        <span class="text-xs bg-slate-800 text-slate-200 px-2 py-1 rounded border border-white/10">
                                            #{{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($job->company_website)
                            <a href="{{ $job->company_website }}" target="_blank" class="fx-btn block w-full text-center border-2 border-white/20 text-white font-bold py-3 rounded-xl hover:bg-white/10 hover:text-cyan-300 transition">
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
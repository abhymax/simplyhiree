@extends('layouts.app')

@section('content')
<style>
    .fx-card {
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, background-color .22s ease;
    }
    .fx-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 34px rgba(14,165,233,.22);
        border-color: rgba(255,255,255,.30);
    }
    .fx-row {
        transition: all .22s ease;
        border-left: 4px solid transparent;
    }
    .fx-row:hover {
        transform: scale(1.003);
        background: rgba(255,255,255,.10) !important;
        border-left-color: #22d3ee;
    }
    .fx-btn {
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .fx-btn:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 24px rgba(59,130,246,.35);
        filter: brightness(1.04);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[24rem] h-[24rem] bg-indigo-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="text-center mb-10 border-b border-white/15 pb-8">
            <span class="inline-flex px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                Candidate Workspace
            </span>
            <h1 class="mt-3 text-4xl md:text-5xl font-extrabold tracking-tight text-white">Find Your Next Opportunity</h1>
            <p class="mt-3 text-lg text-blue-100">Browse verified jobs from top companies and agencies.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 sticky top-6 shadow-2xl">
                    <h2 class="text-lg font-bold text-white mb-4">Filter Jobs</h2>

                    <form action="{{ route('jobs.index') }}" method="GET">

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-cyan-200 mb-1">Keywords</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Job title, skill, or company"
                                   class="w-full rounded-xl border border-blue-500/30 bg-slate-800 text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-cyan-200 mb-1">Location</label>
                            <select name="location" class="w-full rounded-xl border border-blue-500/30 bg-slate-800 text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                                <option value="" class="text-slate-900">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}" class="text-slate-900" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-cyan-200 mb-1">Category</label>
                            <select name="category_id" class="w-full rounded-xl border border-blue-500/30 bg-slate-800 text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                                <option value="" class="text-slate-900">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" class="text-slate-900" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-cyan-200 mb-1">Job Type</label>
                            <select name="job_type" class="w-full rounded-xl border border-blue-500/30 bg-slate-800 text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                                <option value="" class="text-slate-900">All Types</option>
                                @foreach($jobTypes as $type)
                                    <option value="{{ $type }}" class="text-slate-900" {{ request('job_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-cyan-200 mb-1">Experience</label>
                            <select name="experience_level_id" class="w-full rounded-xl border border-blue-500/30 bg-slate-800 text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                                <option value="" class="text-slate-900">Any Experience</option>
                                @foreach($experienceLevels as $level)
                                    <option value="{{ $level->id }}" class="text-slate-900" {{ request('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="submit" class="fx-btn w-full bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-2.5 px-4 rounded-xl">
                                <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                            </button>
                            @if(request()->anyFilled(['search', 'location', 'category_id', 'job_type', 'experience_level_id']))
                                <a href="{{ route('jobs.index') }}" class="fx-btn w-full text-center bg-rose-500 hover:bg-rose-400 text-white font-bold py-2.5 px-4 rounded-xl">
                                    Clear All
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-3 space-y-5">
                @forelse($jobs as $job)
                    <div class="fx-row bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-36 h-36 bg-indigo-500/20 rounded-full blur-2xl"></div>

                        <div class="relative z-10 flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <h3 class="text-2xl font-extrabold text-white">
                                    <a href="{{ route('jobs.show', $job->id) }}" class="hover:text-cyan-300 transition">
                                        {{ $job->title }}
                                    </a>
                                </h3>
                                <p class="text-amber-300 font-bold mt-1">{{ $job->company_name }}</p>

                                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-blue-100">
                                    <span class="flex items-center"><i class="fa-solid fa-location-dot mr-1.5 text-cyan-300"></i> {{ $job->location }}</span>
                                    <span class="flex items-center"><i class="fa-solid fa-briefcase mr-1.5 text-cyan-300"></i> {{ $job->job_type }}</span>
                                    <span class="flex items-center"><i class="fa-solid fa-money-bill mr-1.5 text-cyan-300"></i> {{ $job->salary ?? 'Not Disclosed' }}</span>
                                    <span class="flex items-center"><i class="fa-regular fa-clock mr-1.5 text-cyan-300"></i> Posted {{ $job->created_at->diffForHumans() }}</span>
                                </div>

                                @if($job->skills_required)
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach(array_slice(explode(',', $job->skills_required), 0, 4) as $skill)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-100 border border-indigo-400/30">
                                                {{ trim($skill) }}
                                            </span>
                                        @endforeach
                                        @if(count(explode(',', $job->skills_required)) > 4)
                                            <span class="text-xs text-blue-200 self-center">+ more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="hidden sm:block shrink-0">
                                <a href="{{ route('jobs.show', $job->id) }}" class="fx-btn inline-flex items-center px-4 py-2.5 rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 font-bold text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-14 text-center">
                        <div class="mx-auto h-12 w-12 text-blue-200">
                            <i class="fa-solid fa-search fa-2x"></i>
                        </div>
                        <h3 class="mt-3 text-xl font-bold text-white">No jobs found</h3>
                        <p class="mt-2 text-blue-100">Try adjusting your search or filters.</p>
                        <div class="mt-6">
                            <a href="{{ route('jobs.index') }}" class="text-cyan-300 hover:text-white font-bold">Clear all filters</a>
                        </div>
                    </div>
                @endforelse

                <div class="mt-6 bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-4">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
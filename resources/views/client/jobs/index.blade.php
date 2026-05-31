@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Client Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">My Job Postings</h1>
                <p class="text-blue-200 mt-2 text-lg">Manage all your requirements and track their status.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="{{ route('client.jobs.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg hover:shadow-blue-500/50">
                    <i class="fa-solid fa-plus"></i> Post New Job
                </a>
            </div>
        </div>

        {{-- Status Pills --}}
        <div class="flex flex-wrap items-center gap-3 mb-8 bg-white/5 backdrop-blur-md p-2 rounded-2xl border border-white/10">
            <a href="{{ route('client.jobs.index') }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ !request('status') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                All <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ !request('status') ? 'bg-blue-400/40 text-white border border-blue-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', ['status' => 'approved']) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Active <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'approved' ? 'bg-emerald-400/40 text-white border border-emerald-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['approved'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', ['status' => 'pending']) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Pending Approval <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'pending' ? 'bg-amber-700/20 text-slate-900 border border-slate-900/10' : 'bg-white/10 text-blue-200' }}">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', ['status' => 'hold']) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'hold' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                On Hold <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'hold' ? 'bg-orange-400/40 text-white border border-orange-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['hold'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', ['status' => 'closed']) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'closed' ? 'bg-rose-600 text-white shadow-lg shadow-rose-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Closed <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'closed' ? 'bg-rose-400/40 text-white border border-rose-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['closed'] }}</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 px-5 py-3 rounded-2xl flex items-start gap-3 shadow-lg"
                 style="background: rgba(16,185,129,0.15); border: 1px solid rgba(52,211,153,0.4); color: #d1fae5;"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false, 6000)" x-transition>
                <i class="fa-solid fa-circle-check text-emerald-300 text-xl mt-0.5"></i>
                <div class="flex-1 font-semibold">{{ session('success') }}</div>
                <button type="button" @click="show=false" class="text-emerald-200 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- Job List --}}
        @if($jobs->isEmpty())
            <div class="bg-white/5 backdrop-blur-md rounded-3xl shadow-xl border border-white/10 p-16 text-center">
                <div class="w-24 h-24 bg-blue-500/10 border border-blue-400/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-regular fa-folder-open text-5xl text-blue-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">No jobs found</h3>
                <p class="text-blue-200 text-sm max-w-md mx-auto mb-8">You don't have any job postings matching the current criteria. Start by posting a new requirement.</p>
                @if(!request('status'))
                    <a href="{{ route('client.jobs.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-extrabold rounded-xl shadow-lg transition-colors gap-2">
                        <i class="fa-solid fa-plus"></i> Post Your First Job
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($jobs as $job)
                    <div class="bg-white/5 backdrop-blur-md rounded-2xl shadow-xl border border-white/10 overflow-hidden flex flex-col hover:bg-white/10 transition duration-300 relative group">
                        
                        {{-- Top Border color based on status --}}
                        <div class="absolute top-0 left-0 w-full h-1.5
                            @if($job->status === 'approved') bg-emerald-500
                            @elseif($job->status === 'pending_approval') bg-amber-400
                            @elseif($job->status === 'on_hold') bg-orange-500
                            @elseif($job->status === 'closed') bg-rose-500
                            @else bg-slate-400 @endif
                        "></div>

                        <div class="p-6 flex-1 pt-8">
                            <div class="flex justify-between items-start mb-4 gap-3">
                                <h3 class="text-xl font-bold text-white line-clamp-2 leading-tight" title="{{ $job->title }}">{{ $job->title }}</h3>
                                
                                <div class="shrink-0">
                                    @if($job->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            <i class="fa-solid fa-check mr-1"></i> Active
                                        </span>
                                    @elseif($job->status === 'pending_approval')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            <i class="fa-regular fa-clock mr-1"></i> Pending
                                        </span>
                                    @elseif($job->status === 'on_hold')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-orange-500/20 text-orange-300 border border-orange-500/30">
                                            <i class="fa-solid fa-pause mr-1"></i> On Hold
                                        </span>
                                    @elseif($job->status === 'closed')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            <i class="fa-solid fa-xmark mr-1"></i> Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-slate-500/20 text-slate-300 border border-slate-500/30">
                                            <i class="fa-solid fa-circle-info mr-1"></i> {{ ucwords(str_replace('_', ' ', $job->status)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-3 mt-5 text-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 flex justify-center text-blue-400 mt-0.5"><i class="fa-solid fa-location-dot"></i></div>
                                    <span class="truncate text-blue-100">{{ $job->location ?: 'Location not specified' }}</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-5 flex justify-center text-blue-400 mt-0.5"><i class="fa-solid fa-briefcase"></i></div>
                                    <span class="text-blue-100">{{ $job->formatted_experience ?? (($job->min_experience ?? '') . '-' . ($job->max_experience ?? '') . ' yrs') }}</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-5 flex justify-center text-blue-400 mt-0.5"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                                    <span class="text-blue-100">{{ $job->salary ?: 'Salary unlisted' }}</span>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="w-5 flex justify-center text-emerald-400 mt-0.5"><i class="fa-solid fa-users"></i></div>
                                    <span class="font-bold text-emerald-300">{{ $job->job_applications_count }} Applications</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-900/30 border-t border-white/5 p-4 flex items-center justify-between gap-3">
                            <a href="{{ route('client.jobs.applicants', $job->id) }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 shadow-sm text-sm font-bold rounded-xl text-white transition-colors gap-2">
                                <i class="fa-regular fa-eye"></i> View Candidates
                            </a>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="p-2.5 text-blue-200 hover:text-white rounded-xl hover:bg-white/10 transition border border-transparent">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                
                                <div x-show="open" x-transition style="display: none;" class="absolute bottom-full right-0 mb-2 w-48 rounded-2xl shadow-xl bg-slate-800 ring-1 ring-white/10 divide-y divide-white/5 overflow-hidden z-20">
                                    <div class="py-1">
                                        <a href="{{ route('client.jobs.edit', $job->id) }}" class="group flex items-center px-4 py-2.5 text-sm text-slate-200 hover:bg-white/5 hover:text-white">
                                            <i class="fa-solid fa-pen-to-square w-5 text-slate-400 group-hover:text-blue-400"></i> Edit Job
                                        </a>
                                        @if($job->status === 'approved')
                                            <form action="{{ route('client.jobs.request-deactivation', $job->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full text-left group flex items-center px-4 py-2.5 text-sm text-slate-200 hover:bg-rose-500/10 hover:text-rose-400 transition">
                                                    <i class="fa-solid fa-ban w-5 text-slate-400 group-hover:text-rose-400"></i> Request Closure
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @endif

    </div>
@endsection

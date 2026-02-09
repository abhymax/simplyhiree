@extends('layouts.app')

@section('content')
<style>
    .fx-row { transition: all .22s ease; border-left: 4px solid transparent; }
    .fx-row:hover { transform: scale(1.004); background: rgba(255,255,255,.10) !important; border-left-color: #22d3ee; }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[24rem] h-[24rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <span class="text-cyan-300 text-xs font-bold uppercase tracking-wider">Partner Workspace</span>
                <h1 class="text-4xl font-extrabold tracking-tight mt-2">Browse Jobs</h1>
                <p class="text-blue-100 mt-1">Find high-payout approved roles and apply faster.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-blue-600 border border-blue-400 rounded-2xl px-5 py-3 shadow-xl">
                <div class="text-blue-100 text-xs font-bold uppercase">Visible Jobs</div>
                <div class="text-3xl font-black">{{ $jobs->total() }}</div>
            </div>
        </div>

        <form action="{{ route('partner.jobs') }}" method="GET" class="mb-6">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <input type="text" name="search" placeholder="Search title, skills, company..." value="{{ request('search') }}"
                           class="md:col-span-2 rounded-xl border border-blue-500/30 bg-slate-800 text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">
                    <select name="location" onchange="this.form.submit()" class="rounded-xl border border-blue-500/30 bg-slate-800 text-white">
                        <option value="" class="text-slate-900">Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" class="text-slate-900" {{ request('location') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                    <select name="job_type" onchange="this.form.submit()" class="rounded-xl border border-blue-500/30 bg-slate-800 text-white">
                        <option value="" class="text-slate-900">Job Type</option>
                        @foreach($job_types as $job_type)
                            <option value="{{ $job_type }}" class="text-slate-900" {{ request('job_type') == $job_type ? 'selected' : '' }}>{{ $job_type }}</option>
                        @endforeach
                    </select>
                    <select name="experience_level_id" onchange="this.form.submit()" class="rounded-xl border border-blue-500/30 bg-slate-800 text-white">
                        <option value="" class="text-slate-900">Experience</option>
                        @foreach($experienceLevels as $level)
                            <option value="{{ $level->id }}" class="text-slate-900" {{ request('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                    <select name="education_level_id" onchange="this.form.submit()" class="rounded-xl border border-blue-500/30 bg-slate-800 text-white">
                        <option value="" class="text-slate-900">Education</option>
                        @foreach($educationLevels as $level)
                            <option value="{{ $level->id }}" class="text-slate-900" {{ request('education_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit" class="fx-btn bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black px-5 py-2 rounded-xl">
                        <i class="fa-solid fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('partner.jobs') }}" class="fx-btn bg-rose-500 hover:bg-rose-400 text-white font-bold px-4 py-2 rounded-xl">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-5">Job Designation</th>
                            <th class="px-6 py-5">Requirements</th>
                            <th class="px-6 py-5">Applications</th>
                            <th class="px-6 py-5">Payout & Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($jobs as $job)
                            <tr class="fx-row">
                                <td class="px-6 py-5 align-top">
                                    <a href="{{ route('partner.jobs.show', $job->id) }}" class="text-white font-bold text-lg hover:text-cyan-300 transition">
                                        {{ $job->title }}
                                        @if($job->is_walkin)
                                            <span class="ml-2 bg-cyan-500/20 text-cyan-100 text-xs font-bold px-2 py-1 rounded border border-cyan-400/30">Walkin</span>
                                        @endif
                                    </a>
                                    <div class="text-amber-300 text-sm font-bold mt-1">{{ $job->company_name }}</div>
                                    <div class="text-blue-200 text-xs">{{ $job->location }} | {{ $job->category->name ?? 'N/A' }}</div>
                                    <div class="text-slate-300 text-xs mt-1">Posted: {{ $job->created_at->format('d M, Y') }}</div>
                                </td>

                                <td class="px-6 py-5 align-top text-blue-100 text-sm">
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Openings:</span> {{ $job->openings ?? 'N/A' }}</div>
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Exp:</span> {{ $job->experienceLevel->name ?? 'N/A' }}</div>
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Edu:</span> {{ $job->educationLevel->name ?? 'N/A' }}</div>
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Salary:</span> {{ $job->salary ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    @foreach(['applied', 'screened', 'turned_up', 'selected', 'joined'] as $status)
                                        <div class="flex items-center mb-2">
                                            <span class="text-xs text-blue-200 w-24 uppercase">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                            <div class="w-full bg-slate-800 rounded-full h-2.5 mx-2 border border-white/10">
                                                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-2.5 rounded-full" style="width: {{ $job->stats->applied > 0 ? ($job->stats->$status / $job->stats->applied) * 100 : 0 }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-white w-6 text-right">{{ $job->stats->$status }}</span>
                                        </div>
                                    @endforeach
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <div class="text-xs uppercase text-blue-200 font-bold">Payout</div>
                                    <div class="text-3xl font-black text-emerald-300">₹{{ number_format($job->payout_amount, 0) }}</div>
                                    <div class="text-xs text-slate-300 mb-3">after {{ $job->minimum_stay_days ?? 0 }} days</div>

                                    <div class="space-y-2">
                                        <a href="{{ route('partner.jobs.show', $job->id) }}" class="fx-btn block text-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm">View Details</a>
                                        <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="fx-btn block text-center px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black text-sm">Apply Now</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-14 px-6 text-center text-blue-100">
                                    <i class="fa-solid fa-folder-open text-5xl text-blue-200 mb-3"></i>
                                    <p class="font-bold text-white">No approved jobs match your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/10 bg-slate-900/80">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
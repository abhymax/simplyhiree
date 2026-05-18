@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">

    {{-- DECORATIVE BACKGROUND GLOWS --}}
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Client Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Overview</h1>
                <p class="text-blue-200 mt-2 text-lg">Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg shadow-lg">
                        <i class="fa-regular fa-calendar text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-300 font-bold uppercase">Today's Date</p>
                        <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 1: DAILY PULSE --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            {{-- Interviews Card --}}
            <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden group">
                    <div class="absolute right-0 top-0 p-6 opacity-10 group-hover:opacity-20 transition transform group-hover:scale-110 duration-500">
                        <i class="fa-solid fa-users-viewfinder text-9xl text-white"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-video"></i></span>
                            <h3 class="font-bold text-xl text-white">Interviews Today</h3>
                        </div>

                        <div class="flex items-baseline gap-4">
                            <span class="text-6xl font-black text-white tracking-tighter">{{ $todayInterviews ?? 0 }}</span>
                            <span class="text-blue-200 font-medium">Scheduled</span>
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('client.interviews.today') }}" class="inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg hover:shadow-white/20">
                                {{ ($todayInterviews ?? 0) > 0 ? 'View Schedule' : 'Open Interview Board' }} <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financials Card --}}
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 relative overflow-hidden hover:bg-white/15 transition duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                    </div>
                    @if(($dueInvoicesCount ?? 0) > 0)
                        <span class="animate-pulse bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">Action Needed</span>
                    @else
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs font-bold px-3 py-1 rounded-full">All Clear</span>
                    @endif
                </div>

                <div>
                    <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Invoices Due</p>
                    <p class="text-5xl font-extrabold text-white mt-2 mb-1">{{ $dueInvoicesCount ?? 0 }}</p>
                    <p class="text-slate-400 text-sm">Pending payments</p>
                </div>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('client.billing') }}" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                        <span>View Billing</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- SECTION 2: QUICK ACTIONS --}}
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-12">
            {{-- 1. Post Job --}}
            <a href="{{ route('client.jobs.create') }}" class="group bg-blue-600 rounded-2xl p-5 text-white shadow-lg hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fa-solid fa-plus text-5xl"></i></div>
                <div class="relative z-10">
                    <div class="h-10 w-10 bg-white/20 rounded-lg flex items-center justify-center mb-3 backdrop-blur-sm">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <h4 class="font-bold text-lg">Post Job</h4>
                    <p class="text-blue-200 text-xs">Create vacancy</p>
                </div>
            </a>

            {{-- 2. Interviews --}}
            <a href="{{ route('client.interviews.today') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-video"></i>
                </div>
                <h4 class="font-bold text-white">Interviews</h4>
                <p class="text-slate-400 text-xs">Today's schedule</p>
            </a>

            {{-- 3. Vendors --}}
            <a href="{{ route('client.vendors.browse') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <h4 class="font-bold text-white">Vendors</h4>
                <p class="text-slate-400 text-xs">Browse partners</p>
            </a>

            {{-- 4. Billing --}}
            <a href="{{ route('client.billing') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <h4 class="font-bold text-white">Billing</h4>
                <p class="text-slate-400 text-xs">Invoices &amp; payments</p>
            </a>

            {{-- 5. Profile --}}
            <a href="{{ route('client.profile.company') }}" class="group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-teal-500/20 text-teal-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-building"></i>
                </div>
                <h4 class="font-bold text-white">Company</h4>
                <p class="text-slate-400 text-xs">Edit profile</p>
            </a>
        </div>

        {{-- SECTION 3: LIVE METRICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-12">
            <a href="#my-jobs" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase">Active Jobs</span>
                    <i class="fa-solid fa-briefcase text-blue-400"></i>
                </div>
                <div class="text-2xl font-extrabold text-white">{{ $activeJobs ?? 0 }}</div>
            </a>

            <a href="#my-jobs" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase">Total Jobs</span>
                    <i class="fa-solid fa-folder-open text-amber-400"></i>
                </div>
                <div class="text-2xl font-extrabold text-white">{{ $totalJobs ?? 0 }}</div>
            </a>

            <a href="#my-jobs" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase">Applicants</span>
                    <i class="fa-solid fa-users text-emerald-400"></i>
                </div>
                <div class="text-2xl font-extrabold text-white">{{ $totalApplicants ?? 0 }}</div>
            </a>

            <a href="#my-jobs" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase">Total Hires</span>
                    <i class="fa-solid fa-user-check text-purple-400"></i>
                </div>
                <div class="text-2xl font-extrabold text-white">{{ $totalHires ?? 0 }}</div>
            </a>

            <a href="{{ route('client.billing') }}" class="bg-white/5 backdrop-blur-md border border-white/5 rounded-2xl p-4 hover:bg-white/10 transition-all">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase">Invoices Due</span>
                    <i class="fa-solid fa-file-invoice-dollar text-rose-400"></i>
                </div>
                <div class="text-2xl font-extrabold text-white">{{ $dueInvoicesCount ?? 0 }}</div>
            </a>
        </div>

        {{-- SECTION 4: MY JOB POSTINGS --}}
        <div id="my-jobs" class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-white/10 flex flex-col md:flex-row justify-between gap-3 md:items-center">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-3">
                        <span class="w-1.5 h-7 bg-blue-500 rounded-full"></span> My Job Postings
                    </h3>
                    <p class="text-slate-400 text-sm mt-1 ml-5">Total: <span class="text-white font-bold">{{ $totalJobs ?? 0 }}</span> · Active: <span class="text-emerald-400 font-bold">{{ $activeJobs ?? 0 }}</span></p>
                </div>
                <a href="{{ route('client.jobs.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg">
                    <i class="fa-solid fa-plus"></i> Post New Job
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-900/50 text-blue-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Designation / Role</th>
                            <th class="px-6 py-4">Requirements</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Applicants</th>
                            <th class="px-6 py-4">Posted On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($jobs as $job)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-white hover:text-blue-300 transition">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-xs text-slate-400 mt-1">{{ $job->location }} · {{ $job->job_type }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    <div><span class="text-blue-300 text-[10px] uppercase font-bold">Openings:</span> {{ $job->openings ?? 'N/A' }}</div>
                                    <div><span class="text-blue-300 text-[10px] uppercase font-bold">Exp:</span> {{ $job->formatted_experience }}</div>
                                    <div><span class="text-blue-300 text-[10px] uppercase font-bold">Gender:</span> {{ $job->gender_preference ?? 'Any' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusMap = [
                                            'approved' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                                            'pending_approval' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                                            'on_hold' => 'bg-orange-500/20 text-orange-300 border-orange-500/40',
                                            'closed' => 'bg-slate-500/20 text-slate-300 border-slate-500/40',
                                            'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusMap[$job->status] ?? 'bg-blue-500/20 text-blue-300 border-blue-500/40' }}">
                                        {{ str_replace('_', ' ', ucfirst($job->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($job->status == 'approved')
                                        <div class="flex flex-col gap-2 items-start">
                                            <a href="{{ route('client.jobs.applicants', $job) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 shadow-md px-3 py-2 rounded-lg font-bold text-xs text-white">
                                                View Applicants ({{ $job->jobApplications->where('status', 'Approved')->count() }})
                                            </a>
                                            @if($job->deactivation_requested_at)
                                                <span class="inline-flex items-center gap-1.5 bg-amber-500/20 border border-amber-400/40 text-amber-300 px-2.5 py-1 rounded-md text-[11px] font-semibold">
                                                    <i class="fa-solid fa-hourglass-half"></i> Deactivation Requested
                                                </span>
                                                <form method="POST" action="{{ route('client.jobs.cancel-deactivation', $job) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-[11px] text-slate-300 hover:text-white underline">Cancel request</button>
                                                </form>
                                            @else
                                                <button type="button" onclick="document.getElementById('deact-{{ $job->id }}').classList.toggle('hidden')"
                                                    class="text-[11px] text-rose-300 hover:text-rose-200 underline">
                                                    Request Deactivation
                                                </button>
                                                <form id="deact-{{ $job->id }}" method="POST" action="{{ route('client.jobs.request-deactivation', $job) }}"
                                                      class="hidden mt-1 flex flex-col gap-2 w-64 bg-slate-900/60 border border-rose-400/30 p-3 rounded-lg">
                                                    @csrf
                                                    <textarea name="reason" rows="2" maxlength="1000" placeholder="Reason (optional)"
                                                        class="w-full text-xs bg-slate-900 border border-white/20 rounded p-2 text-white"></textarea>
                                                    <div class="flex gap-2">
                                                        <button type="submit" class="bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold px-3 py-1.5 rounded">Submit</button>
                                                        <button type="button" onclick="document.getElementById('deact-{{ $job->id }}').classList.add('hidden')" class="text-xs text-slate-300 hover:text-white">Cancel</button>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif($job->status === 'pending_approval')
                                        <a href="{{ route('client.jobs.edit', $job) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 shadow-md px-3 py-2 rounded-lg font-bold text-xs text-slate-900">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit Pending Job
                                        </a>
                                    @else
                                        <span class="text-slate-500 text-xs italic">Not available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-300">{{ $job->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                        <i class="fa-regular fa-folder-open text-5xl text-blue-300"></i>
                                    </div>
                                    <p class="font-bold text-white text-lg">No jobs posted yet</p>
                                    <a href="{{ route('client.jobs.create') }}" class="text-blue-300 hover:text-white underline mt-2 inline-block">Post your first job</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

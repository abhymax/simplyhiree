<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-6xl mx-auto">

            @if(session('info'))
                <div class="mb-6 px-5 py-3 bg-blue-500/20 border border-blue-400/40 text-blue-100 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-6 px-5 py-3 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 px-5 py-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            {{-- HEADER / BREADCRUMB --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end gap-4">
                <div>
                    <a href="{{ $job->status === 'pending_approval' ? route('admin.jobs.pending') : route('admin.reports.jobs') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-3 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> {{ $job->status === 'pending_approval' ? 'Back to Pending Queue' : 'Back to Master Job Report' }}
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">{{ $job->status === 'approved' ? 'Live Job Control' : 'Job Review' }}</h1>
                    <p class="text-blue-200 mt-1 text-lg">{{ $job->status === 'approved' ? 'Edit details, commercials, and vendor access without interrupting applications.' : 'Review posting details before going live.' }}</p>
                </div>

                {{-- STATUS BADGE --}}
                <div>
                    <a href="{{ route('admin.jobs.edit', $job) }}" class="mb-3 inline-flex items-center gap-2 rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-4 py-2 text-sm font-bold text-cyan-100 transition hover:-translate-y-0.5 hover:bg-cyan-500/25">
                        <i class="fa-solid fa-pen-to-square"></i> {{ $job->status === 'approved' ? 'Edit Live Job' : 'Edit Job & Commercials' }}
                    </a>
                    @if($job->status === 'approved')
                        <span class="px-5 py-2 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-sm font-extrabold shadow-lg flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> LIVE / APPROVED
                        </span>
                    @elseif($job->status === 'pending_approval')
                        <span class="px-5 py-2 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/50 text-sm font-extrabold shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-clock"></i> PENDING REVIEW
                        </span>
                    @else
                        <span class="px-5 py-2 rounded-full bg-slate-700 text-slate-300 border border-slate-500 text-sm font-extrabold flex items-center gap-2">
                            {{ strtoupper($job->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: JOB DETAILS --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    {{-- 1. MAIN INFO CARD --}}
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-6 opacity-10">
                            <i class="fa-solid fa-briefcase text-8xl text-white"></i>
                        </div>

                        <div class="relative z-10">
                            <h2 class="text-3xl font-bold text-white leading-tight">{{ $job->title }}</h2>
                            <div class="flex items-center gap-2 mt-2 text-amber-300 font-bold text-lg" style="color: #fcd34d !important;">
                                <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                            </div>

                            {{-- META GRID --}}
                            <div class="grid grid-cols-2 gap-4 mt-8">
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Category</span>
                                    <span class="text-white font-medium">{{ $job->category->name ?? 'General' }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Type</span>
                                    <span class="text-white font-medium">{{ $job->job_type }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Location</span>
                                    <span class="text-white font-medium"><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i> {{ $job->location }}</span>
                                </div>
                                <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                                    <span class="block text-xs font-bold text-cyan-300 uppercase mb-1">Salary</span>
                                    <span class="text-white font-medium">{{ $job->salary ?? 'Not Disclosed' }}</span>
                                </div>
                            </div>

                            {{-- DESCRIPTION --}}
                            <div class="mt-8">
                                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-align-left text-blue-400"></i> Job Description
                                </h3>
                                <div class="text-blue-100 leading-relaxed text-sm bg-slate-900/30 p-6 rounded-2xl border border-white/5 job-desc-html">
                                    {!! $job->formatted_description !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. REQUIREMENTS CARD --}}
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-purple-400"></i> Requirements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Experience Range</span>
                                <div class="text-white font-bold text-lg flex items-center gap-2">
                                    <span class="bg-white/10 px-3 py-1 rounded-lg">{{ $job->min_experience ?? 0 }}</span>
                                    <span>-</span>
                                    <span class="bg-white/10 px-3 py-1 rounded-lg">{{ $job->max_experience ?? 'N/A' }}</span>
                                    <span class="text-sm font-normal text-slate-400">Years</span>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Education Level</span>
                                <div class="text-white font-bold text-lg">{{ $job->educationLevel->name ?? 'Any' }}</div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Gender Preference</span>
                                <div class="text-white font-bold text-lg">{{ $job->gender_preference ?? 'Any' }}</div>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Age Range</span>
                                <div class="text-white font-bold text-lg">
                                    @if($job->min_age || $job->max_age)
                                        {{ $job->min_age ?? '—' }} - {{ $job->max_age ?? '—' }} Yrs
                                    @else
                                        <span class="text-slate-400 font-normal italic text-base">Not specified</span>
                                    @endif
                                </div>
                            </div>
                            @php
                                $extraDetails = array_filter([
                                    'Work Mode' => $job->work_mode,
                                    'Shift' => $job->shift,
                                    'Specialization' => $job->specialization,
                                    'Notice Period' => $job->notice_period,
                                    'Languages' => $job->languages,
                                    'Industry' => $job->industry,
                                    'Department' => $job->department,
                                    'Reporting Manager' => $job->reporting_manager,
                                ], fn ($v) => filled($v));
                                $jobBenefitsList = is_array($job->benefits) ? $job->benefits : [];
                            @endphp
                            @foreach($extraDetails as $label => $val)
                                <div>
                                    <span class="block text-xs font-bold text-slate-400 uppercase mb-1">{{ $label }}</span>
                                    <div class="text-white font-bold text-lg">{{ $val }}</div>
                                </div>
                            @endforeach
                            @if(!is_null($job->travel_required))
                                <div>
                                    <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Travel Required</span>
                                    <div class="text-white font-bold text-lg">{{ $job->travel_required ? 'Yes' : 'No' }}</div>
                                </div>
                            @endif
                            @if(count($jobBenefitsList))
                                <div class="md:col-span-2">
                                    <span class="block text-xs font-bold text-slate-400 uppercase mb-2">Benefits</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($jobBenefitsList as $benefit)
                                            <span class="inline-flex items-center rounded-lg bg-white/10 px-3 py-1 text-sm font-semibold text-white">{{ $benefit }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: ACTIONS --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- CLIENT COMMERCIAL + FINAL PARTNER PAYOUT --}}
                    <div class="bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-md border border-amber-500/30 rounded-3xl p-6 shadow-lg">
                        <h3 class="text-amber-300 font-bold text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-coins"></i> Commercials
                        </h3>
                        <div class="space-y-4">
                            <div class="border-b border-amber-500/20 pb-4">
                                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-amber-300">Client-submitted commercial</span>
                                @if($job->commercial_source === 'manual')
                                    <span class="mt-1 block text-xl font-black text-white">
                                        @if($job->fee_type === 'percentage')
                                            {{ rtrim(rtrim(number_format((float) $job->fee_amount, 2), '0'), '.') }}%
                                        @else
                                            ₹{{ number_format((float) $job->fee_amount, 2) }} flat
                                        @endif
                                    </span>
                                    <span class="mt-1 block text-xs text-amber-100/75">
                                        Manual override &middot;
                                        {{ $job->client_payout_days ?? $job->minimum_stay_days ?? '—' }}-day maturity &middot;
                                        {{ $job->replacement_period_days ?? $job->replacement_guarantee_days ?? '—' }}-day replacement
                                    </span>
                                @else
                                    <span class="mt-1 block text-base font-bold text-cyan-100">SimplyHire client agreement</span>
                                    <span class="mt-1 block text-xs text-blue-200/75">The client account-level commercial applies.</span>
                                @endif
                            </div>
                            <div>
                                <span class="block text-[10px] font-extrabold uppercase tracking-wider text-emerald-300">Admin-approved partner payout</span>
                                @if($job->payout_amount !== null)
                                    <div class="mt-1 flex justify-between items-end gap-4">
                                        <span class="text-2xl font-black text-white">₹{{ number_format((float) $job->payout_amount, 2) }}</span>
                                        <span class="text-sm font-bold text-white">{{ $job->minimum_stay_days ?? '—' }} payout days</span>
                                    </div>
                                @else
                                    <span class="mt-1 block text-sm font-bold text-slate-300">Not set yet</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- CLIENT INFO --}}
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-6">
                        <h3 class="text-slate-400 font-bold text-sm uppercase tracking-wider mb-4">Posted By</h3>
                        @php $poster = $job->user; @endphp
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($poster->name ?? ($job->company_name ?: 'S'), 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-white font-bold">{{ $poster->name ?? ($job->company_name ?: 'SimplyHiree') }}</div>
                                <div class="text-xs text-blue-300">{{ $poster->email ?? 'SimplyHiree (Internal job)' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl sticky top-24">
                        <h3 class="text-white font-bold text-lg mb-4">Admin Actions</h3>
                        
                        @if($job->status === 'pending_approval')
                            <div class="space-y-3">
                                <form action="{{ route('admin.jobs.approve', $job->id) }}" method="POST" class="space-y-3 bg-amber-500/10 border border-amber-400/30 rounded-2xl p-4">
                                    @csrf
                                    <h4 class="text-amber-200 text-xs font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                        <i class="fa-solid fa-coins"></i> Set Payout Before Approving
                                    </h4>
                                    <div>
                                        <label class="block text-[11px] text-amber-200 font-bold uppercase mb-1">Payout Amount (₹) <span class="text-rose-400">*</span></label>
                                        <input type="number" name="payout_amount" min="0" step="0.01" required
                                            value="{{ old('payout_amount', $job->payout_amount) }}"
                                            placeholder="e.g. 5000"
                                            class="w-full bg-slate-900/80 border border-amber-500/40 rounded-lg text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-11 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-amber-200 font-bold uppercase mb-1">Maturity Period (Days) <span class="text-rose-400">*</span></label>
                                        <input type="number" name="minimum_stay_days" min="1" required
                                            value="{{ old('minimum_stay_days', ($job->client_payout_days ?? $job->minimum_stay_days) ?: 30) }}"
                                            placeholder="e.g. 30"
                                            class="w-full bg-slate-900/80 border border-amber-500/40 rounded-lg text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-11 px-3">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] text-amber-200 font-bold uppercase mb-1">Replacement Guarantee (Days) <span class="text-amber-300/70">(client said {{ $job->replacement_period_days ?? $job->replacement_guarantee_days ?? '—' }})</span></label>
                                        <input type="number" name="replacement_guarantee_days" min="0" max="365"
                                            value="{{ old('replacement_guarantee_days', $job->replacement_period_days ?? $job->replacement_guarantee_days ?? 90) }}"
                                            placeholder="Override client value if needed"
                                            class="w-full bg-slate-900/80 border border-amber-500/40 rounded-lg text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-11 px-3">
                                    </div>
                                    @error('payout_amount') <p class="text-rose-300 text-xs">{{ $message }}</p> @enderror
                                    @error('minimum_stay_days') <p class="text-rose-300 text-xs">{{ $message }}</p> @enderror
                                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-black py-3 rounded-xl font-bold shadow-lg shadow-emerald-500/20 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-check"></i> Approve Job
                                    </button>
                                </form>

                                <form action="{{ route('admin.jobs.reject', $job->id) }}" method="POST" onsubmit="return confirm('Reject this job posting?');">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded-xl font-bold shadow-lg shadow-red-600/30 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-xmark"></i> Reject Job
                                    </button>
                                </form>
                            </div>
                        @elseif($job->status === 'approved')
                            <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="on_hold">
                                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-400 text-black py-3 rounded-xl font-bold shadow-lg transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-pause"></i> Put On Hold
                                </button>
                            </form>
                        @endif

                        @if($job->status !== 'pending_approval' && $job->status !== 'closed')
                            <details class="mt-4 border border-amber-500/30 bg-amber-500/5 rounded-xl p-3 text-left">
                                <summary class="cursor-pointer text-sm font-bold text-amber-300"><i class="fa-solid fa-shield-halved mr-2"></i>Special Controls</summary>
                                <form method="POST" action="{{ route('admin.marketplace.jobs.commission', $job) }}" class="mt-3 space-y-2">@csrf
                                    <label class="block text-xs text-slate-300">Partner payout / commission</label>
                                    <input required name="payout_amount" type="number" min="0" step="0.01" value="{{ $job->payout_amount }}" class="w-full bg-slate-900 border-slate-700 rounded-lg text-sm">
                                    <input required name="reason" maxlength="1000" placeholder="Reason for override" class="w-full bg-slate-900 border-slate-700 rounded-lg text-sm">
                                    <button class="w-full bg-amber-600 hover:bg-amber-500 py-2 rounded-lg text-sm font-bold">Override Commission</button>
                                </form>
                                <form method="POST" action="{{ route('admin.marketplace.jobs.close', $job) }}" class="mt-4 pt-4 border-t border-white/10" onsubmit="return confirm('Force close this job? This action is audited.')">@csrf
                                    <input required name="reason" maxlength="1000" placeholder="Mandatory closure reason" class="w-full bg-slate-900 border-slate-700 rounded-lg text-sm">
                                    <button class="mt-2 w-full bg-rose-700 hover:bg-rose-600 py-2 rounded-lg text-sm font-bold">Force Close Job</button>
                                </form>
                            </details>
                        @endif

                        <div class="mt-4 pt-4 border-t border-white/10">
                            <form action="{{ route('admin.jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Move this job to the Archive? All applications, candidate, and partner data will be preserved.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full text-rose-400 hover:text-rose-200 text-sm font-bold transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-box-archive"></i> Archive Job
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>

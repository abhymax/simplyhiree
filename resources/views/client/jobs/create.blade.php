@extends('layouts.client')

@section('client_content')
<style>
    .post-job-shell { max-width: 1040px; }
    .post-job-surface { background: linear-gradient(145deg, rgba(10, 24, 59, .94), rgba(15, 41, 90, .9)); border: 1px solid rgba(125, 211, 252, .28); box-shadow: 0 28px 60px rgba(2, 6, 23, .35); }
    .post-job-header { background: linear-gradient(110deg, rgba(37, 99, 235, .24), rgba(139, 92, 246, .16), rgba(6, 182, 212, .12)); }
    .job-form-section { border: 1px solid rgba(148, 163, 184, .16); background: rgba(2, 10, 33, .28); border-radius: 18px; padding: 1.35rem; }
    .job-section-title { color: #fff; font-size: .88rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; display: flex; align-items: center; gap: .55rem; margin-bottom: 1.15rem; }
    .job-section-title i { width: 1.9rem; height: 1.9rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .6rem; background: rgba(59, 130, 246, .18); color: #67e8f9; }
    .post-job-shell input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), .post-job-shell select { min-height: 46px; padding: .65rem .8rem; transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
    .post-job-shell input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):focus, .post-job-shell select:focus { border-color: rgba(103, 232, 249, .8) !important; box-shadow: 0 0 0 3px rgba(34, 211, 238, .13); outline: none; }
    .job-flow-card { position: relative; overflow: hidden; border: 1px solid rgba(96, 165, 250, .32); border-radius: 18px; background: linear-gradient(120deg, rgba(30, 64, 175, .3), rgba(15, 23, 42, .48)); }
    .job-flow-option { width: 100%; border: 1px solid rgba(148, 163, 184, .2); border-radius: 14px; background: rgba(2, 6, 23, .38); transition: transform .22s ease, border-color .22s ease, background .22s ease, box-shadow .22s ease; cursor: pointer; text-align: left; }
    .job-flow-option.is-active { transform: translateY(-2px); border-color: rgba(56, 189, 248, .75); background: linear-gradient(135deg, rgba(14, 116, 144, .38), rgba(30, 64, 175, .26)); box-shadow: 0 14px 26px rgba(8, 47, 73, .3); }
    .job-flow-option.is-muted { opacity: .5; }
    #job-flow-badge { align-self: flex-start; height: auto; line-height: 1; white-space: nowrap; }
    .job-submit-btn { position: relative; overflow: hidden; transition: transform .22s ease, box-shadow .22s ease; }
    .job-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 26px rgba(37, 99, 235, .35); }
    .job-submit-btn::after { content: ''; position: absolute; inset: 0 auto 0 -45%; width: 28%; transform: skewX(-20deg); background: linear-gradient(90deg, transparent, rgba(255,255,255,.36), transparent); }
    .job-submit-btn:hover::after { animation: job-button-sweep .75s ease-out; }
    @keyframes job-button-sweep { to { left: 125%; } }
    @media (prefers-reduced-motion: reduce) { .job-flow-option, .job-submit-btn { transition: none; } .job-submit-btn:hover::after { animation: none; } }
</style>
@php
    $isEditMode = ($formMode ?? 'create') === 'edit' && isset($job) && $job;
    $formAction = $isEditMode ? route('client.jobs.update', $job) : route('client.jobs.store');
    $formTitle = $isEditMode ? 'Edit Pending Job' : 'Post a New Job';
    $submitLabel = $isEditMode ? 'Save Changes' : 'Post Job';
    $salaryDigits = collect(preg_split('/\D+/', (string) ($job->salary ?? ''), -1, PREG_SPLIT_NO_EMPTY))
        ->map(fn ($value) => (int) $value)
        ->values();
    $existingMinSalary = $salaryDigits->get(0);
    $existingMaxSalary = $salaryDigits->count() > 1 ? $salaryDigits->get(1) : $salaryDigits->get(0);

    $jobTypeOptions = ['Full-time','Part-time','Contract','Contract-to-Hire (C2H)','Internship','Freelance','Temporary','Permanent','Trainee','Consultant'];
    $workModeOptions = ['On-site','Hybrid','Remote','Field Job','Work From Home (WFH)'];
    $shiftOptions = ['Day Shift','Night Shift','Rotational Shift','Flexible Shift','Weekend Shift'];
    $benefitOptions = ['PF','ESIC','Insurance','Food','Transport','Accommodation','Laptop','Mobile','Joining Bonus','Relocation'];
    $jobBenefits = old('benefits', $job->benefits ?? []);
    if (!is_array($jobBenefits)) { $jobBenefits = []; }
    $travelRequiredVal = old('travel_required', isset($job->travel_required) ? (int) $job->travel_required : '');
    $travelRequiredVal = ($travelRequiredVal === '' || $travelRequiredVal === null) ? '' : (string) $travelRequiredVal;
@endphp

    <div class="post-job-shell relative z-10 mx-auto">

        @if ($errors->any())
            <div class="mb-6 bg-rose-500/20 border border-rose-400/40 text-rose-100 p-4 rounded-xl">
                <p class="font-bold">Please fix the following errors:</p>
                <ul class="list-disc ml-5 mt-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="post-job-surface rounded-3xl overflow-hidden">
            <div class="post-job-header p-6 md:p-8 border-b border-white/10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-cyan-400/15 border border-cyan-300/25 text-cyan-200 flex items-center justify-center text-xl"><i class="fa-solid fa-briefcase"></i></div>
                    <div>
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white mt-3">{{ $formTitle }}</h1>
                <p class="mt-2 text-sm text-blue-100">Define the role, choose its hiring flow, and send it for approval.</p>
                @if($isEditMode)
                    <p class="mt-2 text-sm text-amber-200">This job is still pending approval, so you can update it. Once approved by superadmin, editing is locked.</p>
                @endif
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <form action="{{ $formAction }}" method="POST">
                    @csrf
                    @if($isEditMode)
                        @method('PATCH')
                    @endif

                    <input type="hidden" name="screening_required" id="job-screening-required" value="{{ old('screening_required', isset($job) ? (int) $job->screening_required : 1) }}">

                    <section class="job-flow-card mb-6 p-5 md:p-6">
                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between mb-5">
                            <div>
                                <p class="text-cyan-200 text-xs font-extrabold uppercase tracking-wider"><i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> SimplyHiree Smart Routing</p>
                                <h2 class="mt-1 text-lg font-extrabold text-white">Start with the right candidate journey</h2>
                                <p id="job-flow-summary" class="mt-1 text-sm text-blue-100">SimplyHiree recommends a flow from the role details; you can override it.</p>
                            </div>
                            <span id="job-flow-badge" class="inline-flex items-center justify-center shrink-0 rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-100">Analysing role</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button id="screening-flow-card" type="button" class="job-flow-option p-4" aria-pressed="true">
                                <div class="flex items-start gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-violet-500/20 text-violet-200 flex items-center justify-center shrink-0"><i class="fa-solid fa-shield-halved"></i></span>
                                    <div>
                                        <h3 class="font-bold text-white">Screening Required</h3>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-300">Vendor submits, SimplyHiree screens, then the client receives qualified candidates for interview.</p>
                                    </div>
                                </div>
                            </button>
                            <button id="direct-flow-card" type="button" class="job-flow-option p-4" aria-pressed="false">
                                <div class="flex items-start gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-200 flex items-center justify-center shrink-0"><i class="fa-solid fa-bolt"></i></span>
                                    <div>
                                        <h3 class="font-bold text-white">Direct Interview</h3>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-300">For high-volume or urgent roles, recruiters schedule the interview while submitting each candidate.</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                        <p class="mt-4 text-[11px] text-slate-300"><i class="fa-solid fa-circle-info mr-1 text-cyan-300"></i> Click a flow to choose it. SimplyHiree validates the final routing when the job is submitted for approval.</p>
                    </section>

                    <section class="job-form-section mb-6">
                        <h2 class="job-section-title"><i class="fa-solid fa-briefcase"></i> Role Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-blue-100">Job Title <span class="text-rose-300">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $job->title ?? '') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" placeholder="e.g. Senior Accountant">
                            @error('title') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Category <span class="text-rose-300">*</span></label>
                            <select name="category_id" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('category_id', $job->category_id ?? '') === (string) $cat->id ? 'selected' : '' }} class="text-slate-900">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Job Type <span class="text-rose-300">*</span></label>
                            <select name="job_type" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Type</option>
                                @foreach($jobTypeOptions as $jt)
                                    <option value="{{ $jt }}" {{ old('job_type', $job->job_type ?? '') == $jt ? 'selected' : '' }} class="text-slate-900">{{ $jt }}</option>
                                @endforeach
                            </select>
                            @error('job_type') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-blue-100">Location(s) <span class="text-rose-300">*</span></label>
                            <input type="hidden" name="location" id="job-location" value="{{ old('location', $job->location ?? '') }}">
                            <div id="job-location-chipbox"
                                class="mt-1 flex min-h-[48px] flex-wrap items-center gap-2 rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 focus-within:border-blue-400">
                                <input type="text" id="job-location-search" autocomplete="off"
                                    class="flex-1 min-w-[160px] border-0 bg-transparent text-white placeholder-blue-200/60 focus:outline-none focus:ring-0 p-1"
                                    placeholder="Type a city, press Enter to add">
                            </div>
                            <div id="job-location-suggestions"
                                class="absolute left-0 right-0 top-full z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-600 bg-slate-900 shadow-2xl ring-1 ring-slate-700"></div>
                            <p class="mt-1 text-xs text-blue-200/80">Pick one or more cities. You can also type any location not in the list and press <kbd class="px-1 py-0.5 bg-white/10 rounded text-[10px]">Enter</kbd> or <kbd class="px-1 py-0.5 bg-white/10 rounded text-[10px]">,</kbd> to add it.</p>
                            @error('location') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Salary Range (INR)</label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_salary" placeholder="Min Salary" value="{{ old('min_salary', $existingMinSalary) }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                    @error('min_salary') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_salary" placeholder="Max Salary" value="{{ old('max_salary', $existingMaxSalary) }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                    @error('max_salary') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Experience Range (Years) <span class="text-rose-300">*</span></label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_experience" placeholder="Min" value="{{ old('min_experience', $job->min_experience ?? '') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" required>
                                    @error('min_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_experience" placeholder="Max" value="{{ old('max_experience', $job->max_experience ?? '') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" required>
                                    @error('max_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Desired Candidate Gender <span class="text-rose-300">*</span></label>
                            <select name="gender_preference" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                @foreach(['Any', 'Male', 'Female', 'Other'] as $genderOption)
                                    <option value="{{ $genderOption }}" {{ old('gender_preference', $job->gender_preference ?? 'Any') === $genderOption ? 'selected' : '' }} class="text-slate-900">{{ $genderOption }}</option>
                                @endforeach
                            </select>
                            @error('gender_preference') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Education <span class="text-rose-300">*</span></label>
                            <select name="education_level_id" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}" {{ (string) old('education_level_id', $job->education_level_id ?? '') === (string) $edu->id ? 'selected' : '' }} class="text-slate-900">{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline', optional($job->application_deadline ?? null)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" min="{{ date('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Total Openings</label>
                            <input type="number" name="openings" value="{{ old('openings', $job->openings ?? 1) }}" min="1" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        </div>
                    </div>
                    </section>

                    <section class="job-form-section mb-6">
                        <h2 class="job-section-title"><i class="fa-solid fa-sliders"></i> Additional Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-blue-100">Work Mode</label>
                            <select name="work_mode" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Work Mode</option>
                                @foreach($workModeOptions as $wm)
                                    <option value="{{ $wm }}" {{ old('work_mode', $job->work_mode ?? '') === $wm ? 'selected' : '' }} class="text-slate-900">{{ $wm }}</option>
                                @endforeach
                            </select>
                            @error('work_mode') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Shift</label>
                            <select name="shift" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Shift</option>
                                @foreach($shiftOptions as $sh)
                                    <option value="{{ $sh }}" {{ old('shift', $job->shift ?? '') === $sh ? 'selected' : '' }} class="text-slate-900">{{ $sh }}</option>
                                @endforeach
                            </select>
                            @error('shift') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Specialization</label>
                            <input type="text" name="specialization" value="{{ old('specialization', $job->specialization ?? '') }}" placeholder="e.g. Taxation, Frontend, Cardiology" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('specialization') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Notice Period</label>
                            <input type="text" name="notice_period" value="{{ old('notice_period', $job->notice_period ?? '') }}" placeholder="e.g. Immediate, 30 days, 60 days" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('notice_period') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Languages</label>
                            <input type="text" name="languages" value="{{ old('languages', $job->languages ?? '') }}" placeholder="e.g. English, Hindi, Tamil" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('languages') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Industry</label>
                            <input type="text" name="industry" value="{{ old('industry', $job->industry ?? '') }}" placeholder="e.g. IT, Manufacturing, Healthcare" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('industry') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Department</label>
                            <input type="text" name="department" value="{{ old('department', $job->department ?? '') }}" placeholder="e.g. Finance, Engineering, Sales" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('department') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Reporting Manager</label>
                            <input type="text" name="reporting_manager" value="{{ old('reporting_manager', $job->reporting_manager ?? '') }}" placeholder="e.g. Head of Finance" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('reporting_manager') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Travel Required</label>
                            <select name="travel_required" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Not specified</option>
                                <option value="1" {{ $travelRequiredVal === '1' ? 'selected' : '' }} class="text-slate-900">Yes</option>
                                <option value="0" {{ $travelRequiredVal === '0' ? 'selected' : '' }} class="text-slate-900">No</option>
                            </select>
                            @error('travel_required') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-blue-100 mb-2">Benefits</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
                            @foreach($benefitOptions as $benefit)
                                <label class="flex items-center gap-2 rounded-xl border border-white/15 bg-slate-900/40 px-3 py-2 cursor-pointer hover:border-cyan-300/50 transition">
                                    <input type="checkbox" name="benefits[]" value="{{ $benefit }}" @checked(in_array($benefit, $jobBenefits, true)) class="h-4 w-4 rounded border-white/30 bg-slate-900 text-cyan-400 focus:ring-cyan-300">
                                    <span class="text-sm text-blue-100">{{ $benefit }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('benefits') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                    </div>
                    </section>

                    <section class="job-form-section mb-6">
                        <h2 class="job-section-title"><i class="fa-solid fa-file-lines"></i> Role Description</h2>
                        <label class="block text-sm font-medium text-blue-100">Job Description <span class="text-rose-300">*</span></label>
                        <input type="hidden" name="description" id="job-description-input" value="{{ old('description', $job->description ?? '') }}">
                        <div id="job-description-editor" class="mt-1 bg-slate-900/40 rounded-xl border border-white/20 text-white min-h-[200px]"></div>
                        <p class="mt-1 text-xs text-blue-200/80">Paste from Word or use the advanced toolbar for fonts, tables, images, case conversion, links, alignment, lists and more.</p>
                        @error('description') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                    </section>

                    <section class="job-form-section mb-6">
                        <h2 class="job-section-title"><i class="fa-solid fa-list-check"></i> Skills & Presence</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                        <label class="block text-sm font-medium text-blue-100">Skills Required (Comma separated)</label>
                        <input type="text" name="skills_required" value="{{ old('skills_required', $job->skills_required ?? '') }}" placeholder="e.g. PHP, Laravel, MySQL" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        </div>
                        <div>
                        <label class="block text-sm font-medium text-blue-100">Company Website (Optional)</label>
                        <input type="url" name="company_website" value="{{ old('company_website', $job->company_website ?? '') }}" placeholder="https://example.com" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        </div>
                        </div>

                        <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl border border-amber-300/30 bg-amber-400/10 p-4 transition hover:border-amber-300/60 hover:bg-amber-400/15">
                            <input type="hidden" name="is_company_confidential" value="0">
                            <input type="checkbox" name="is_company_confidential" value="1"
                                   @checked((bool) old('is_company_confidential', $job->is_company_confidential ?? false))
                                   class="mt-0.5 h-5 w-5 rounded border-amber-200/50 bg-slate-900 text-amber-400 focus:ring-2 focus:ring-amber-300">
                            <span class="min-w-0">
                                <span class="flex items-center gap-2 text-sm font-extrabold text-white">
                                    <i class="fa-solid fa-user-secret text-amber-300"></i>
                                    Keep Company Name Confidential
                                </span>
                                <span class="mt-1 block text-xs leading-relaxed text-amber-100/75">Sourcing partners and candidates will see “Confidential Client” instead of your company name and website.</span>
                            </span>
                        </label>
                        @error('is_company_confidential') <span class="mt-1 block text-xs text-rose-300">{{ $message }}</span> @enderror
                    </section>

                    @include('jobs.partials.assessment-stages', ['availableAssessments' => $availableAssessments ?? collect(), 'job' => $job, 'assessmentCreateRoute' => 'client.assessments.create'])

                    <section class="job-form-section mb-6 bg-amber-500/10 border-amber-400/30" x-data="{ commercial: '{{ old('commercial_source', $job->commercial_source ?? 'simplyhire') }}' }">
                        <h3 class="text-amber-200 font-bold text-sm uppercase tracking-wider mb-3 flex items-center gap-2"><i class="fa-solid fa-coins"></i> Commercials</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <label class="cursor-pointer rounded-xl border p-4 transition" :class="commercial === 'simplyhire' ? 'border-cyan-300 bg-cyan-400/15' : 'border-white/15 bg-slate-900/30'">
                                <input type="radio" name="commercial_source" value="simplyhire" x-model="commercial" class="sr-only">
                                <span class="font-bold text-white">Continue with SimplyHire commercials</span>
                                <span class="mt-1 block text-xs text-blue-100">Default. The client commercial set by Admin will apply to this job.</span>
                            </label>
                            <label class="cursor-pointer rounded-xl border p-4 transition" :class="commercial === 'manual' ? 'border-amber-300 bg-amber-400/15' : 'border-white/15 bg-slate-900/30'">
                                <input type="radio" name="commercial_source" value="manual" x-model="commercial" class="sr-only">
                                <span class="font-bold text-white">Manual commercials</span>
                                <span class="mt-1 block text-xs text-blue-100">Set an override for this job only.</span>
                            </label>
                        </div>
                        <div x-show="commercial === 'manual'" x-cloak>
                        <p class="text-amber-100/80 text-xs mb-4">Choose a flat payout or a percentage, then set the release and guarantee terms.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-amber-200 uppercase mb-1">Commercial type</label>
                                <select name="manual_fee_type" class="block w-full rounded-xl border border-amber-400/40 bg-slate-900/60 text-white px-3 py-2.5">
                                    <option value="flat" @selected(old('manual_fee_type', $job->fee_type ?? 'flat') === 'flat')>Flat amount (₹)</option>
                                    <option value="percentage" @selected(old('manual_fee_type', $job->fee_type ?? '') === 'percentage')>Percentage (%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-amber-200 uppercase mb-1">Commercial value <span class="text-rose-300">*</span></label>
                                <input type="number" name="manual_fee_amount" min="0" step="0.01"
                                    value="{{ old('manual_fee_amount', $job->fee_amount ?? '') }}" placeholder="e.g. 25000 or 8"
                                    class="block w-full rounded-xl border border-amber-400/40 bg-slate-900/60 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('manual_fee_amount') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-amber-200 uppercase mb-1">Maturity Period (Days) <span class="text-rose-300">*</span></label>
                                <input type="number" name="minimum_stay_days" min="0" max="365"
                                    value="{{ old('minimum_stay_days', $job->client_payout_days ?? $job->minimum_stay_days ?? 30) }}"
                                    placeholder="e.g. 30"
                                    class="block w-full rounded-xl border border-amber-400/40 bg-slate-900/60 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('minimum_stay_days') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-amber-200 uppercase mb-1">Replacement Guarantee (Days) <span class="text-rose-300">*</span></label>
                                <input type="number" name="replacement_guarantee_days" min="0" max="365"
                                    value="{{ old('replacement_guarantee_days', $job->replacement_period_days ?? $job->replacement_guarantee_days ?? 90) }}"
                                    placeholder="e.g. 90"
                                    class="block w-full rounded-xl border border-amber-400/40 bg-slate-900/60 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('replacement_guarantee_days') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <a href="{{ route('client.jobs.index') }}" class="bg-white/10 border border-white/20 text-slate-100 font-bold py-3 px-6 rounded-xl hover:bg-white/20 transition mr-4">
                            Cancel
                        </a>
                        <button type="submit" class="job-submit-btn bg-gradient-to-r from-cyan-500 to-indigo-500 text-white font-bold py-3 px-8 rounded-xl hover:from-cyan-400 hover:to-indigo-400 transition">
                            {{ $submitLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@include('jobs.partials.advanced-editor', ['uploadUrl' => route('client.jobs.description-images.store')])
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const titleField = document.querySelector('input[name="title"]');
        const experienceField = document.querySelector('input[name="min_experience"]');
        const openingsField = document.querySelector('input[name="openings"]');
        const screeningField = document.getElementById('job-screening-required');
        const screeningCard = document.getElementById('screening-flow-card');
        const directCard = document.getElementById('direct-flow-card');
        const flowSummary = document.getElementById('job-flow-summary');
        const flowBadge = document.getElementById('job-flow-badge');
        let manualFlowChoice = null;

        const applyHiringFlow = function (useDirectFlow, isManual) {
            if (!screeningField || !screeningCard || !directCard || !flowSummary || !flowBadge) return;
            screeningField.value = useDirectFlow ? '0' : '1';
            screeningCard.classList.toggle('is-active', !useDirectFlow);
            screeningCard.classList.toggle('is-muted', useDirectFlow);
            directCard.classList.toggle('is-active', useDirectFlow);
            directCard.classList.toggle('is-muted', !useDirectFlow);
            screeningCard.setAttribute('aria-pressed', String(!useDirectFlow));
            directCard.setAttribute('aria-pressed', String(useDirectFlow));

            if (useDirectFlow) {
                flowBadge.textContent = isManual ? 'Direct Interview selected' : 'Direct Interview recommended';
                flowBadge.className = 'inline-flex items-center justify-center shrink-0 rounded-full border border-emerald-300/30 bg-emerald-400/10 px-3 py-1.5 text-xs font-bold text-emerald-100';
                flowSummary.textContent = isManual
                    ? 'Direct Interview is selected. Recruiters must include an interview date and time with every candidate submission.'
                    : 'High-volume, urgent, or entry-level hiring can move directly to an interview lineup.';
            } else {
                flowBadge.textContent = isManual ? 'Screening selected' : 'Screening required';
                flowBadge.className = 'inline-flex items-center justify-center shrink-0 rounded-full border border-violet-300/30 bg-violet-400/10 px-3 py-1.5 text-xs font-bold text-violet-100';
                flowSummary.textContent = isManual
                    ? 'Screening Required is selected. SimplyHiree reviews submitted candidates before your interview pipeline.'
                    : 'This role benefits from SimplyHiree screening before qualified candidates reach your interview pipeline.';
            }
        };

        const updateHiringFlow = function () {
            if (manualFlowChoice !== null) return;
            const title = (titleField?.value || '').toLowerCase();
            const minimumExperience = Number(experienceField?.value || 0);
            const openings = Number(openingsField?.value || 1);
            const directTerms = ['bpo', 'telecaller', 'sales executive', 'walk-in', 'walkin', 'urgent'];
            const useDirectFlow = openings >= 10 || (minimumExperience <= 1 && directTerms.some(function (term) { return title.includes(term); }));
            applyHiringFlow(useDirectFlow, false);
        };

        screeningCard?.addEventListener('click', function () { manualFlowChoice = false; applyHiringFlow(false, true); });
        directCard?.addEventListener('click', function () { manualFlowChoice = true; applyHiringFlow(true, true); });

        [titleField, experienceField, openingsField].filter(Boolean).forEach(function (field) {
            field.addEventListener('input', updateHiringFlow);
            field.addEventListener('change', updateHiringFlow);
        });
        updateHiringFlow();

        const hidden     = document.getElementById('job-location');
        const chipbox    = document.getElementById('job-location-chipbox');
        const search     = document.getElementById('job-location-search');
        const suggestions= document.getElementById('job-location-suggestions');
        const embeddedCities = @json($indianCities ?? []);

        if (!hidden || !chipbox || !search || !suggestions) return;

        let cities = Array.isArray(embeddedCities) ? embeddedCities : [];
        let selected = (hidden.value || '').split(',').map(s => s.trim()).filter(Boolean);

        const hideSuggestions = () => { suggestions.classList.add('hidden'); suggestions.innerHTML = ''; };

        const syncHidden = () => { hidden.value = selected.join(', '); };

        const renderChips = () => {
            chipbox.querySelectorAll('.loc-chip').forEach(c => c.remove());
            selected.forEach((city, idx) => {
                const chip = document.createElement('span');
                chip.className = 'loc-chip inline-flex items-center gap-1.5 rounded-full bg-blue-500/30 border border-blue-400/40 px-3 py-1 text-sm text-white';
                chip.innerHTML = '<span>' + city.replace(/</g,'&lt;') + '</span><button type="button" aria-label="Remove" class="hover:text-rose-300 leading-none text-base">&times;</button>';
                chip.querySelector('button').addEventListener('click', () => { selected.splice(idx, 1); syncHidden(); renderChips(); });
                chipbox.insertBefore(chip, search);
            });
        };

        const addCity = (raw) => {
            const city = (raw || '').trim().replace(/,+$/, '');
            if (!city) return;
            if (!selected.some(s => s.toLowerCase() === city.toLowerCase())) {
                selected.push(city);
                syncHidden();
                renderChips();
            }
            search.value = '';
            hideSuggestions();
        };

        const showSuggestions = (matches) => {
            if (!matches.length) { hideSuggestions(); return; }
            suggestions.innerHTML = matches.map(c =>
                '<button type="button" class="job-location-option block w-full border-b border-slate-700 px-4 py-3 text-left text-sm font-medium text-white hover:bg-blue-600 transition last:border-b-0">' + c + '</button>'
            ).join('');
            suggestions.classList.remove('hidden');
            suggestions.querySelectorAll('.job-location-option').forEach(btn => {
                btn.addEventListener('click', () => addCity(btn.textContent));
            });
        };

        const updateSuggestions = () => {
            const q = search.value.trim().toLowerCase();
            if (q.length < 2) { hideSuggestions(); return; }
            const matches = cities
                .filter(c => c.toLowerCase().includes(q) && !selected.some(s => s.toLowerCase() === c.toLowerCase()))
                .slice(0, 12);
            showSuggestions(matches);
        };

        search.addEventListener('input', updateSuggestions);
        search.addEventListener('focus', updateSuggestions);
        search.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); if (search.value.trim()) addCity(search.value); }
            else if (e.key === 'Backspace' && !search.value && selected.length) { selected.pop(); syncHidden(); renderChips(); }
            else if (e.key === 'Escape') hideSuggestions();
        });
        chipbox.addEventListener('click', (e) => { if (e.target === chipbox) search.focus(); });
        document.addEventListener('click', (e) => {
            if (!suggestions.contains(e.target) && e.target !== search) hideSuggestions();
        });

        // Block form submit if no locations chosen
        const form = chipbox.closest('form');
        if (form) form.addEventListener('submit', (e) => {
            if (search.value.trim()) addCity(search.value);
            if (!selected.length) {
                e.preventDefault();
                search.focus();
                alert('Please add at least one location.');
            }
        });

        renderChips();

        if (!cities.length) {
            fetch(@js(asset('data/indian-cities.json')))
                .then(r => r.ok ? r.json() : [])
                .then(d => { if (Array.isArray(d)) cities = d; })
                .catch(() => { cities = []; });
        }
    });
</script>
@endsection

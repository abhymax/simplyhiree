@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    #job-description-editor { min-height: 220px; color: #fff; }
    #job-description-editor .ql-editor { min-height: 200px; font-size: 15px; line-height: 1.6; }
    #job-description-editor .ql-editor.ql-blank::before { color: rgba(148, 163, 184, 0.55); font-style: normal; }
    .ql-toolbar.ql-snow { border: 1px solid rgba(255,255,255,0.1); border-bottom: 0; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; background: rgba(15,23,42,0.6); }
    .ql-container.ql-snow { border: 1px solid rgba(255,255,255,0.1); border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; font-family: inherit; }
    .ql-snow .ql-stroke { stroke: #cbd5e1; }
    .ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill { fill: #cbd5e1; }
    .ql-snow .ql-picker { color: #cbd5e1; }
    .ql-snow .ql-picker-options { background: #0f172a; color: #fff; border-color: rgba(255,255,255,0.1); }
    .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #60a5fa; }
    .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #60a5fa; }
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
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">

    {{-- DECORATIVE BACKGROUND GLOWS --}}
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-5xl mx-auto">

        {{-- HEADER --}}
        <div class="mb-8 border-b border-white/10 pb-6">
            <a href="{{ route('client.dashboard') }}" class="inline-flex items-center text-blue-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                <i class="fa-solid fa-arrow-left mr-2"></i> Cancel &amp; Return
            </a>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-white/80 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">{{ $formTitle }}</h1>
            @if($isEditMode)
                <p class="mt-2 text-amber-300 text-sm"><i class="fa-solid fa-circle-info mr-1"></i> This job is still pending approval, so you can update it. Once approved, editing is locked.</p>
            @else
                <p class="mt-2 text-white/80">Fill in the details below. The Superadmin will review your job before it goes live to vendors.</p>
            @endif
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-rose-500/10 border border-rose-400/40 text-rose-100 p-4 rounded-2xl backdrop-blur-md">
                <p class="font-bold flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Please fix the following errors:</p>
                <ul class="list-disc ml-6 mt-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
                <form action="{{ $formAction }}" method="POST">
                    @csrf
                    @if($isEditMode)
                        @method('PATCH')
                    @endif

                    {{-- Job Specification Card --}}
                    <div class="rounded-2xl p-5 shadow-lg" style="background:#0443cd; margin-bottom: 2.5rem;">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-3">
                            <span class="w-1.5 h-7 bg-white rounded-full"></span>
                            <i class="fa-solid fa-briefcase text-white"></i> Job Specification
                        </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-white">Job Title <span class="text-rose-300">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $job->title ?? '') }}" required class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white" placeholder="e.g. Senior Accountant">
                            @error('title') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Category <span class="text-rose-300">*</span></label>
                            <select name="category_id" required class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                <option value="" class="text-slate-900">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('category_id', $job->category_id ?? '') === (string) $cat->id ? 'selected' : '' }} class="text-slate-900">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Job Type <span class="text-rose-300">*</span></label>
                            <select name="job_type" required class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                <option value="" class="text-slate-900">Select Type</option>
                                <option value="Full-time" {{ old('job_type', $job->job_type ?? '') == 'Full-time' ? 'selected' : '' }} class="text-slate-900">Full-time</option>
                                <option value="Part-time" {{ old('job_type', $job->job_type ?? '') == 'Part-time' ? 'selected' : '' }} class="text-slate-900">Part-time</option>
                                <option value="Contract" {{ old('job_type', $job->job_type ?? '') == 'Contract' ? 'selected' : '' }} class="text-slate-900">Contract</option>
                                <option value="Internship" {{ old('job_type', $job->job_type ?? '') == 'Internship' ? 'selected' : '' }} class="text-slate-900">Internship</option>
                            </select>
                            @error('job_type') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-white">Location(s) <span class="text-rose-300">*</span></label>
                            <input type="hidden" name="location" id="job-location" value="{{ old('location', $job->location ?? '') }}">
                            <div id="job-location-chipbox"
                                class="mt-1 flex min-h-[48px] flex-wrap items-center gap-2 rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 focus-within:border-blue-400">
                                <input type="text" id="job-location-search" autocomplete="off"
                                    class="flex-1 min-w-[160px] border-0 bg-transparent text-white placeholder-blue-200/60 focus:outline-none focus:ring-0 p-1"
                                    placeholder="Type a city, press Enter to add">
                            </div>
                            <div id="job-location-suggestions"
                                class="absolute left-0 right-0 top-full z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-600 bg-slate-900 shadow-2xl ring-1 ring-slate-700"></div>
                            <p class="mt-1 text-xs text-white/80/80">Pick one or more cities. You can also type any location not in the list and press <kbd class="px-1 py-0.5 bg-white/10 rounded text-[10px]">Enter</kbd> or <kbd class="px-1 py-0.5 bg-white/10 rounded text-[10px]">,</kbd> to add it.</p>
                            @error('location') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Salary Range (INR)</label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_salary" placeholder="Min Salary" value="{{ old('min_salary', $existingMinSalary) }}" min="0" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                    @error('min_salary') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_salary" placeholder="Max Salary" value="{{ old('max_salary', $existingMaxSalary) }}" min="0" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                    @error('max_salary') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Experience Range (Years) <span class="text-rose-300">*</span></label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_experience" placeholder="Min" value="{{ old('min_experience', $job->min_experience ?? '') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white" required>
                                    @error('min_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_experience" placeholder="Max" value="{{ old('max_experience', $job->max_experience ?? '') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white" required>
                                    @error('max_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Desired Candidate Gender <span class="text-rose-300">*</span></label>
                            <select name="gender_preference" required class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                @foreach(['Any', 'Male', 'Female', 'Other'] as $genderOption)
                                    <option value="{{ $genderOption }}" {{ old('gender_preference', $job->gender_preference ?? 'Any') === $genderOption ? 'selected' : '' }} class="text-slate-900">{{ $genderOption }}</option>
                                @endforeach
                            </select>
                            @error('gender_preference') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Education <span class="text-rose-300">*</span></label>
                            <select name="education_level_id" required class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}" {{ (string) old('education_level_id', $job->education_level_id ?? '') === (string) $edu->id ? 'selected' : '' }} class="text-slate-900">{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline', optional($job->application_deadline ?? null)->format('Y-m-d')) }}" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white" min="{{ date('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white">Total Openings</label>
                            <input type="number" name="openings" value="{{ old('openings', $job->openings ?? 1) }}" min="1" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                        </div>
                    </div>

                    </div>{{-- /Job Specification grid --}}
                    </div>{{-- /Job Specification card --}}

                    {{-- Description & Skills Card --}}
                    <div class="rounded-2xl p-5 shadow-lg" style="background:#0443cd; margin-bottom: 2.5rem;">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-3">
                            <span class="w-1.5 h-7 bg-white rounded-full"></span>
                            <i class="fa-solid fa-align-left text-white"></i> Description &amp; Skills
                        </h3>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-white">Job Description <span class="text-rose-300">*</span></label>
                            <input type="hidden" name="description" id="job-description-input" value="{{ old('description', $job->description ?? '') }}">
                            <div id="job-description-editor" class="mt-1 bg-slate-900/40 rounded-xl border border-white/10 text-white min-h-[200px]"></div>
                            <p class="mt-1 text-xs text-slate-400">Use the toolbar to format — bold, italic, headings, lists, links, etc.</p>
                            @error('description') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white">Skills Required (Comma separated)</label>
                                <input type="text" name="skills_required" value="{{ old('skills_required', $job->skills_required ?? '') }}" placeholder="e.g. PHP, Laravel, MySQL" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white">Company Website (Optional)</label>
                                <input type="url" name="company_website" value="{{ old('company_website', $job->company_website ?? '') }}" placeholder="https://example.com" class="mt-1 block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Vendor Assignment Card --}}
                    @php $currMode = old('vendor_assignment_mode', $job->vendor_assignment_mode ?? 'open'); @endphp
                    <div class="rounded-2xl p-5 shadow-lg" style="background:#0443cd; margin-bottom: 2.5rem;" x-data="{ mode: '{{ $currMode }}' }">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-3">
                            <span class="w-1.5 h-7 bg-white rounded-full"></span>
                            <i class="fa-solid fa-handshake text-white"></i> Vendor Assignment
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="cursor-pointer flex items-start gap-2 bg-slate-900/40 border border-white/10 rounded-xl px-3 py-3" :class="mode==='open' ? 'ring-2 ring-blue-400 border-blue-400/50' : ''">
                                <input type="radio" name="vendor_assignment_mode" value="open" x-model="mode" class="mt-1">
                                <div>
                                    <div class="text-white font-bold text-sm">🔓 Open Marketplace</div>
                                    <div class="text-white/80 text-xs">All active partners can apply</div>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-start gap-2 bg-slate-900/40 border border-white/10 rounded-xl px-3 py-3" :class="mode==='preferred' ? 'ring-2 ring-blue-400 border-blue-400/50' : ''">
                                <input type="radio" name="vendor_assignment_mode" value="preferred" x-model="mode" class="mt-1">
                                <div>
                                    <div class="text-white font-bold text-sm">⭐ Preferred Only</div>
                                    <div class="text-white/80 text-xs">Only my saved Preferred vendors</div>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-start gap-2 bg-slate-900/40 border border-white/10 rounded-xl px-3 py-3" :class="mode==='selected' ? 'ring-2 ring-blue-400 border-blue-400/50' : ''">
                                <input type="radio" name="vendor_assignment_mode" value="selected" x-model="mode" class="mt-1">
                                <div>
                                    <div class="text-white font-bold text-sm">🎯 Selected (Per-Job)</div>
                                    <div class="text-white/80 text-xs">Pick specific vendors for this job</div>
                                </div>
                            </label>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-white/80 text-[11px] uppercase font-bold mb-1">Max Vendors per Job (optional)</label>
                                <input type="number" name="max_vendors_per_job" min="1" max="50" value="{{ old('max_vendors_per_job', $job->max_vendors_per_job ?? '') }}" placeholder="e.g. 5"
                                    class="block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white px-3 py-2.5">
                            </div>
                            <div class="md:col-span-2" x-show="mode === 'selected'" x-cloak>
                                <label class="block text-white/80 text-[11px] uppercase font-bold mb-1">Pick from Preferred Vendors</label>
                                @php $preferred = auth()->user()->preferredVendors()->orderBy('name')->get(); @endphp
                                @if($preferred->isEmpty())
                                    <p class="text-rose-200 text-xs">You have no preferred vendors yet. <a href="{{ route('client.vendors.browse') }}" class="underline">Browse and add some</a> first.</p>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-40 overflow-y-auto pr-1">
                                        @foreach($preferred as $pv)
                                            <label class="flex items-center gap-2 text-white text-sm bg-slate-900/60 border border-white/10 rounded-md px-2 py-1.5">
                                                <input type="checkbox" name="allowed_partners[]" value="{{ $pv->id }}" class="rounded">
                                                <span>{{ $pv->name }} <span class="text-blue-300 text-xs">(⭐ {{ $pv->avg_rating ?? '—' }})</span></span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Confidentiality toggle --}}
                        <div class="mt-5 pt-4 border-t border-white/20">
                            <label class="flex items-start gap-3 cursor-pointer select-none">
                                <input type="hidden" name="is_company_confidential" value="0">
                                <input type="checkbox" name="is_company_confidential" value="1"
                                       {{ old('is_company_confidential', $job->is_company_confidential ?? false) ? 'checked' : '' }}
                                       class="mt-1 h-5 w-5 rounded border-white/40 bg-blue-950/40 text-white focus:ring-2 focus:ring-white">
                                <div>
                                    <div class="text-white font-bold text-sm flex items-center gap-2">
                                        <i class="fa-solid fa-user-secret"></i> Keep company name confidential
                                    </div>
                                    <p class="text-white/80 text-xs mt-0.5">Vendors and candidates will see this posting as "Confidential" until you choose to reveal your company.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Payout Settings Card --}}
                    <div class="rounded-2xl p-5 shadow-lg" style="background:#0443cd; margin-bottom: 2.5rem;">
                        <h3 class="text-lg font-bold text-white mb-1 flex items-center gap-3">
                            <span class="w-1.5 h-7 bg-white rounded-full"></span>
                            <i class="fa-solid fa-coins text-white"></i> Payout Settings
                        </h3>
                        <p class="text-white/80 text-sm ml-5 mb-4">
                            Payout amount per successful hire, the maturity period before release, and the replacement-guarantee window.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-white uppercase mb-1">Payout Amount (₹) <span class="text-rose-300">*</span></label>
                                <input type="number" name="payout_amount" min="0" step="0.01" required
                                    value="{{ old('payout_amount', $job->payout_amount ?? '') }}"
                                    placeholder="e.g. 25000"
                                    class="block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('payout_amount') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-white uppercase mb-1">Maturity Period (Days) <span class="text-rose-300">*</span></label>
                                <input type="number" name="minimum_stay_days" min="0" max="365" required
                                    value="{{ old('minimum_stay_days', $job->minimum_stay_days ?? 30) }}"
                                    placeholder="e.g. 30"
                                    class="block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('minimum_stay_days') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-white uppercase mb-1">Replacement Guarantee (Days) <span class="text-rose-300">*</span></label>
                                <input type="number" name="replacement_guarantee_days" min="0" max="365" required
                                    value="{{ old('replacement_guarantee_days', $job->replacement_guarantee_days ?? 90) }}"
                                    placeholder="e.g. 90"
                                    class="block w-full rounded-xl border border-white/30 bg-blue-950/40 text-white px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                @error('replacement_guarantee_days') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-3 pt-2">
                        <a href="{{ route('client.dashboard') }}" class="bg-white/10 border border-white/10 text-slate-100 font-bold py-3 px-6 rounded-xl hover:bg-white/20 transition backdrop-blur-md">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-3 px-8 rounded-xl transition flex items-center gap-2 shadow-lg hover:shadow-blue-500/40">
                            <i class="fa-solid fa-paper-plane"></i> {{ $submitLabel }}
                        </button>
                    </div>
                </form>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Job description rich-text editor
        const descEditorEl = document.getElementById('job-description-editor');
        const descHidden   = document.getElementById('job-description-input');
        if (descEditorEl && descHidden && window.Quill) {
            const quill = new Quill(descEditorEl, {
                theme: 'snow',
                placeholder: 'Describe the role, responsibilities, requirements...',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ indent: '-1' }, { indent: '+1' }],
                        [{ align: [] }],
                        ['blockquote', 'link'],
                        ['clean'],
                    ],
                },
            });
            // Pre-fill with existing/old value
            const initialHtml = descHidden.value || '';
            if (initialHtml) {
                quill.clipboard.dangerouslyPasteHTML(initialHtml);
            }
            // Sync editor → hidden input on every change AND on submit
            quill.on('text-change', () => {
                const html = quill.root.innerHTML;
                descHidden.value = (quill.getText().trim().length === 0) ? '' : html;
            });
            const descForm = descHidden.closest('form');
            if (descForm) {
                descForm.addEventListener('submit', () => {
                    descHidden.value = (quill.getText().trim().length === 0) ? '' : quill.root.innerHTML;
                });
            }
        }

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

<x-app-layout>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    #job-description-editor { min-height: 240px; color: #fff; }
    #job-description-editor .ql-editor { min-height: 220px; font-size: 15px; line-height: 1.7; }
    #job-description-editor .ql-editor.ql-blank::before { color: rgba(191, 219, 254, .55); font-style: normal; }
    .ql-toolbar.ql-snow { border: 1px solid rgba(255,255,255,.15); border-bottom: 0; border-radius: .75rem .75rem 0 0; background: rgba(15,23,42,.8); }
    .ql-container.ql-snow { border: 1px solid rgba(255,255,255,.15); border-radius: 0 0 .75rem .75rem; background: rgba(15,23,42,.8); font-family: inherit; }
    .ql-snow .ql-stroke { stroke: #cbd5e1; }
    .ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill { fill: #cbd5e1; }
    .ql-snow .ql-picker { color: #cbd5e1; }
    .ql-snow .ql-picker-options { background: #0f172a; color: #fff; border-color: rgba(255,255,255,.2); }
    .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #67e8f9; }
    .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill { fill: #67e8f9; }
</style>
<div class="min-h-screen bg-slate-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 text-white">
<div class="max-w-6xl mx-auto">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4 border-b border-white/10 pb-5">
        <div>
            <a href="{{ route('admin.jobs.show', $job) }}" class="text-sm font-bold uppercase text-cyan-300 hover:text-white"><i class="fa-solid fa-arrow-left mr-2"></i>Job details</a>
            <h1 class="mt-3 text-4xl font-black">{{ $job->status === 'approved' ? 'Edit Live Job' : 'Edit Job & Commercials' }}</h1>
            <p class="mt-1 text-slate-400">Update job details, commercials, hiring flow, and vendor access in one place.</p>
        </div>
        <span class="rounded-full border border-amber-400/40 bg-amber-500/10 px-4 py-2 text-xs font-bold uppercase text-amber-200">Superadmin control</span>
    </div>

    @if(isset($errors) && $errors->any())<div class="mb-5 rounded-xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-100">{{ $errors->first() }}</div>@endif
    @if($job->status === 'approved')
        <div class="mb-5 flex gap-3 rounded-2xl border border-amber-400/35 bg-amber-500/10 p-4 text-sm text-amber-100">
            <i class="fa-solid fa-tower-broadcast mt-0.5 text-amber-300"></i>
            <div><strong class="block text-white">This job is live.</strong>Saved changes appear immediately. Existing applications, interview history, and candidate decisions remain attached to this job.</div>
        </div>
    @endif
    @php
        $input='w-full rounded-xl border border-white/15 bg-slate-900/80 px-3 py-2.5 text-white focus:border-cyan-400 focus:ring-cyan-400';
        $label='mb-2 block text-xs font-bold uppercase text-cyan-200';
        $selectedPartners=old('allowed_partners',$job->allowedPartners->pluck('id')->all());
        $jobTypeOptions=['Full-time','Part-time','Contract','Contract-to-Hire (C2H)','Internship','Freelance','Temporary','Permanent','Trainee','Consultant'];
        $workModeOptions=['On-site','Hybrid','Remote','Field Job','Work From Home (WFH)'];
        $shiftOptions=['Day Shift','Night Shift','Rotational Shift','Flexible Shift','Weekend Shift'];
        $benefitOptions=['PF','ESIC','Insurance','Food','Transport','Accommodation','Laptop','Mobile','Joining Bonus','Relocation'];
        $jobBenefits=old('benefits',$job->benefits ?? []); if(!is_array($jobBenefits)){$jobBenefits=[];}
        $travelRequiredVal=old('travel_required', isset($job->travel_required)?(int)$job->travel_required:'');
        $travelRequiredVal=($travelRequiredVal===''||$travelRequiredVal===null)?'':(string)$travelRequiredVal;
    @endphp
    <form method="POST" action="{{ route('admin.jobs.update',$job) }}" class="space-y-6" x-data="{ commercial: '{{ old('commercial_source',$job->commercial_source ?: 'simplyhire') }}', visibility: '{{ old('partner_visibility',$job->partner_visibility ?: 'all') }}' }">
        @csrf @method('PATCH')
        <section class="rounded-2xl border border-blue-400/20 bg-blue-500/5 p-6">
            <h2 class="mb-5 text-xl font-bold"><i class="fa-solid fa-briefcase mr-2 text-blue-300"></i>Job details</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="{{ $label }}">Client</label><select name="client_id" class="{{ $input }}"><option value="">SimplyHiree internal</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected((string)old('client_id',$job->user_id)===(string)$client->id)>{{ $client->name }} ({{ $client->email }})</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Company name</label><input name="company_name" required value="{{ old('company_name',$job->company_name) }}" class="{{ $input }}"></div>
                <div class="md:col-span-2"><label class="{{ $label }}">Job title</label><input name="title" required value="{{ old('title',$job->title) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Category</label><select name="category_id" required class="{{ $input }}">@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('category_id',$job->category_id)===(string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Job type</label><select name="job_type" class="{{ $input }}">@foreach($jobTypeOptions as $type)<option @selected(old('job_type',$job->job_type)===$type)>{{ $type }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Location</label><input name="location" required value="{{ old('location',$job->location) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Salary display</label><input name="salary" value="{{ old('salary',$job->salary) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Minimum experience</label><input type="number" name="min_experience" min="0" required value="{{ old('min_experience',$job->min_experience) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Maximum experience</label><input type="number" name="max_experience" min="0" required value="{{ old('max_experience',$job->max_experience) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Education</label><select name="education_level_id" required class="{{ $input }}">@foreach($educationLevels as $education)<option value="{{ $education->id }}" @selected((string)old('education_level_id',$job->education_level_id)===(string)$education->id)>{{ $education->name }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Openings</label><input type="number" name="openings" min="1" required value="{{ old('openings',$job->openings ?: 1) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Deadline</label><input type="date" name="application_deadline" value="{{ old('application_deadline',optional($job->application_deadline)->format('Y-m-d')) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Status</label><select name="status" class="{{ $input }}">@foreach(['pending_approval','approved','on_hold','closed','rejected'] as $status)<option value="{{ $status }}" @selected(old('status',$job->status)===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Gender preference</label><select name="gender_preference" class="{{ $input }}">@foreach(['Any','Male','Female','Other'] as $gender)<option @selected(old('gender_preference',$job->gender_preference ?: 'Any')===$gender)>{{ $gender }}</option>@endforeach</select></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="{{ $label }}">Min age</label><input type="number" name="min_age" value="{{ old('min_age',$job->min_age) }}" class="{{ $input }}"></div><div><label class="{{ $label }}">Max age</label><input type="number" name="max_age" value="{{ old('max_age',$job->max_age) }}" class="{{ $input }}"></div></div>
                <div><label class="{{ $label }}">Website</label><input type="url" name="company_website" value="{{ old('company_website',$job->company_website) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Company Name Visibility</label><select name="is_company_confidential" class="{{ $input }}"><option value="0" @selected(!old('is_company_confidential',$job->is_company_confidential))>Show company name</option><option value="1" @selected(old('is_company_confidential',$job->is_company_confidential))>Keep Company Name Confidential</option></select></div>
                <div><label class="{{ $label }}">Hiring flow</label><select name="screening_required" class="{{ $input }}"><option value="1" @selected(old('screening_required',$job->screening_required))>Screening by SimplyHiree</option><option value="0" @selected(!old('screening_required',$job->screening_required))>Direct interview lineup</option></select></div>
                <div class="md:col-span-2"><label class="{{ $label }}">Skills</label><input name="skills_required" value="{{ old('skills_required',$job->skills_required) }}" class="{{ $input }}"></div>
                <div class="md:col-span-2">
                    <label class="{{ $label }}">Description</label>
                    <input type="hidden" name="description" id="job-description-input" value="{{ old('description',$job->description) }}">
                    <div id="job-description-editor" aria-label="Job description rich text editor"></div>
                    <p class="mt-2 text-xs text-slate-400">The description is displayed as formatted content. Use the toolbar to update headings, emphasis, lists, alignment, quotes, and links.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-cyan-400/20 bg-cyan-500/5 p-6">
            <h2 class="mb-5 text-xl font-bold"><i class="fa-solid fa-sliders mr-2 text-cyan-300"></i>Additional details</h2>
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="{{ $label }}">Work mode</label><select name="work_mode" class="{{ $input }}"><option value="">Select work mode</option>@foreach($workModeOptions as $wm)<option value="{{ $wm }}" @selected(old('work_mode',$job->work_mode)===$wm)>{{ $wm }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Shift</label><select name="shift" class="{{ $input }}"><option value="">Select shift</option>@foreach($shiftOptions as $sh)<option value="{{ $sh }}" @selected(old('shift',$job->shift)===$sh)>{{ $sh }}</option>@endforeach</select></div>
                <div><label class="{{ $label }}">Specialization</label><input name="specialization" value="{{ old('specialization',$job->specialization) }}" class="{{ $input }}" placeholder="e.g. Taxation, Frontend"></div>
                <div><label class="{{ $label }}">Notice period</label><input name="notice_period" value="{{ old('notice_period',$job->notice_period) }}" class="{{ $input }}" placeholder="e.g. 30 days"></div>
                <div><label class="{{ $label }}">Languages</label><input name="languages" value="{{ old('languages',$job->languages) }}" class="{{ $input }}" placeholder="e.g. English, Hindi"></div>
                <div><label class="{{ $label }}">Industry</label><input name="industry" value="{{ old('industry',$job->industry) }}" class="{{ $input }}" placeholder="e.g. IT, Manufacturing"></div>
                <div><label class="{{ $label }}">Department</label><input name="department" value="{{ old('department',$job->department) }}" class="{{ $input }}" placeholder="e.g. Finance, Sales"></div>
                <div><label class="{{ $label }}">Reporting manager</label><input name="reporting_manager" value="{{ old('reporting_manager',$job->reporting_manager) }}" class="{{ $input }}" placeholder="e.g. Head of Finance"></div>
                <div><label class="{{ $label }}">Travel required</label><select name="travel_required" class="{{ $input }}"><option value="">Not specified</option><option value="1" @selected($travelRequiredVal==='1')>Yes</option><option value="0" @selected($travelRequiredVal==='0')>No</option></select></div>
            </div>
            <div class="mt-5">
                <label class="{{ $label }}">Benefits</label>
                <div class="grid grid-cols-2 gap-2 md:grid-cols-5">
                    @foreach($benefitOptions as $benefit)<label class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-900/60 p-3"><input type="checkbox" name="benefits[]" value="{{ $benefit }}" @checked(in_array($benefit,$jobBenefits,true))><span>{{ $benefit }}</span></label>@endforeach
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-amber-400/25 bg-amber-500/5 p-6">
            <h2 class="mb-5 text-xl font-bold"><i class="fa-solid fa-coins mr-2 text-amber-300"></i>Commercials & guarantee</h2>
            <div class="grid gap-5 md:grid-cols-3">
                <div><label class="{{ $label }}">Commercial source</label><select name="commercial_source" x-model="commercial" class="{{ $input }}"><option value="simplyhire">SimplyHiree client agreement</option><option value="manual">Manual override</option></select></div>
                <div x-cloak x-show="commercial==='manual'"><label class="{{ $label }}">Fee type</label><select name="fee_type" class="{{ $input }}"><option value="flat" @selected(old('fee_type',$job->fee_type)==='flat')>Flat amount</option><option value="percentage" @selected(old('fee_type',$job->fee_type)==='percentage')>Percentage</option></select></div>
                <div x-cloak x-show="commercial==='manual'"><label class="{{ $label }}">Fee amount / %</label><input type="number" step="0.01" name="fee_amount" value="{{ old('fee_amount',$job->fee_amount) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Partner payout</label><input type="number" step="0.01" name="payout_amount" required value="{{ old('payout_amount',$job->payout_amount ?: 0) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Payout maturity days</label><input type="number" name="minimum_stay_days" required value="{{ old('minimum_stay_days',$job->minimum_stay_days ?: 0) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Guarantee days</label><input type="number" name="replacement_guarantee_days" value="{{ old('replacement_guarantee_days',$job->replacement_guarantee_days) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Invoice release days</label><input type="number" name="invoice_release_days" value="{{ old('invoice_release_days',$job->invoice_release_days) }}" class="{{ $input }}"></div>
                <div><label class="{{ $label }}">Replacement response days</label><input type="number" name="replacement_period_days" value="{{ old('replacement_period_days',$job->replacement_period_days) }}" class="{{ $input }}"></div>
            </div>
        </section>

        <section class="rounded-2xl border border-emerald-400/20 bg-emerald-500/5 p-6">
            <h2 class="mb-2 text-xl font-bold"><i class="fa-solid fa-sliders mr-2 text-emerald-300"></i>Vendor distribution controls</h2>
            <p class="mb-5 text-sm text-slate-400">Control which sourcing partners can access this job and when screened resumes are forwarded.</p>
            <label class="{{ $label }}">Job access</label>
            <select name="partner_visibility" x-model="visibility" class="{{ $input }}"><option value="all">All active partners</option><option value="selected">Selected partners only</option></select>
            <div x-cloak x-show="visibility==='selected'" class="mt-4 grid max-h-64 gap-2 overflow-y-auto md:grid-cols-3">
                @foreach($partners as $partner)<label class="flex items-center gap-2 rounded-lg border border-white/10 bg-slate-900/60 p-3"><input type="checkbox" name="allowed_partners[]" value="{{ $partner->id }}" @checked(in_array($partner->id,$selectedPartners))><span>{{ $partner->name }}</span></label>@endforeach
            </div>
            <div class="mt-5 max-w-sm">
                <label class="{{ $label }}">Auto-forward after hours</label>
                <input type="number" name="auto_forward_hours" min="0" max="720" value="{{ old('auto_forward_hours',$job->auto_forward_hours) }}" class="{{ $input }}" placeholder="0">
                <p class="mt-2 text-xs text-slate-400">Use 0 or leave blank to disable. Pending screened resumes are forwarded after this many hours.</p>
            </div>
        </section>

        <div class="flex justify-end gap-3"><a href="{{ route('admin.jobs.show',$job) }}" class="rounded-xl border border-white/15 px-5 py-3 font-bold text-slate-200">Cancel</a><button class="rounded-xl bg-emerald-500 px-6 py-3 font-black text-slate-950 hover:bg-emerald-400"><i class="fa-solid fa-floppy-disk mr-2"></i>{{ $job->status === 'approved' ? 'Save live changes' : 'Save changes' }}</button></div>
    </form>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editorElement = document.getElementById('job-description-editor');
    const descriptionInput = document.getElementById('job-description-input');
    if (!editorElement || !descriptionInput || !window.Quill) return;

    const editor = new Quill(editorElement, {
        theme: 'snow',
        placeholder: 'Describe the role, responsibilities, and requirements...',
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

    if (descriptionInput.value) {
        editor.clipboard.dangerouslyPasteHTML(descriptionInput.value);
    }

    const syncDescription = function () {
        descriptionInput.value = editor.getText().trim().length === 0 ? '' : editor.root.innerHTML;
    };
    editor.on('text-change', syncDescription);
    descriptionInput.closest('form')?.addEventListener('submit', syncDescription);
});
</script>
</x-app-layout>

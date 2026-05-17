@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <form action="{{ route('partner.jobs.submit', $job->id) }}" method="POST" id="applicationForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6">
                        <h1 class="text-3xl font-bold text-white mb-2">{{ $job->title }}</h1>
                        <p class="text-lg text-slate-200 mb-4">{{ $job->is_company_confidential ? 'SimplyHiree Client' : $job->company_name }} - {{ $job->location }}</p>

                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-blue-500/20 border border-blue-400/30 text-blue-100 text-sm font-medium px-3 py-1 rounded">
                                {{ $job->salary ?? 'Salary Not Disclosed' }}
                            </span>
                            <span class="bg-indigo-500/20 border border-indigo-400/30 text-indigo-100 text-sm font-medium px-3 py-1 rounded">
                                {{ $job->job_type }}
                            </span>
                        </div>

                        <hr class="my-6 border-white/10">

                        <h3 class="text-lg font-bold text-white mb-3">Job Description</h3>
                        <div class="text-slate-100 mb-6 text-sm leading-7 job-desc-html">
                            {!! $job->formatted_description !!}
                        </div>

                        <h3 class="text-lg font-bold text-white mb-3">Requirements</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-200">
                            <div><span class="font-semibold text-blue-100">Experience:</span> {{ $job->formatted_experience }}</div>
                            <div><span class="font-semibold text-blue-100">Education:</span> {{ $job->educationLevel->name ?? 'N/A' }}</div>
                            <div class="sm:col-span-2"><span class="font-semibold text-blue-100">Skills:</span> {{ $job->skills_required ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    @php
                        $partner = auth()->user();
                        $alreadyCount = \App\Models\JobApplication::where('job_id', $job->id)
                            ->whereHas('candidate', fn($q) => $q->where('partner_id', $partner->id))
                            ->count();
                        $cap = (int) ($job->max_resume_per_vendor ?? 0);
                        $remaining = $cap > 0 ? max(0, $cap - $alreadyCount) : null;
                        $deadlinePassed = $job->resume_submission_deadline && now()->isAfter($job->resume_submission_deadline);
                        $isBlocked = $deadlinePassed || ($remaining === 0);
                    @endphp

                    @if($job->resume_submission_deadline || $cap > 0)
                    <div class="mb-3 rounded-2xl border {{ $isBlocked ? 'bg-rose-500/10 border-rose-400/40' : 'bg-blue-500/10 border-blue-400/30' }} px-4 py-3 text-sm space-y-1">
                        @if($job->resume_submission_deadline)
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-white"><i class="fa-regular fa-clock mr-1"></i> Submission Deadline</span>
                                <span class="{{ $deadlinePassed ? 'text-rose-200 font-bold' : 'text-blue-100' }}">
                                    {{ \Illuminate\Support\Carbon::parse($job->resume_submission_deadline)->format('d M Y, H:i') }}
                                    {{ $deadlinePassed ? '(closed)' : '· ' . \Illuminate\Support\Carbon::parse($job->resume_submission_deadline)->diffForHumans() }}
                                </span>
                            </div>
                        @endif
                        @if($cap > 0)
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-white"><i class="fa-solid fa-users mr-1"></i> Resumes Quota</span>
                                <span class="{{ $remaining === 0 ? 'text-rose-200 font-bold' : 'text-blue-100' }}">
                                    {{ $alreadyCount }} / {{ $cap }} used · {{ $remaining }} left
                                </span>
                            </div>
                        @endif
                        @if($isBlocked)
                            <div class="text-rose-200 text-xs pt-1 border-t border-rose-400/20 mt-1.5">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                {{ $deadlinePassed ? 'The submission window for this job has closed.' : 'You have reached your resume cap for this job.' }}
                            </div>
                        @endif
                    </div>
                    @endif

                    <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden flex flex-col h-[calc(100vh-100px)] sticky top-6">
                        <div class="p-4 bg-slate-900/40 border-b border-white/10 z-10">
                            <h3 class="font-bold text-white mb-3">Select Candidates</h3>

                            <div class="relative mb-3">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-search text-slate-300"></i>
                                </div>
                                <input type="text" id="candidateSearch" placeholder="Search by name or skill..."
                                       class="pl-10 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 text-sm">
                            </div>

                            <button type="button" onclick="openModal()" class="w-full flex justify-center items-center px-4 py-2 border border-dashed border-indigo-300/60 rounded-xl text-sm font-medium text-indigo-100 bg-indigo-500/10 hover:bg-indigo-500/20">
                                <i class="fa-solid fa-plus mr-2"></i> Add New Candidate
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="candidateList">
                            @forelse($candidates as $candidate)
                                <label class="candidate-item flex items-start p-3 border border-white/10 rounded-xl hover:bg-white/5 cursor-pointer transition relative">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="candidate_ids[]" value="{{ $candidate->id }}" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-white/30 rounded bg-slate-900/40">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <div class="font-bold text-white search-name">{{ $candidate->first_name }} {{ $candidate->last_name }}</div>
                                        <div class="text-slate-300 text-xs">{{ $candidate->email }}</div>
                                        <div class="text-slate-300 text-xs mt-1 search-skills">
                                            <i class="fa-solid fa-code text-slate-300 mr-1"></i> {{ Str::limit($candidate->skills, 40) }}
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center py-4 text-slate-300 text-sm">
                                    No candidates found in your pool.
                                </div>
                            @endforelse
                        </div>

                        <div class="p-4 bg-slate-900/40 border-t border-white/10">
                            <button type="submit" {{ $isBlocked ? 'disabled' : '' }}
                                class="w-full justify-center inline-flex items-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-600 hover:to-indigo-600 transition disabled:from-slate-700 disabled:to-slate-700 disabled:cursor-not-allowed disabled:opacity-70">
                                {{ $isBlocked ? ($deadlinePassed ? 'Deadline Passed' : 'Cap Reached') : 'Submit Selected' }}
                            </button>
                            <a href="{{ route('partner.jobs') }}" class="block text-center mt-3 text-sm text-slate-300 hover:text-white">Cancel</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div id="addCandidateModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-950/70 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-slate-900 border border-white/10 text-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[85vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4 border-b border-white/10 pb-2">
                    <h3 class="text-lg leading-6 font-bold text-white" id="modal-title">Add New Candidate</h3>
                    <button onclick="closeModal()" class="text-slate-300 hover:text-white">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <form id="quickAddForm" enctype="multipart/form-data">
                    @csrf

                    <h4 class="text-sm font-bold text-blue-100 uppercase tracking-wide mb-3">Personal Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-medium text-slate-300">First Name <span class="text-rose-300">*</span></label>
                            <input type="text" name="first_name" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Last Name <span class="text-rose-300">*</span></label>
                            <input type="text" name="last_name" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Email</label>
                            <input type="email" name="email" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Phone <span class="text-rose-300">*</span></label>
                            <input type="text" name="phone_number" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Alternate Phone</label>
                            <input type="text" name="alternate_phone_number" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Date of Birth <span class="text-rose-300">*</span></label>
                            <input type="date" name="date_of_birth" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Gender <span class="text-rose-300">*</span></label>
                            <select name="gender" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                                <option value="" class="text-slate-900">Select</option>
                                <option value="Male" class="text-slate-900">Male</option>
                                <option value="Female" class="text-slate-900">Female</option>
                                <option value="Other" class="text-slate-900">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Marital Status <span class="text-rose-300">*</span></label>
                            <select name="marital_status" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                                <option value="" class="text-slate-900">Select</option>
                                <option value="Single" class="text-slate-900">Single</option>
                                <option value="Married" class="text-slate-900">Married</option>
                                <option value="Divorced" class="text-slate-900">Divorced</option>
                                <option value="Widowed" class="text-slate-900">Widowed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Current Location <span class="text-rose-300">*</span></label>
                            <input type="text" name="location" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Preferred Locations <span class="text-rose-300">*</span></label>
                            <input type="text" name="preferred_locations" required placeholder="e.g. Mumbai, Pune" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300">Languages Spoken</label>
                            <input type="text" name="languages_spoken" placeholder="e.g. English, Hindi" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-blue-100 uppercase tracking-wide mb-3 border-t border-white/10 pt-4">Professional Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300">Key Skills (Comma Separated)</label>
                            <input type="text" name="skills" placeholder="Java, Python, Sales, Marketing..." class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Education Level <span class="text-rose-300">*</span></label>
                            <input type="text" name="education_level" required placeholder="e.g. Under Graduate" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Qualification / Degree <span class="text-rose-300">*</span></label>
                            <input type="text" name="qualification_degree" required placeholder="e.g. B.Tech" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Specialization <span class="text-rose-300">*</span></label>
                            <input type="text" name="specialization" required placeholder="e.g. Computer Science" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Experience Status <span class="text-rose-300">*</span></label>
                            <select name="experience_status" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                                <option value="Experienced" class="text-slate-900">Experienced</option>
                                <option value="Fresher" class="text-slate-900">Fresher</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Total Experience (Years) <span class="text-rose-300">*</span></label>
                            <input type="number" name="total_experience_years" required min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Total Experience (Months) <span class="text-rose-300">*</span></label>
                            <input type="number" name="total_experience_months" required min="0" max="11" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Current Company <span class="text-rose-300">*</span></label>
                            <input type="text" name="current_company" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Current Designation <span class="text-rose-300">*</span></label>
                            <input type="text" name="current_designation" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Current CTC <span class="text-rose-300">*</span></label>
                            <input type="text" name="current_ctc" required placeholder="Annual CTC" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Expected CTC <span class="text-rose-300">*</span></label>
                            <input type="text" name="expected_ctc" required placeholder="Annual CTC" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Notice Period <span class="text-rose-300">*</span></label>
                            <input type="text" name="notice_period" required placeholder="e.g. 15 Days, Immediate" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300">Job Interest <span class="text-rose-300">*</span></label>
                            <input type="text" name="job_interest" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-300">Preferred Job Role</label>
                            <input type="text" name="job_role_preference" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-800 text-white text-sm">
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-blue-100 uppercase tracking-wide mb-3 border-t border-white/10 pt-4">Resume</h4>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-slate-300">Upload Resume (PDF/DOC)</label>
                        <input type="file" name="resume" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                    </div>

                    <div id="modalErrors" class="p-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 text-sm rounded mb-2 hidden"></div>
                </form>
            </div>

            <div class="bg-slate-950/60 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-white/10">
                <button type="button" onclick="submitQuickAdd()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-base font-medium text-white hover:from-blue-600 hover:to-indigo-600 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Candidate
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-white/20 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-slate-200 hover:bg-white/10 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('candidateSearch').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const candidates = document.querySelectorAll('.candidate-item');

        candidates.forEach(candidate => {
            const name = candidate.querySelector('.search-name').textContent.toLowerCase();
            const skills = candidate.querySelector('.search-skills').textContent.toLowerCase();

            if (name.includes(searchTerm) || skills.includes(searchTerm)) {
                candidate.style.display = 'flex';
            } else {
                candidate.style.display = 'none';
            }
        });
    });

    const modal = document.getElementById('addCandidateModal');
    const modalErrors = document.getElementById('modalErrors');

    function openModal() {
        modal.classList.remove('hidden');
        modalErrors.classList.add('hidden');
        modalErrors.innerHTML = '';
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function submitQuickAdd() {
        const form = document.getElementById('quickAddForm');
        const formData = new FormData(form);
        const submitBtn = document.querySelector('button[onclick="submitQuickAdd()"]');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';

        fetch("{{ route('partner.candidates.store') }}", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const candidate = data.candidate;
                const newItem = `
                    <label class="candidate-item flex items-start p-3 border border-emerald-400/30 rounded-xl hover:bg-white/5 cursor-pointer transition relative bg-emerald-500/10">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="candidate_ids[]" value="${candidate.id}" checked class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-white/30 rounded bg-slate-900/40">
                        </div>
                        <div class="ml-3 text-sm">
                            <div class="font-bold text-white search-name">${candidate.first_name} ${candidate.last_name}</div>
                            <div class="text-slate-300 text-xs">${candidate.email}</div>
                            <div class="text-slate-300 text-xs mt-1 search-skills">
                                <i class="fa-solid fa-code text-slate-300 mr-1"></i> ${candidate.skills || 'No skills listed'}
                            </div>
                        </div>
                    </label>
                `;

                document.getElementById('candidateList').insertAdjacentHTML('afterbegin', newItem);
                form.reset();
                closeModal();
            } else {
                modalErrors.classList.remove('hidden');
                let errorMsg = data.message || 'Error saving candidate.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                modalErrors.innerHTML = errorMsg;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalErrors.classList.remove('hidden');
            modalErrors.innerHTML = 'An unexpected error occurred. Please check your inputs.';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Save Candidate';
        });
    }
</script>
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit Application') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <form action="{{ route('partner.jobs.submit', $job->id) }}" method="POST" id="applicationForm">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $job->title }}</h1>
                            <p class="text-lg text-gray-600 mb-4">{{ $job->company_name }} - {{ $job->location }}</p>
                            
                            <div class="flex items-center gap-4 mb-6">
                                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded">
                                    {{ $job->salary ?? 'Salary Not Disclosed' }}
                                </span>
                                <span class="bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded">
                                    {{ $job->job_type }}
                                </span>
                            </div>

                            <hr class="my-6 border-gray-100">

                            <h3 class="text-lg font-bold text-gray-800 mb-3">Job Description</h3>
                            <div class="prose max-w-none text-gray-600 mb-6">
                                {!! nl2br(e($job->description)) !!}
                            </div>

                            <h3 class="text-lg font-bold text-gray-800 mb-3">Requirements</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
                                <div><span class="font-semibold">Experience:</span> {{ $job->experienceLevel->name ?? 'N/A' }}</div>
                                <div><span class="font-semibold">Education:</span> {{ $job->educationLevel->name ?? 'N/A' }}</div>
                                <div><span class="font-semibold">Skills:</span> {{ $job->skills_required ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white shadow-lg rounded-xl border border-gray-100 sticky top-6 overflow-hidden flex flex-col h-[calc(100vh-100px)]">
                            
                            <div class="p-4 bg-gray-50 border-b border-gray-200 z-10">
                                <h3 class="font-bold text-gray-800 mb-3">Select Candidates</h3>
                                
                                <div class="relative mb-3">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" id="candidateSearch" placeholder="Search by name or skill..." 
                                           class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <button type="button" onclick="openModal()" class="w-full flex justify-center items-center px-4 py-2 border border-dashed border-indigo-300 rounded-md text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100">
                                    <i class="fa-solid fa-plus mr-2"></i> Add New Candidate
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="candidateList">
                                @forelse($candidates as $candidate)
                                    <label class="candidate-item flex items-start p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition relative">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="candidate_ids[]" value="{{ $candidate->id }}" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <div class="font-bold text-gray-900 search-name">{{ $candidate->first_name }} {{ $candidate->last_name }}</div>
                                            <div class="text-gray-500 text-xs">{{ $candidate->email }}</div>
                                            <div class="text-gray-500 text-xs mt-1 search-skills">
                                                <i class="fa-solid fa-code text-gray-400 mr-1"></i> {{ Str::limit($candidate->skills, 40) }}
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-center py-4 text-gray-500 text-sm">
                                        No candidates found in your pool.
                                    </div>
                                @endforelse
                            </div>

                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none transition ease-in-out duration-150">
                                    Submit Selected
                                </button>
                                <a href="{{ route('partner.jobs') }}" class="block text-center mt-3 text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                            </div>

                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <div id="addCandidateModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[85vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Add New Candidate</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="quickAddForm" enctype="multipart/form-data">
                        @csrf
                        
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Personal Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="phone_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Gender</label>
                                <select name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                             <div>
                                <label class="block text-xs font-medium text-gray-700">Location</label>
                                <input type="text" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Languages Spoken</label>
                                <input type="text" name="languages_spoken" placeholder="e.g. English, Hindi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 border-t pt-4">Professional Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700">Key Skills (Comma Separated)</label>
                                <input type="text" name="skills" placeholder="Java, Python, Sales, Marketing..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Highest Education</label>
                                <input type="text" name="education_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Experience Status</label>
                                <select name="experience_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="Experienced">Experienced</option>
                                    <option value="Fresher">Fresher</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Current/Expected CTC</label>
                                <input type="number" name="expected_ctc" placeholder="Annual CTC" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Notice Period</label>
                                <input type="text" name="notice_period" placeholder="e.g. 15 Days, Immediate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Preferred Job Role</label>
                                <input type="text" name="job_role_preference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                             <div>
                                <label class="block text-xs font-medium text-gray-700">Job Interest</label>
                                <input type="text" name="job_interest" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>

                         <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 border-t pt-4">Resume</h4>
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-700">Upload Resume (PDF/DOC)</label>
                            <input type="file" name="resume" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div id="modalErrors" class="p-3 bg-red-50 text-red-600 text-sm rounded mb-2 hidden"></div>
                    </form>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                    <button type="button" onclick="submitQuickAdd()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save Candidate
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Search Filter Logic
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

        // 2. Modal Logic
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

        // 3. AJAX Quick Add Logic
        function submitQuickAdd() {
            const form = document.getElementById('quickAddForm');
            const formData = new FormData(form);
            const submitBtn = document.querySelector('button[onclick="submitQuickAdd()"]');
            
            // Loading State
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
                    // Create new candidate list item HTML
                    const candidate = data.candidate;
                    const newItem = `
                        <label class="candidate-item flex items-start p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition relative bg-green-50 border-green-200">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="candidate_ids[]" value="${candidate.id}" checked class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <div class="font-bold text-gray-900 search-name">${candidate.first_name} ${candidate.last_name}</div>
                                <div class="text-gray-500 text-xs">${candidate.email}</div>
                                <div class="text-gray-500 text-xs mt-1 search-skills">
                                    <i class="fa-solid fa-code text-gray-400 mr-1"></i> ${candidate.skills || 'No skills listed'}
                                </div>
                            </div>
                        </label>
                    `;

                    // Prepend to list
                    document.getElementById('candidateList').insertAdjacentHTML('afterbegin', newItem);
                    
                    // Reset and Close
                    form.reset();
                    closeModal();
                } else {
                    // Handle Validation Errors
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
                // Reset Button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Save Candidate';
            });
        }
    </script>
</x-app-layout>
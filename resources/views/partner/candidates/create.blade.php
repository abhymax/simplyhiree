@extends('layouts.app')

@section('content')
<style>
    /* Reusing the stylish input field styles */
    .input-group { position: relative; margin-top: 1.5rem; }
    .input-field {
        border: 0; border-bottom: 2px solid #d1d5db; outline: 0; font-size: 1rem; color: #111827;
        padding: 7px 0; background: transparent; transition: border-color 0.2s; width: 100%;
    }
    .input-field::placeholder { color: transparent; }
    .input-label {
        position: absolute; top: 0; display: block; transition: 0.2s; font-size: 1rem;
        color: #6b7280; pointer-events: none;
    }
    .input-field:focus ~ .input-label, .input-field:not(:placeholder-shown) ~ .input-label {
        top: -20px; font-size: 0.8rem; color: #1e3a8a; /* Royal Blue */
    }
    .input-field:focus {
        padding-bottom: 6px; border-width: 3px;
        border-image: linear-gradient(to right, #2563eb, #4f46e5); /* Blue to Indigo */
        border-image-slice: 1;
    }
    /* Read-only state styling */
    .input-field[readonly] {
        color: #6b7280; border-bottom: 2px dashed #d1d5db; cursor: not-allowed;
    }
    
    .select-field, .textarea-field, .file-input {
        border: 1px solid #d1d5db; border-radius: 0.375rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .select-field:focus, .textarea-field:focus, .file-input:focus {
        --tw-ring-color: #4f46e5; border-color: #4f46e5;
        box-shadow: 0 0 0 2px var(--tw-ring-color);
    }
    .radio-group label { display: inline-flex; align-items: center; margin-right: 1rem; cursor: pointer; }
</style>

<div class="py-12 bg-gray-100">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            
            <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-700 sm:px-8 flex flex-wrap justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-white">Add New Candidate</h1>
                    <p class="mt-1 text-sm text-blue-100">Create the candidate's online profile.</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-white/20 text-white border border-white/30 backdrop-blur-sm">
                        <i class="fa-solid fa-mobile-screen mr-2"></i> {{ $mobile ?? 'Verified' }}
                    </span>
                </div>
            </div>

            <div class="p-6 md:p-8">
                
                @if ($errors->any())
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Oops! Please review the form for errors.</p>
                         <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('partner.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-6">Basic Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                            <div class="input-group">
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required class="input-field" placeholder="First Name">
                                <label for="first_name" class="input-label">First Name <span class="text-red-500">*</span></label>
                            </div>
                            
                            <div class="input-group">
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required class="input-field" placeholder="Last Name">
                                <label for="last_name" class="input-label">Last Name <span class="text-red-500">*</span></label>
                            </div>

                            <div class="input-group">
                                <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number', $mobile ?? '') }}" readonly class="input-field" placeholder="Phone Number">
                                <label for="phone_number" class="input-label text-blue-600 font-semibold">Phone Number (Verified) <i class="fa-solid fa-check-circle ml-1"></i></label>
                            </div>

                            <div class="input-group">
                                <input type="tel" name="alternate_phone_number" id="alternate_phone_number" value="{{ old('alternate_phone_number') }}" class="input-field" placeholder="Alternate Phone (Optional)">
                                <label for="alternate_phone_number" class="input-label">Alternate Phone (Optional)</label>
                            </div>

                            <div class="input-group">
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="input-field" placeholder="Email ID (Optional)">
                                <label for="email" class="input-label">Email ID (Optional)</label>
                            </div>

                            <div class="input-group">
                                <input type="text" name="location" id="location" value="{{ old('location') }}" required class="input-field" placeholder="Candidate's Location">
                                <label for="location" class="input-label">Candidate's Location <span class="text-red-500">*</span></label>
                            </div>

                             <div class="mt-4 md:mt-0">
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-500 mb-1">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required class="block w-full shadow-sm select-field p-2">
                             </div>

                             <div class="radio-group mt-4 md:mt-0 flex items-center h-full pt-6">
                                <span class="block text-sm font-medium text-gray-500 mr-4">Gender:</span>
                                <label><input type="radio" name="gender" value="Male" {{ old('gender') == 'Male' ? 'checked' : '' }} required class="text-indigo-600 focus:ring-indigo-500"> Male</label>
                                <label><input type="radio" name="gender" value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500"> Female</label>
                                <label><input type="radio" name="gender" value="Other" {{ old('gender') == 'Other' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500"> Other</label>
                             </div>
                        </div>
                    </div>
                    
                     <div>
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-6">Professional Details</h2>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="input-group">
                                <input type="text" name="job_interest" id="job_interest" value="{{ old('job_interest') }}" required class="input-field" placeholder="Job Interest">
                                <label for="job_interest" class="input-label">Job Interest (e.g., Sales Executive) <span class="text-red-500">*</span></label>
                            </div>

                            <div>
                                <label for="education_level" class="block text-sm font-medium text-gray-500 mb-1">Highest Education <span class="text-red-500">*</span></label>
                                <select name="education_level" id="education_level" required class="block w-full shadow-sm select-field p-2.5">
                                    <option value="">Select Education</option>
                                    <option value="Less than 10th" {{ old('education_level') == 'Less than 10th' ? 'selected' : '' }}>Less than 10th</option>
                                    <option value="10th Pass" {{ old('education_level') == '10th Pass' ? 'selected' : '' }}>10th Pass</option>
                                    <option value="12th Pass" {{ old('education_level') == '12th Pass' ? 'selected' : '' }}>12th Pass</option>
                                    <option value="Diploma" {{ old('education_level') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                    <option value="Graduation" {{ old('education_level') == 'Graduation' ? 'selected' : '' }}>Graduation</option>
                                    <option value="Post Graduation" {{ old('education_level') == 'Post Graduation' ? 'selected' : '' }}>Post Graduation</option>
                                    <option value="Doctorate" {{ old('education_level') == 'Doctorate' ? 'selected' : '' }}>Doctorate</option>
                                </select>
                            </div>

                             <div class="radio-group flex items-center">
                                <span class="block text-sm font-medium text-gray-500 mr-4">Experience Status:</span>
                                <label><input type="radio" name="experience_status" value="Experienced" {{ old('experience_status') == 'Experienced' ? 'checked' : '' }} required class="text-indigo-600 focus:ring-indigo-500"> Experienced</label>
                                <label><input type="radio" name="experience_status" value="Fresher" {{ old('experience_status') == 'Fresher' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500"> Fresher</label>
                             </div>

                             <div class="input-group">
                                <input type="number" step="0.01" name="expected_ctc" id="expected_ctc" value="{{ old('expected_ctc') }}" class="input-field" placeholder="Expected CTC">
                                <label for="expected_ctc" class="input-label">Expected CTC (Annual, ₹)</label>
                             </div>

                             <div class="input-group md:col-span-2">
                                <input type="text" name="notice_period" id="notice_period" value="{{ old('notice_period') }}" class="input-field" placeholder="Notice Period">
                                <label for="notice_period" class="input-label">Notice Period (Optional)</label>
                             </div>
                        </div>

                         <div class="mt-6">
                            <label for="job_role_preference" class="block text-sm font-medium text-gray-700 mb-1">Job Role Preference (Optional, comma separated)</label>
                            <textarea name="job_role_preference" id="job_role_preference" rows="2" class="block w-full shadow-sm textarea-field p-2" placeholder="e.g. Sales Manager, Team Lead">{{ old('job_role_preference') }}</textarea>
                        </div>
                        
                         <div class="mt-6">
                            <label for="languages_spoken" class="block text-sm font-medium text-gray-700 mb-1">Languages Candidate Can Speak (Optional, comma separated)</label>
                            <textarea name="languages_spoken" id="languages_spoken" rows="2" class="block w-full shadow-sm textarea-field p-2" placeholder="e.g. English, Hindi, Marathi">{{ old('languages_spoken') }}</textarea>
                        </div>
                        
                         <div class="mt-6">
                            <label for="skills" class="block text-sm font-medium text-gray-700 mb-1">Skills (Optional, comma separated)</label>
                            <textarea name="skills" id="skills" rows="3" class="block w-full shadow-sm textarea-field p-2" placeholder="e.g. Java, Python, Communication, Driving">{{ old('skills') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-6">Resume / CV</h2>
                        <div class="bg-blue-50 p-4 rounded-md border border-blue-100">
                            <label for="resume" class="block text-sm font-medium text-gray-700 mb-2">Upload Resume (PDF, DOC, DOCX - Max 2MB)</label>
                            <input type="file" name="resume" id="resume" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file-input cursor-pointer">
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-x-2 px-8 py-3 border border-transparent text-base font-bold rounded-md shadow-lg text-white bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105 transition duration-300 ease-in-out">
                            <i class="fa-solid fa-user-plus"></i> Register Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<style>
    /* Main background with a subtle geometric pattern */
    .page-background {
        background-color: #f3f4f6;
        background-image: linear-gradient(rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.92)), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23e5e7eb' fill-opacity='0.4'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-जब तक आप चाहें, मैं आपकी मदद कर सकता हूँ।9v9h9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0h1v5h5V0h1v5h5V0h1v5h5V0h1v5h5V0h1v5h5V0h1v5h5V0h1v5h5V0h1v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h4v1h-4v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5h-5v5h-1v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5v-5H6v-1h5V6H5V5h1zm-1 5h-5v5h5v-5zm-1 0h-3v3h3v-3z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .form-card {
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .input-group { position: relative; margin-top: 1.5rem; margin-right: 1.5rem; margin-bottom: 1.5rem;}
    .input-field {
        border: 0; border-bottom: 2px solid #d1d5db; outline: 0; font-size: 1rem; color: #111827;
        padding: 7px 7px; margin 2px 2px; background: transparent; transition: border-color 0.2s; width: 100%;
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
    .select-field, .textarea-field {
        border: 1px solid #d1d5db; border-radius: 0.375rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .select-field:focus, .textarea-field:focus {
        --tw-ring-color: #4f46e5; border-color: #4f46e5;
        box-shadow: 0 0 0 2px var(--tw-ring-color);
    }
    /* Custom Royal Blue for headers */
    .text-royal-blue { color: #1e3a8a; }
</style>

<div class="py-12 page-background">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="form-card overflow-hidden shadow-2xl sm:rounded-lg">
            
            <div class="px-6 py-5 bg-gradient-to-r from-blue-700 to-indigo-800 sm:px-8">
                <h1 class="text-3xl font-bold text-white">Post a New Job</h1>
                <p class="mt-1 text-sm text-blue-100">Fill in the details below to find your next great hire.</p>
            </div>

            <div class="p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Oops! Please review the form for errors.</p>
                    </div>
                @endif

                <form action="{{ route('client.jobs.store') }}" method="POST" class="space-y-12">
                    @csrf

                    <!-- SECTION 1: CORE JOB DETAILS -->
                    <div>
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-royal-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.07a2.25 2.25 0 01-2.25 2.25h-13.5a2.25 2.25 0 01-2.25-2.25v-4.07m18-4.21l-9-6.32-9 6.32m18 0l-9 6.32-9-6.32M2.25 14.15v4.07a2.25 2.25 0 002.25 2.25h13.5a2.25 2.25 0 002.25-2.25v-4.07" /></svg>
                            <h2 class="text-xl font-bold text-royal-blue">Core Job Details</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 mt-4">
                            <div class="input-group"><input type="text" name="title" id="title" value="{{ old('title') }}" required class="input-field" placeholder="Job Title"><label for="title" class="input-label">Job Title</label></div>
                            <div class="input-group"><input type="text" name="location" id="location" value="{{ old('location') }}" required class="input-field" placeholder="Location"><label for="location" class="input-label">Location</label></div>
                            <div class="input-group"><input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required class="input-field" placeholder="Company Name"><label for="company_name" class="input-label">Company Name</label></div>
                            
                            <div><label for="job_type" class="block text-sm font-medium text-gray-500">Job Type</label><select name="job_type" id="job_type" required class="mt-1 block w-full shadow-sm select-field">{{-- Options --}}
                                <option value="Full-time" {{ old('job_type') == 'Full-time' ? 'selected' : '' }}>Full-time</option><option value="Part-time" {{ old('job_type') == 'Part-time' ? 'selected' : '' }}>Part-time</option><option value="Contract" {{ old('job_type') == 'Contract' ? 'selected' : '' }}>Contract</option><option value="Internship" {{ old('job_type') == 'Internship' ? 'selected' : '' }}>Internship</option></select></div>
                            <div class="input-group"><input type="text" name="salary" id="salary" value="{{ old('salary') }}" class="input-field" placeholder="Salary Range (Optional)"><label for="salary" class="input-label">Salary Range (Optional)</label></div>
                            <div><label for="category_id" class="block text-sm font-medium text-gray-500">Job Category</label><select name="category_id" id="category_id" required class="mt-1 block w-full shadow-sm select-field">{{-- Categories --}}
                                <option value="">Select a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
                        </div>
                    </div>

                    <!-- SECTION 2: REQUIREMENTS & LOGISTICS -->
                     <div>
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-royal-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.68c.119-.046.237-.092.355-.137m7.533 2.493a4.125 4.125 0 00-4.122-4.122A4.125 4.125 0 0015 15.128c0 1.113.285 2.16.786 3.07M8.624 15.128a4.125 4.125 0 00-4.122-4.122A4.125 4.125 0 000 15.128c0 1.113.285 2.16.786 3.07M8.624 21a12.318 12.318 0 004.755-1.002L11.964 19.4c-1.07.388-2.235.604-3.46.604a11.916 11.916 0 01-6.224-1.957l.001-.109a6.375 6.375 0 0111.964-4.68c.119-.046.237-.092.355-.137m-8.119-4.112A4.125 4.125 0 008.624 7.128c1.113 0 2.16.285 3.07.786A4.125 4.125 0 0015.128 11c1.113 0 2.16.285 3.07.786A4.125 4.125 0 0021.5 11c.475 0 .937.067 1.375.195m-15.048-.02A12.318 12.318 0 018.624 3c2.331 0 4.512.645 6.374 1.766l.001.109a6.375 6.375 0 00-11.964 4.68c-.119.046-.237.092-.355.137" /></svg>
                            <h2 class="text-xl font-bold text-royal-blue">Candidate Requirements</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-8 mt-4">
                            <div><label for="experience_level_id" class="block text-sm font-medium text-gray-500">Experience Required</label><select name="experience_level_id" id="experience_level_id" required class="mt-1 block w-full shadow-sm select-field"><option value="">Select experience level</option>@foreach($experienceLevels as $level)<option value="{{ $level->id }}" {{ old('experience_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>@endforeach</select></div>
                            <div><label for="education_level_id" class="block text-sm font-medium text-gray-500">Education Level</label><select name="education_level_id" id="education_level_id" required class="mt-1 block w-full shadow-sm select-field"><option value="">Select education level</option>@foreach($educationLevels as $level)<option value="{{ $level->id }}" {{ old('education_level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>@endforeach</select></div>
                            <div class="input-group"><input type="number" name="openings" id="openings" value="{{ old('openings') }}" class="input-field" placeholder="Number of Openings"><label for="openings" class="input-label">Number of Openings</label></div>
                            <div class="input-group"><input type="number" name="min_age" id="min_age" value="{{ old('min_age') }}" class="input-field" placeholder="Minimum Age"><label for="min_age" class="input-label">Minimum Age</label></div>
                            <div class="input-group"><input type="number" name="max_age" id="max_age" value="{{ old('max_age') }}" class="input-field" placeholder="Maximum Age"><label for="max_age" class="input-label">Maximum Age</label></div>
                            <div><label for="gender_preference" class="block text-sm font-medium text-gray-500">Gender Preference</label><select name="gender_preference" id="gender_preference" class="mt-1 block w-full shadow-sm select-field"><option value="Any" {{ old('gender_preference') == 'Any' ? 'selected' : '' }}>Any</option><option value="Male" {{ old('gender_preference') == 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ old('gender_preference') == 'Female' ? 'selected' : '' }}>Female</option></select></div>
                            <div class="input-group"><input type="text" name="category" id="category" value="{{ old('category') }}" class="input-field" placeholder="Job Category (e.g., Sales)"><label for="category" class="input-label">Job Category (e.g., Sales)</label></div>
                             <div><label for="job_type_tags" class="block text-sm font-medium text-gray-500">Job Tags (e.g., Walkin, New)</label><input type="text" name="job_type_tags" id="job_type_tags" value="{{ old('job_type_tags') }}" class="mt-1 block w-full shadow-sm select-field" placeholder="Comma-separated, e.g., Walkin, New"></div>
                            <div><label for="application_deadline" class="block text-sm font-medium text-gray-500">Application Deadline</label><input type="date" name="application_deadline" id="application_deadline" value="{{ old('application_deadline') }}" required class="mt-1 block w-full shadow-sm select-field"></div>
                        </div>

                        <!-- Walk-in Details -->
                        <div class="mt-6" x-data="{ isWalkin: {{ old('is_walkin') ? 'true' : 'false' }} }">
                            <div class="flex items-center">
                                <input id="is_walkin" name="is_walkin" type="checkbox" x-model="isWalkin" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="is_walkin" class="ml-2 block text-sm text-gray-900">
                                    Is this a Walk-in Interview?
                                </label>
                            </div>
                            <div x-show="isWalkin" class="mt-4">
                                <label for="interview_slot" class="block text-sm font-medium text-gray-700">Interview Slot</label>
                                <input type="datetime-local" id="interview_slot" name="interview_slot" value="{{ old('interview_slot') }}" class="mt-1 block w-full shadow-sm select-field">
                            </div>
                        </div>

                        <div class="mt-6"><label for="skills_required" class="block text-sm font-medium text-gray-700">Skills Required (Comma separated)</label><textarea name="skills_required" id="skills_required" rows="3" required class="mt-1 block w-full shadow-sm textarea-field">{{ old('skills_required') }}</textarea></div>
                    </div>
                    
                    <!-- SECTION 3: DESCRIPTION & COMPANY WEBSITE -->
                    <div>
                        <div class="flex items-center space-x-3">
                            <svg class="h-6 w-6 text-royal-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6.375M9 12h6.375m-6.375 5.25h6.375M5.25 6h.008v.008H5.25V6zm.75 0h.008v.008H6V6zm.75 0h.008v.008H6.75V6zm.75 0h.008v.008H7.5V6zm.75 0h.008v.008H8.25V6zm5.25 0h.008v.008H13.5V6zm.75 0h.008v.008H14.25V6zm.75 0h.008v.008H15V6zm.75 0h.008v.008H15.75V6zm.75 0h.008v.008H16.5V6zm-11.25 5.25h.008v.008H5.25v-.008zm.75 0h.008v.008H6v-.008zm.75 0h.008v.008H6.75v-.008zm.75 0h.008v.008H7.5v-.008zm.75 0h.008v.008H8.25v-.008zm5.25 0h.008v.008H13.5v-.008zm.75 0h.008v.008H14.25v-.008zm.75 0h.008v.008H15v-.008zm.75 0h.008v.008H15.75v-.008zm.75 0h.008v.008H16.5v-.008zm-11.25 5.25h.008v.008H5.25v-.008zm.75 0h.008v.008H6v-.008zm.75 0h.008v.008H6.75v-.008zm.75 0h.008v.008H7.5v-.008zm.75 0h.008v.008H8.25v-.008zm5.25 0h.008v.008H13.5v-.008zm.75 0h.008v.008H14.25v-.008zm.75 0h.008v.008H15v-.008zm.75 0h.008v.008H15.75v-.008zm.75 0h.008v.008H16.5v-.008z" /></svg>
                            <h2 class="text-xl font-bold text-royal-blue">Description & Company Details</h2>
                        </div>
                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Job Description</label>
                            <textarea name="description" id="description" rows="6" required class="mt-1 block w-full shadow-sm textarea-field">{{ old('description') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 mt-6">
                            <div class="input-group md:col-span-2"><input type="url" name="company_website" id="company_website" value="{{ old('company_website') }}" class="input-field" placeholder="Company Website (Optional)"><label for="company_website" class="input-label">Company Website (Optional)</label></div>
                        </div>
                    </div>
                    
                    <!-- SUBMIT BUTTON -->
                    <div class="mt-10 pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-x-2 px-8 py-3 border border-transparent text-base font-bold rounded-md shadow-lg bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105 transition duration-300 ease-in-out">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            Post This Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Post a New Job') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <p class="font-bold">Please fix the following errors:</p>
                    <ul class="list-disc ml-5 mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                
                <form action="{{ route('client.jobs.store') }}" method="POST">
                    @csrf

                    {{-- 1. Job Specification --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Job Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Senior Accountant">
                            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Job Type <span class="text-red-500">*</span></label>
                            <select name="job_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Type</option>
                                <option value="Full-time" {{ old('job_type') == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                <option value="Part-time" {{ old('job_type') == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                <option value="Contract" {{ old('job_type') == 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Internship" {{ old('job_type') == 'Internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                            @error('job_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" value="{{ old('location') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Salary / CTC</label>
                            <input type="text" name="salary" value="{{ old('salary') }}" placeholder="e.g. 5-7 LPA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- NEW: MANUAL EXPERIENCE RANGE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Experience Range (Years) <span class="text-red-500">*</span></label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_experience" placeholder="Min (e.g. 0)" value="{{ old('min_experience') }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('min_experience') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_experience" placeholder="Max (e.g. 5)" value="{{ old('max_experience') }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @error('max_experience') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Education <span class="text-red-500">*</span></label>
                            <select name="education_level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}" {{ old('education_level_id') == $edu->id ? 'selected' : '' }}>{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" min="{{ date('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Openings</label>
                            <input type="number" name="openings" value="{{ old('openings', 1) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Job Description <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Skills Required (Comma separated)</label>
                        <input type="text" name="skills_required" value="{{ old('skills_required') }}" placeholder="e.g. PHP, Laravel, MySQL" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Company Website (Optional)</label>
                        <input type="url" name="company_website" value="{{ old('company_website') }}" placeholder="https://example.com" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('client.dashboard') }}" class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded shadow hover:bg-gray-300 transition mr-4">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded shadow hover:bg-blue-700 transition">
                            Post Job
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
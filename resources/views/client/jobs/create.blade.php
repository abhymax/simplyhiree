@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto">

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

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold mt-3">Post a New Job</h1>
            </div>

            <div class="p-6">
                <form action="{{ route('client.jobs.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-blue-100">Job Title <span class="text-rose-300">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" placeholder="e.g. Senior Accountant">
                            @error('title') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Category <span class="text-rose-300">*</span></label>
                            <select name="category_id" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }} class="text-slate-900">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Job Type <span class="text-rose-300">*</span></label>
                            <select name="job_type" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                <option value="" class="text-slate-900">Select Type</option>
                                <option value="Full-time" {{ old('job_type') == 'Full-time' ? 'selected' : '' }} class="text-slate-900">Full-time</option>
                                <option value="Part-time" {{ old('job_type') == 'Part-time' ? 'selected' : '' }} class="text-slate-900">Part-time</option>
                                <option value="Contract" {{ old('job_type') == 'Contract' ? 'selected' : '' }} class="text-slate-900">Contract</option>
                                <option value="Internship" {{ old('job_type') == 'Internship' ? 'selected' : '' }} class="text-slate-900">Internship</option>
                            </select>
                            @error('job_type') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Location <span class="text-rose-300">*</span></label>
                            <input type="text" name="location" value="{{ old('location') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                            @error('location') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Salary / CTC</label>
                            <input type="text" name="salary" value="{{ old('salary') }}" placeholder="e.g. 5-7 LPA" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Experience Range (Years) <span class="text-rose-300">*</span></label>
                            <div class="flex space-x-2">
                                <div class="w-1/2">
                                    <input type="number" name="min_experience" placeholder="Min" value="{{ old('min_experience') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" required>
                                    @error('min_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_experience" placeholder="Max" value="{{ old('max_experience') }}" min="0" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" required>
                                    @error('max_experience') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Education <span class="text-rose-300">*</span></label>
                            <select name="education_level_id" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}" {{ old('education_level_id') == $edu->id ? 'selected' : '' }} class="text-slate-900">{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline') }}" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" min="{{ date('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-blue-100">Total Openings</label>
                            <input type="number" name="openings" value="{{ old('openings', 1) }}" min="1" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-blue-100">Job Description <span class="text-rose-300">*</span></label>
                        <textarea name="description" rows="4" required class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('description') }}</textarea>
                        @error('description') <span class="text-rose-300 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-blue-100">Skills Required (Comma separated)</label>
                        <input type="text" name="skills_required" value="{{ old('skills_required') }}" placeholder="e.g. PHP, Laravel, MySQL" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-blue-100">Company Website (Optional)</label>
                        <input type="url" name="company_website" value="{{ old('company_website') }}" placeholder="https://example.com" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('client.dashboard') }}" class="bg-white/10 border border-white/20 text-slate-100 font-bold py-3 px-6 rounded-xl hover:bg-white/20 transition mr-4">
                            Cancel
                        </a>
                        <button type="submit" class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white font-bold py-3 px-8 rounded-xl hover:from-blue-600 hover:to-indigo-600 transition">
                            Post Job
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
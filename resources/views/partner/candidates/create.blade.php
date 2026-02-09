@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-6 py-6 border-b border-white/10 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Partner Workspace
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold mt-3">Add New Candidate</h1>
                    <p class="text-blue-200 text-sm mt-1">Create candidate profile and upload resume.</p>
                </div>
                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold bg-white/10 border border-white/20 text-white">
                    <i class="fa-solid fa-mobile-screen mr-2"></i> {{ $mobile ?? 'Verified' }}
                </span>
            </div>

            <div class="p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-rose-500/20 border border-rose-400/40 text-rose-100 p-4 rounded-xl">
                        <p class="font-bold">Please fix the errors below:</p>
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
                        <h2 class="text-lg font-bold text-white border-b border-white/10 pb-2 mb-5">Basic Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="First Name *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Last Name *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="tel" name="phone_number" value="{{ old('phone_number', $mobile ?? '') }}" readonly placeholder="Phone Number" class="w-full rounded-xl border border-dashed border-white/30 bg-slate-900/40 text-slate-300 px-4 py-3">
                            <input type="tel" name="alternate_phone_number" value="{{ old('alternate_phone_number') }}" placeholder="Alternate Phone (Optional)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (Optional)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="text" name="location" value="{{ old('location') }}" required placeholder="Candidate Location *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <div>
                                <label class="block text-sm text-blue-100 mb-2">Date of Birth *</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100 mb-2">Gender *</label>
                                <div class="flex gap-4 text-sm pt-2">
                                    <label><input type="radio" name="gender" value="Male" {{ old('gender') == 'Male' ? 'checked' : '' }} required class="mr-1"> Male</label>
                                    <label><input type="radio" name="gender" value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }} class="mr-1"> Female</label>
                                    <label><input type="radio" name="gender" value="Other" {{ old('gender') == 'Other' ? 'checked' : '' }} class="mr-1"> Other</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-white border-b border-white/10 pb-2 mb-5">Professional Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="job_interest" value="{{ old('job_interest') }}" required placeholder="Job Interest *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">

                            <select name="education_level" required class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="" class="text-slate-900">Select Education *</option>
                                <option value="Less than 10th" {{ old('education_level') == 'Less than 10th' ? 'selected' : '' }} class="text-slate-900">Less than 10th</option>
                                <option value="10th Pass" {{ old('education_level') == '10th Pass' ? 'selected' : '' }} class="text-slate-900">10th Pass</option>
                                <option value="12th Pass" {{ old('education_level') == '12th Pass' ? 'selected' : '' }} class="text-slate-900">12th Pass</option>
                                <option value="Diploma" {{ old('education_level') == 'Diploma' ? 'selected' : '' }} class="text-slate-900">Diploma</option>
                                <option value="Graduation" {{ old('education_level') == 'Graduation' ? 'selected' : '' }} class="text-slate-900">Graduation</option>
                                <option value="Post Graduation" {{ old('education_level') == 'Post Graduation' ? 'selected' : '' }} class="text-slate-900">Post Graduation</option>
                                <option value="Doctorate" {{ old('education_level') == 'Doctorate' ? 'selected' : '' }} class="text-slate-900">Doctorate</option>
                            </select>

                            <div>
                                <label class="block text-sm text-blue-100 mb-2">Experience Status *</label>
                                <div class="flex gap-4 text-sm pt-2">
                                    <label><input type="radio" name="experience_status" value="Experienced" {{ old('experience_status') == 'Experienced' ? 'checked' : '' }} required class="mr-1"> Experienced</label>
                                    <label><input type="radio" name="experience_status" value="Fresher" {{ old('experience_status') == 'Fresher' ? 'checked' : '' }} class="mr-1"> Fresher</label>
                                </div>
                            </div>

                            <input type="number" step="0.01" name="expected_ctc" value="{{ old('expected_ctc') }}" placeholder="Expected CTC (Annual, ₹)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="text" name="notice_period" value="{{ old('notice_period') }}" placeholder="Notice Period (Optional)" class="md:col-span-2 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div class="mt-4 space-y-4">
                            <textarea name="job_role_preference" rows="2" placeholder="Job Role Preference (Optional, comma separated)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('job_role_preference') }}</textarea>
                            <textarea name="languages_spoken" rows="2" placeholder="Languages Spoken (Optional, comma separated)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('languages_spoken') }}</textarea>
                            <textarea name="skills" rows="3" placeholder="Skills (Optional, comma separated)" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('skills') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-white border-b border-white/10 pb-2 mb-5">Resume / CV</h2>
                        <input type="file" name="resume" class="block w-full text-sm text-slate-200 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                        <p class="text-xs text-slate-300 mt-2">PDF, DOC, DOCX. Max 2MB.</p>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 transition shadow-lg">
                            <i class="fa-solid fa-user-plus"></i> Register Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
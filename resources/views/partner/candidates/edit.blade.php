@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-5xl mx-auto">
        <div class="mb-5">
            <a href="{{ route('partner.candidates.index') }}" class="inline-flex items-center gap-2 text-blue-200 hover:text-white font-semibold">
                <i class="fa-solid fa-arrow-left"></i> Back to Candidates
            </a>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-6 py-6 border-b border-white/10">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Partner Workspace
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold mt-3">Edit Candidate</h1>
                <p class="text-blue-200 text-sm mt-1">{{ $candidate->first_name }} {{ $candidate->last_name }}</p>
            </div>

            <div class="p-6 md:p-8">
                <form action="{{ route('partner.candidates.update', $candidate->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    <div>
                        <h2 class="text-lg font-bold text-white border-b border-white/10 pb-2 mb-5">Basic Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <input type="text" name="first_name" value="{{ old('first_name', $candidate->first_name) }}" required placeholder="First Name *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                @error('first_name') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="text" name="last_name" value="{{ old('last_name', $candidate->last_name) }}" required placeholder="Last Name *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                @error('last_name') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="email" name="email" value="{{ old('email', $candidate->email) }}" placeholder="Email" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                @error('email') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $candidate->phone_number) }}" required placeholder="Phone Number *" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                @error('phone_number') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <input type="text" name="location" value="{{ old('location', $candidate->location) }}" placeholder="Location" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">

                            <select name="gender" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                <option value="" class="text-slate-900">Select Gender</option>
                                <option value="Male" {{ old('gender', $candidate->gender) == 'Male' ? 'selected' : '' }} class="text-slate-900">Male</option>
                                <option value="Female" {{ old('gender', $candidate->gender) == 'Female' ? 'selected' : '' }} class="text-slate-900">Female</option>
                                <option value="Other" {{ old('gender', $candidate->gender) == 'Other' ? 'selected' : '' }} class="text-slate-900">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-white border-b border-white/10 pb-2 mb-5">Professional Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <select name="experience_status" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                                <option value="Fresher" {{ old('experience_status', $candidate->experience_status) == 'Fresher' ? 'selected' : '' }} class="text-slate-900">Fresher</option>
                                <option value="Experienced" {{ old('experience_status', $candidate->experience_status) == 'Experienced' ? 'selected' : '' }} class="text-slate-900">Experienced</option>
                            </select>

                            <input type="text" name="notice_period" value="{{ old('notice_period', $candidate->notice_period) }}" placeholder="Notice Period" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">
                            <input type="number" name="expected_ctc" value="{{ old('expected_ctc', $candidate->expected_ctc) }}" placeholder="Expected CTC" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">

                            <div>
                                @if($candidate->resume_path)
                                    <div class="text-sm text-emerald-300 mb-2">
                                        Current Resume:
                                        <a href="{{ asset('storage/'.$candidate->resume_path) }}" target="_blank" class="underline hover:text-white">View</a>
                                    </div>
                                @endif
                                <input type="file" name="resume" class="block w-full text-sm text-slate-200 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                            </div>

                            <div class="md:col-span-2">
                                <textarea name="skills" rows="3" placeholder="Skills" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-3">{{ old('skills', $candidate->skills) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 transition shadow-lg">
                            Update Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
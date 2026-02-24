@extends('layouts.app')

@section('content')
<style>
    .fx-card { transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .fx-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(14,165,233,.22); border-color: rgba(255,255,255,.30); }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[22rem] h-[22rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-6 border-b border-white/20 pb-6">
            <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
            </a>
            <h1 class="text-4xl font-extrabold tracking-tight">My Professional Profile</h1>
            <p class="text-blue-100 mt-1">Keep your profile updated to improve hiring chances.</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 px-4 py-3 rounded-xl mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('candidate.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="fx-card bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Core Details</h3>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        @error('name') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        @error('email') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $profile->phone_number) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        @error('phone_number') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Current Location</label>
                        <input type="text" name="location" value="{{ old('location', $profile->location) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        @error('location') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($profile->date_of_birth)->format('Y-m-d')) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        @error('date_of_birth') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Gender</label>
                        <select name="gender" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                            <option value="" class="text-slate-900">Select</option>
                            <option value="Male" {{ (old('gender', $profile->gender) == 'Male') ? 'selected' : '' }} class="text-slate-900">Male</option>
                            <option value="Female" {{ (old('gender', $profile->gender) == 'Female') ? 'selected' : '' }} class="text-slate-900">Female</option>
                            <option value="Other" {{ (old('gender', $profile->gender) == 'Other') ? 'selected' : '' }} class="text-slate-900">Other</option>
                        </select>
                        @error('gender') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Experience Status</label>
                        <select name="experience_status" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                            <option value="Fresher" {{ (old('experience_status', $profile->experience_status) == 'Fresher') ? 'selected' : '' }} class="text-slate-900">Fresher</option>
                            <option value="Experienced" {{ (old('experience_status', $profile->experience_status) == 'Experienced') ? 'selected' : '' }} class="text-slate-900">Experienced</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Skills (Comma Separated)</label>
                        <textarea name="skills" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white h-24" placeholder="PHP, Laravel, React, SQL...">{{ old('skills', $profile->skills) }}</textarea>
                        @error('skills') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="fx-card bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Career & Resume</h3>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Current/Last Role</label>
                        <input type="text" name="current_role" value="{{ old('current_role', $profile->current_role) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Expected CTC (Annual)</label>
                        <input type="number" name="expected_ctc" value="{{ old('expected_ctc', $profile->expected_ctc) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Notice Period</label>
                        <input type="text" name="notice_period" value="{{ old('notice_period', $profile->notice_period) }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white" placeholder="e.g. 30 Days">
                    </div>

                    <div class="mb-4">
                        <label class="block text-blue-100 text-sm font-bold mb-2">Resume (PDF/DOC)</label>
                        @if($profile->resume_path)
                            <div class="text-sm text-emerald-300 mb-2">
                                Current Resume:
                                <a href="{{ asset('storage/'.$profile->resume_path) }}" target="_blank" class="underline hover:text-white">View File</a>
                            </div>
                        @endif
                        <input type="file" name="resume" class="block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                        @error('resume') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>

            <div class="mt-6">
                <button type="submit" class="fx-btn bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-2.5 px-6 rounded-xl">
                    Save Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

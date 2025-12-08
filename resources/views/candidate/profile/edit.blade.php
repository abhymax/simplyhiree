@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6">My Professional Profile</h2>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('candidate.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $profile->phone_number) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                @error('phone_number') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Current Location</label>
                                <input type="text" name="location" value="{{ old('location', $profile->location) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Experience Status</label>
                                <select name="experience_status" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="Fresher" {{ (old('experience_status', $profile->experience_status) == 'Fresher') ? 'selected' : '' }}>Fresher</option>
                                    <option value="Experienced" {{ (old('experience_status', $profile->experience_status) == 'Experienced') ? 'selected' : '' }}>Experienced</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Skills (Comma Separated)</label>
                                <textarea name="skills" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight h-24" placeholder="PHP, Laravel, React, SQL...">{{ old('skills', $profile->skills) }}</textarea>
                                @error('skills') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Current/Last Role</label>
                                <input type="text" name="current_role" value="{{ old('current_role', $profile->current_role) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Expected CTC (Annual)</label>
                                <input type="number" name="expected_ctc" value="{{ old('expected_ctc', $profile->expected_ctc) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Notice Period</label>
                                <input type="text" name="notice_period" value="{{ old('notice_period', $profile->notice_period) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" placeholder="e.g. 30 Days">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Resume (PDF/DOC)</label>
                                @if($profile->resume_path)
                                    <div class="text-sm text-green-600 mb-2">
                                        Current Resume: <a href="{{ asset('storage/'.$profile->resume_path) }}" target="_blank" class="underline">View File</a>
                                    </div>
                                @endif
                                <input type="file" name="resume" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                                @error('resume') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
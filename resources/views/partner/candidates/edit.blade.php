@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Edit Candidate: {{ $candidate->first_name }} {{ $candidate->last_name }}</h2>
                    <a href="{{ route('partner.candidates.index') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Back to List
                    </a>
                </div>

                <form action="{{ route('partner.candidates.update', $candidate->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">First Name *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $candidate->first_name) }}" class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:shadow-outline" required>
                            @error('first_name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Last Name *</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $candidate->last_name) }}" class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:shadow-outline" required>
                            @error('last_name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $candidate->email) }}" class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:shadow-outline">
                            @error('email') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Phone Number *</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $candidate->phone_number) }}" class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:shadow-outline" required>
                            @error('phone_number') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                            <input type="text" name="location" value="{{ old('location', $candidate->location) }}" class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Gender</label>
                            <select name="gender" class="shadow border rounded w-full py-2 px-3">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $candidate->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $candidate->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $candidate->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Professional Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                         <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Experience Status</label>
                            <select name="experience_status" class="shadow border rounded w-full py-2 px-3">
                                <option value="Fresher" {{ old('experience_status', $candidate->experience_status) == 'Fresher' ? 'selected' : '' }}>Fresher</option>
                                <option value="Experienced" {{ old('experience_status', $candidate->experience_status) == 'Experienced' ? 'selected' : '' }}>Experienced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Notice Period</label>
                            <input type="text" name="notice_period" value="{{ old('notice_period', $candidate->notice_period) }}" class="shadow border rounded w-full py-2 px-3" placeholder="e.g. 15 Days">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Expected CTC</label>
                            <input type="number" name="expected_ctc" value="{{ old('expected_ctc', $candidate->expected_ctc) }}" class="shadow border rounded w-full py-2 px-3">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Resume</label>
                            @if($candidate->resume_path)
                                <div class="text-sm text-green-600 mb-2">
                                    Current Resume: <a href="{{ asset('storage/'.$candidate->resume_path) }}" target="_blank" class="underline">View</a>
                                </div>
                            @endif
                            <input type="file" name="resume" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Skills</label>
                            <textarea name="skills" class="shadow border rounded w-full py-2 px-3" rows="3">{{ old('skills', $candidate->skills) }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                            Update Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('partner.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Account</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <div class="bg-white shadow rounded-lg p-6 text-center h-full">
                    <div class="relative inline-block">
                        @if($profile->profile_picture_path)
                            <img src="{{ asset('storage/'.$profile->profile_picture_path) }}" alt="Profile" class="h-32 w-32 rounded-full object-cover mx-auto border-4 border-gray-200">
                        @else
                            <div class="h-32 w-32 rounded-full bg-blue-100 flex items-center justify-center mx-auto text-blue-500 text-4xl border-4 border-white shadow">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        @endif
                        <label for="profile_picture" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 shadow-lg" title="Change Photo">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" name="profile_picture" id="profile_picture" class="hidden">
                        </label>
                    </div>
                    
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Company Type</label>
                        <select name="company_type" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 w-full text-center">
                            <option value="Freelancer/Individual" {{ old('company_type', $profile->company_type) == 'Freelancer/Individual' ? 'selected' : '' }}>Freelancer/Individual</option>
                            <option value="Company" {{ old('company_type', $profile->company_type) == 'Company' ? 'selected' : '' }}>Company</option>
                        </select>
                    </div>

                    <div class="mt-4 space-y-3 text-left">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Website</label>
                            <input type="url" name="website" placeholder="https://yourwebsite.com" value="{{ old('website', $profile->website) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Est. Year</label>
                            <input type="number" name="establishment_year" placeholder="e.g. 2020" value="{{ old('establishment_year', $profile->establishment_year) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                        <i class="fa-solid fa-share-nodes mr-2 text-blue-500"></i> Social Media
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">LinkedIn</label>
                            <input type="url" name="linkedin_url" placeholder="https://linkedin.com/in/..." value="{{ old('linkedin_url', $profile->linkedin_url) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Facebook</label>
                            <input type="url" name="facebook_url" placeholder="https://facebook.com/..." value="{{ old('facebook_url', $profile->facebook_url) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Twitter (X)</label>
                            <input type="url" name="twitter_url" placeholder="https://twitter.com/..." value="{{ old('twitter_url', $profile->twitter_url) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Instagram</label>
                            <input type="url" name="instagram_url" placeholder="https://instagram.com/..." value="{{ old('instagram_url', $profile->instagram_url) }}" class="mt-1 block w-full text-sm rounded-md border-gray-300">
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                        <i class="fa-solid fa-briefcase mr-2 text-gray-500"></i> Preferences
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categories & Skills</label>
                        <p class="text-xs text-gray-500 mb-2">Sectors you specialize in (e.g. IT, Sales).</p>
                        <textarea name="preferred_categories" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('preferred_categories', $profile->preferred_categories) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Locations</label>
                        <input type="text" name="preferred_locations" placeholder="e.g. Mumbai, Delhi, Remote" value="{{ old('preferred_locations', $profile->preferred_locations) }}" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full md:col-span-2 xl:col-span-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                        <i class="fa-solid fa-map-location-dot mr-2 text-green-600"></i> Bio & Location
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea name="bio" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $profile->address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Office Hours</label>
                        <input type="text" name="working_hours" placeholder="e.g. Mon-Fri 9AM - 6PM" value="{{ old('working_hours', $profile->working_hours) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 md:col-span-2 xl:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 border-b pb-2 text-indigo-700">
                        <i class="fa-solid fa-lock mr-2"></i> Financial & Compliance
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-gray-50 rounded border">
                            <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">Bank Details</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <input type="text" name="beneficiary_name" placeholder="Beneficiary Name" value="{{ old('beneficiary_name', $profile->beneficiary_name) }}" class="rounded-md border-gray-300 text-sm" required>
                                <input type="text" name="ifsc_code" placeholder="IFSC Code" value="{{ old('ifsc_code', $profile->ifsc_code) }}" class="rounded-md border-gray-300 text-sm" required>
                                <input type="text" name="account_number" placeholder="Account Number" value="{{ old('account_number', $profile->account_number) }}" class="rounded-md border-gray-300 text-sm" required>
                                <input type="text" name="account_number_confirmation" placeholder="Confirm Account No." value="{{ old('account_number', $profile->account_number) }}" class="rounded-md border-gray-300 text-sm" required>
                                <select name="account_type" class="rounded-md border-gray-300 text-sm" required>
                                    <option value="Savings" {{ old('account_type', $profile->account_type) == 'Savings' ? 'selected' : '' }}>Savings</option>
                                    <option value="Current" {{ old('account_type', $profile->account_type) == 'Current' ? 'selected' : '' }}>Current</option>
                                </select>
                                <div>
                                    <label class="text-xs text-gray-500">Cancelled Cheque</label>
                                    <input type="file" name="cancelled_cheque" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded border">
                                <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">PAN Details</h4>
                                <input type="text" name="pan_name" placeholder="Name on PAN" value="{{ old('pan_name', $profile->pan_name) }}" class="w-full rounded-md border-gray-300 text-sm mb-2" required>
                                <input type="text" name="pan_number" placeholder="PAN Number" value="{{ old('pan_number', $profile->pan_number) }}" class="w-full rounded-md border-gray-300 text-sm mb-2" required>
                                <label class="text-xs text-gray-500">Upload PAN</label>
                                <input type="file" name="pan_card" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-green-50 file:text-green-700">
                            </div>

                            <div class="p-4 bg-gray-50 rounded border">
                                <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase">GST Details</h4>
                                <input type="text" name="gst_number" placeholder="GST Number" value="{{ old('gst_number', $profile->gst_number) }}" class="w-full rounded-md border-gray-300 text-sm mb-2" required>
                                <label class="text-xs text-gray-500">Upload Certificate</label>
                                <input type="file" name="gst_certificate" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-purple-50 file:text-purple-700">
                            </div>
                        </div>
                    </div>
                </div>

            </div> <div class="mt-8 flex justify-end pb-10">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-10 rounded shadow-lg transform hover:scale-105 transition duration-150 flex items-center text-lg">
                    <i class="fa-solid fa-save mr-2"></i> Update Profile
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
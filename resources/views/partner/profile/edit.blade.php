@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="mb-6 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-rose-500/20 border border-rose-400/40 text-rose-100 p-4 rounded-xl">
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

            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Partner Workspace
                    </span>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3">My Account</h1>
                    <p class="text-blue-200 mt-2 text-sm md:text-base">Manage business, payout and compliance profile.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 text-center h-full">
                    <div class="relative inline-block">
                        @if($profile->profile_picture_path)
                            <img src="{{ asset('storage/'.$profile->profile_picture_path) }}" alt="Profile" class="h-32 w-32 rounded-full object-cover mx-auto border-4 border-white/20">
                        @else
                            <div class="h-32 w-32 rounded-full bg-blue-500/20 flex items-center justify-center mx-auto text-blue-200 text-4xl border-4 border-white/20">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        @endif
                        <label for="profile_picture" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 shadow-lg">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" name="profile_picture" id="profile_picture" class="hidden">
                        </label>
                    </div>

                    <h2 class="mt-4 text-xl font-bold text-white">{{ $user->name }}</h2>

                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-blue-200 uppercase mb-1">Company Type</label>
                        <select name="company_type" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm text-center px-3 py-2">
                            <option value="Freelancer/Individual" {{ old('company_type', $profile->company_type) == 'Freelancer/Individual' ? 'selected' : '' }} class="text-slate-900">Freelancer/Individual</option>
                            <option value="Company" {{ old('company_type', $profile->company_type) == 'Company' ? 'selected' : '' }} class="text-slate-900">Company</option>
                        </select>
                    </div>

                    <div class="mt-4 space-y-3 text-left">
                        <div>
                            <label class="text-xs font-semibold text-blue-200 uppercase">Website</label>
                            <input type="url" name="website" value="{{ old('website', $profile->website) }}" placeholder="https://yourwebsite.com" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-blue-200 uppercase">Est. Year</label>
                            <input type="number" name="establishment_year" value="{{ old('establishment_year', $profile->establishment_year) }}" placeholder="e.g. 2020" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 h-full">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">
                        <i class="fa-solid fa-share-nodes mr-2 text-blue-300"></i> Social Media
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-blue-100">LinkedIn</label>
                            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}" placeholder="https://linkedin.com/in/..." class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-blue-100">Facebook</label>
                            <input type="url" name="facebook_url" value="{{ old('facebook_url', $profile->facebook_url) }}" placeholder="https://facebook.com/..." class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-blue-100">Twitter (X)</label>
                            <input type="url" name="twitter_url" value="{{ old('twitter_url', $profile->twitter_url) }}" placeholder="https://twitter.com/..." class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-blue-100">Instagram</label>
                            <input type="url" name="instagram_url" value="{{ old('instagram_url', $profile->instagram_url) }}" placeholder="https://instagram.com/..." class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 h-full">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">
                        <i class="fa-solid fa-briefcase mr-2 text-blue-300"></i> Preferences
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm text-blue-100 mb-1">Categories & Skills</label>
                        <textarea name="preferred_categories" rows="4" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">{{ old('preferred_categories', $profile->preferred_categories) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-blue-100 mb-1">Preferred Locations</label>
                        <input type="text" name="preferred_locations" value="{{ old('preferred_locations', $profile->preferred_locations) }}" placeholder="e.g. Mumbai, Delhi, Remote" class="w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 h-full md:col-span-2 xl:col-span-1">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">
                        <i class="fa-solid fa-map-location-dot mr-2 text-emerald-300"></i> Bio & Location
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm text-blue-100">Bio</label>
                        <textarea name="bio" rows="3" class="mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-blue-100">Address</label>
                        <textarea name="address" rows="3" class="mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">{{ old('address', $profile->address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-blue-100">Office Hours</label>
                        <input type="text" name="working_hours" value="{{ old('working_hours', $profile->working_hours) }}" placeholder="e.g. Mon-Fri 9AM - 6PM" class="mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white text-sm px-3 py-2">
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 md:col-span-2 xl:col-span-2">
                    <h3 class="text-lg font-bold text-white mb-6 border-b border-white/10 pb-2">
                        <i class="fa-solid fa-lock mr-2"></i> Financial & Compliance
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-slate-900/40 rounded-xl border border-white/10">
                            <h4 class="font-bold text-blue-100 mb-3 text-sm uppercase">Bank Details</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <input type="text" name="beneficiary_name" value="{{ old('beneficiary_name', $profile->beneficiary_name) }}" placeholder="Beneficiary Name" class="rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm px-3 py-2" required>
                                <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code) }}" placeholder="IFSC Code" class="rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm px-3 py-2" required>
                                <input type="text" name="account_number" value="{{ old('account_number', $profile->account_number) }}" placeholder="Account Number" class="rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm px-3 py-2" required>
                                <input type="text" name="account_number_confirmation" value="{{ old('account_number_confirmation', old('account_number', $profile->account_number)) }}" placeholder="Confirm Account No." class="rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm px-3 py-2" required>
                                <select name="account_type" class="rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm px-3 py-2" required>
                                    <option value="Savings" {{ old('account_type', $profile->account_type) == 'Savings' ? 'selected' : '' }} class="text-slate-900">Savings</option>
                                    <option value="Current" {{ old('account_type', $profile->account_type) == 'Current' ? 'selected' : '' }} class="text-slate-900">Current</option>
                                </select>
                                <div>
                                    <label class="text-xs text-slate-300">Cancelled Cheque</label>
                                    <input type="file" name="cancelled_cheque" class="block w-full text-xs text-slate-200 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:bg-blue-500 file:text-white">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 bg-slate-900/40 rounded-xl border border-white/10">
                                <h4 class="font-bold text-blue-100 mb-3 text-sm uppercase">PAN Details</h4>
                                <input type="text" name="pan_name" value="{{ old('pan_name', $profile->pan_name) }}" placeholder="Name on PAN" class="w-full rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm mb-2 px-3 py-2" required>
                                <input type="text" name="pan_number" value="{{ old('pan_number', $profile->pan_number) }}" placeholder="PAN Number" class="w-full rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm mb-2 px-3 py-2" required>
                                <label class="text-xs text-slate-300">Upload PAN</label>
                                <input type="file" name="pan_card" class="block w-full text-xs text-slate-200 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:bg-emerald-500 file:text-white">
                            </div>

                            <div class="p-4 bg-slate-900/40 rounded-xl border border-white/10">
                                <h4 class="font-bold text-blue-100 mb-3 text-sm uppercase">GST Details</h4>
                                <input type="text" name="gst_number" value="{{ old('gst_number', $profile->gst_number) }}" placeholder="GST Number" class="w-full rounded-xl border border-white/20 bg-slate-900/50 text-white text-sm mb-2 px-3 py-2" required>
                                <label class="text-xs text-slate-300">Upload Certificate</label>
                                <input type="file" name="gst_certificate" class="block w-full text-xs text-slate-200 file:mr-2 file:py-2 file:px-3 file:rounded file:border-0 file:bg-violet-500 file:text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end pb-6">
                <button type="submit" class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition">
                    <i class="fa-solid fa-save mr-2"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
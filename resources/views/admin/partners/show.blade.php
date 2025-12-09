<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Partner Profile: ') . $user->name }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <div class="bg-white shadow rounded-lg p-6 text-center h-full">
                    @if($profile && $profile->profile_picture_path)
                        <img src="{{ asset('storage/'.$profile->profile_picture_path) }}" alt="Profile" class="h-32 w-32 rounded-full object-cover mx-auto border-4 border-gray-200">
                    @else
                        <div class="h-32 w-32 rounded-full bg-blue-100 flex items-center justify-center mx-auto text-blue-500 text-4xl border-4 border-white shadow">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    @endif

                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    <p class="mt-2 text-sm font-semibold text-indigo-600 bg-indigo-50 inline-block px-3 py-1 rounded-full">
                        {{ $profile->company_type ?? 'Freelancer/Individual' }}
                    </p>

                    <div class="mt-6 text-left border-t pt-4">
                        <p class="text-sm text-gray-600"><span class="font-bold">Website:</span> <a href="{{ $profile->website ?? '#' }}" class="text-blue-500 hover:underline" target="_blank">{{ $profile->website ?? 'N/A' }}</a></p>
                        <p class="text-sm text-gray-600 mt-2"><span class="font-bold">Est. Year:</span> {{ $profile->establishment_year ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Social Media</h3>
                    <ul class="space-y-3">
                        <li>
                            <i class="fa-brands fa-linkedin text-blue-700 w-6"></i>
                            <a href="{{ $profile->linkedin_url ?? '#' }}" target="_blank" class="{{ $profile->linkedin_url ? 'text-blue-600 hover:underline' : 'text-gray-400 cursor-not-allowed' }}">
                                {{ $profile->linkedin_url ? 'LinkedIn Profile' : 'Not Provided' }}
                            </a>
                        </li>
                        <li>
                            <i class="fa-brands fa-facebook text-blue-600 w-6"></i>
                            <a href="{{ $profile->facebook_url ?? '#' }}" target="_blank" class="{{ $profile->facebook_url ? 'text-blue-600 hover:underline' : 'text-gray-400 cursor-not-allowed' }}">
                                {{ $profile->facebook_url ? 'Facebook Page' : 'Not Provided' }}
                            </a>
                        </li>
                        <li>
                            <i class="fa-brands fa-twitter text-sky-500 w-6"></i>
                            <a href="{{ $profile->twitter_url ?? '#' }}" target="_blank" class="{{ $profile->twitter_url ? 'text-blue-600 hover:underline' : 'text-gray-400 cursor-not-allowed' }}">
                                {{ $profile->twitter_url ? 'Twitter Profile' : 'Not Provided' }}
                            </a>
                        </li>
                        <li>
                            <i class="fa-brands fa-instagram text-pink-600 w-6"></i>
                            <a href="{{ $profile->instagram_url ?? '#' }}" target="_blank" class="{{ $profile->instagram_url ? 'text-blue-600 hover:underline' : 'text-gray-400 cursor-not-allowed' }}">
                                {{ $profile->instagram_url ? 'Instagram Profile' : 'Not Provided' }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Preferences</h3>
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-gray-700">Preferred Categories</h4>
                        <p class="mt-1 text-sm text-gray-600 bg-gray-50 p-3 rounded border border-gray-100 h-24 overflow-y-auto">
                            {{ $profile->preferred_categories ?? 'No categories listed.' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-700">Locations</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ $profile->preferred_locations ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 h-full md:col-span-2 xl:col-span-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Bio & Location</h3>
                    <div class="mb-4">
                        <h4 class="text-sm font-bold text-gray-700">Bio</h4>
                        <p class="mt-1 text-gray-600 italic text-sm">"{{ $profile->bio ?? 'No bio provided.' }}"</p>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-700">Address</h4>
                            <p class="mt-1 text-gray-600 text-sm">{{ $profile->address ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-700">Working Hours</h4>
                            <p class="mt-1 text-gray-600 text-sm">{{ $profile->working_hours ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6 md:col-span-2 xl:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Financial Details</h3>
                    @if($profile)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="mb-1"><span class="font-bold">Beneficiary:</span> {{ $profile->beneficiary_name }}</p>
                                <p class="mb-1"><span class="font-bold">Account:</span> {{ $profile->account_number }} ({{ $profile->account_type }})</p>
                                <p class="mb-1"><span class="font-bold">IFSC:</span> {{ $profile->ifsc_code }}</p>
                                @if($profile->cancelled_cheque_path)
                                    <a href="{{ asset('storage/'.$profile->cancelled_cheque_path) }}" target="_blank" class="text-blue-600 hover:underline mt-2 inline-block"><i class="fa-solid fa-file-image"></i> View Cheque</a>
                                @endif
                            </div>
                            <div>
                                <p class="mb-1"><span class="font-bold">PAN:</span> {{ $profile->pan_number }}</p>
                                <p class="mb-1"><span class="font-bold">GST:</span> {{ $profile->gst_number }}</p>
                                <div class="mt-2 space-x-4">
                                    @if($profile->pan_card_path)
                                        <a href="{{ asset('storage/'.$profile->pan_card_path) }}" target="_blank" class="text-green-600 hover:underline"><i class="fa-solid fa-id-card"></i> View PAN</a>
                                    @endif
                                    @if($profile->gst_certificate_path)
                                        <a href="{{ asset('storage/'.$profile->gst_certificate_path) }}" target="_blank" class="text-purple-600 hover:underline"><i class="fa-solid fa-file-contract"></i> View GST</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 italic">No financial details submitted yet.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
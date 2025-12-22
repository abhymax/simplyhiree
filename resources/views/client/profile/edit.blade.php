<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Company Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="post" action="{{ route('client.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="md:col-span-2 space-y-6">
                        
                        {{-- 1. Brand Identity (Existing) --}}
                        <div class="p-6 bg-white shadow sm:rounded-lg">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Brand Identity</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $profile->company_name ?? $user->name)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="industry" :value="__('Industry / Sector')" />
                                    <select name="industry" id="industry" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Industry</option>
                                        @foreach(['IT Services', 'Healthcare', 'Education', 'Retail', 'Manufacturing', 'Finance', 'Marketing', 'Real Estate'] as $ind)
                                            <option value="{{ $ind }}" {{ (old('industry', $profile->industry ?? '') == $ind) ? 'selected' : '' }}>{{ $ind }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="company_size" :value="__('Company Size')" />
                                    <select name="company_size" id="company_size" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Size</option>
                                        @foreach(['1-10 Employees', '11-50 Employees', '51-200 Employees', '201-500 Employees', '500+ Employees'] as $size)
                                            <option value="{{ $size }}" {{ (old('company_size', $profile->company_size ?? '') == $size) ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <x-input-label for="website" :value="__('Website URL')" />
                                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $profile->website ?? '')" placeholder="https://www.example.com" />
                                <x-input-error class="mt-2" :messages="$errors->get('website')" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="description" :value="__('About Company')" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tell candidates about your company culture and mission...">{{ old('description', $profile->description ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- 2. Compliance & Legal (NEW SECTION) --}}
                        <div class="p-6 bg-white shadow sm:rounded-lg">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Compliance & Legal Details</h3>
                            
                            {{-- PAN Details --}}
                            <div class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 uppercase mb-3">PAN Details <span class="text-red-500">*</span></h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="pan_number" :value="__('PAN Number')" />
                                        <x-text-input id="pan_number" name="pan_number" type="text" class="mt-1 block w-full uppercase" :value="old('pan_number', $profile->pan_number ?? '')" placeholder="ABCDE1234F" required />
                                        <x-input-error class="mt-2" :messages="$errors->get('pan_number')" />
                                    </div>
                                    <div>
                                        <x-input-label for="pan_file" :value="__('Upload PAN Card')" />
                                        <input type="file" name="pan_file" id="pan_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".pdf,.jpg,.jpeg,.png">
                                        <x-input-error class="mt-2" :messages="$errors->get('pan_file')" />
                                        
                                        @if($profile->pan_file_path)
                                            <div class="mt-2 text-xs">
                                                <span class="text-green-600 font-semibold">✓ File Uploaded.</span> 
                                                <a href="{{ asset('storage/'.$profile->pan_file_path) }}" target="_blank" class="text-indigo-600 hover:underline ml-1">View Document</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- TAN Details --}}
                            <div class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 uppercase mb-3">TAN Details (Optional)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="tan_number" :value="__('TAN Number')" />
                                        <x-text-input id="tan_number" name="tan_number" type="text" class="mt-1 block w-full uppercase" :value="old('tan_number', $profile->tan_number ?? '')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('tan_number')" />
                                    </div>
                                    <div>
                                        <x-input-label for="tan_file" :value="__('Upload TAN Cert')" />
                                        <input type="file" name="tan_file" id="tan_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".pdf,.jpg,.jpeg,.png">
                                        
                                        @if($profile->tan_file_path)
                                            <div class="mt-2 text-xs">
                                                <span class="text-green-600 font-semibold">✓ File Uploaded.</span> 
                                                <a href="{{ asset('storage/'.$profile->tan_file_path) }}" target="_blank" class="text-indigo-600 hover:underline ml-1">View Document</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- COI Details --}}
                            <div class="p-4 bg-gray-50 rounded border border-gray-200">
                                <h4 class="text-sm font-bold text-gray-700 uppercase mb-3">Certificate of Incorporation (Optional)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="coi_number" :value="__('CIN Number / Reg No.')" />
                                        <x-text-input id="coi_number" name="coi_number" type="text" class="mt-1 block w-full uppercase" :value="old('coi_number', $profile->coi_number ?? '')" />
                                    </div>
                                    <div>
                                        <x-input-label for="coi_file" :value="__('Upload COI')" />
                                        <input type="file" name="coi_file" id="coi_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".pdf,.jpg,.jpeg,.png">
                                        
                                        @if($profile->coi_file_path)
                                            <div class="mt-2 text-xs">
                                                <span class="text-green-600 font-semibold">✓ File Uploaded.</span> 
                                                <a href="{{ asset('storage/'.$profile->coi_file_path) }}" target="_blank" class="text-indigo-600 hover:underline ml-1">View Document</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- 3. Billing & Contact (Existing) --}}
                        <div class="p-6 bg-white shadow sm:rounded-lg">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Billing & Contact</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="contact_person_name" :value="__('Contact Person Name')" />
                                    <x-text-input id="contact_person_name" name="contact_person_name" type="text" class="mt-1 block w-full" :value="old('contact_person_name', $profile->contact_person_name ?? '')" required />
                                </div>
                                <div>
                                    <x-input-label for="contact_phone" :value="__('Contact Phone')" />
                                    <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" :value="old('contact_phone', $profile->contact_phone ?? '')" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <x-input-label for="gst_number" :value="__('GST / Tax ID (Optional)')" />
                                <x-text-input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" :value="old('gst_number', $profile->gst_number ?? '')" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="address" :value="__('Registered Address')" />
                                <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $profile->address ?? '') }}</textarea>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="city" :value="__('City')" />
                                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $profile->city ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="state" :value="__('State')" />
                                    <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $profile->state ?? '')" />
                                </div>
                                <div>
                                    <x-input-label for="pincode" :value="__('Pincode')" />
                                    <x-text-input id="pincode" name="pincode" type="text" class="mt-1 block w-full" :value="old('pincode', $profile->pincode ?? '')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar (Existing) --}}
                    <div class="md:col-span-1 space-y-6">
                        <div class="p-6 bg-white shadow sm:rounded-lg text-center">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Company Logo</h3>
                            
                            <div class="mb-4">
                                @if($profile->logo_path)
                                    <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-gray-100 shadow-sm mb-4">
                                @else
                                    <div class="w-32 h-32 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-4 border-2 border-dashed border-gray-300">
                                        <i class="fa-regular fa-image text-3xl"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="relative">
                                <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                                    <i class="fa-solid fa-upload mr-2"></i> Upload New Logo
                                </label>
                                <input id="logo" name="logo" type="file" class="hidden" accept="image/*" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                            </div>
                            <p id="file-chosen" class="text-xs text-gray-500 mt-2 italic">No file chosen</p>
                        </div>

                        <div class="p-6 bg-blue-50 border border-blue-100 rounded-lg">
                            <h4 class="font-bold text-blue-800 mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Compliance Check</h4>
                            <p class="text-sm text-blue-600">Uploading your PAN and COI documents speeds up the verification process and allows for faster job approval.</p>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Save Profile Changes
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
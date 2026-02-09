@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-6">

        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 p-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/20 border border-rose-400/40 text-rose-100 p-4 rounded-xl">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('client.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div class="mb-2 border-b border-white/10 pb-5">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold mt-3">Company Profile</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-2 space-y-6">

                    <div class="p-6 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Brand Identity</h3>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">Company Name</label>
                            <input id="company_name" name="company_name" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('company_name', $profile->company_name ?? $user->name) }}" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm text-blue-100">Industry / Sector</label>
                                <select name="industry" id="industry" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                    <option value="" class="text-slate-900">Select Industry</option>
                                    @foreach(['IT Services', 'Healthcare', 'Education', 'Retail', 'Manufacturing', 'Finance', 'Marketing', 'Real Estate'] as $ind)
                                        <option value="{{ $ind }}" {{ (old('industry', $profile->industry ?? '') == $ind) ? 'selected' : '' }} class="text-slate-900">{{ $ind }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm text-blue-100">Company Size</label>
                                <select name="company_size" id="company_size" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                                    <option value="" class="text-slate-900">Select Size</option>
                                    @foreach(['1-10 Employees', '11-50 Employees', '51-200 Employees', '201-500 Employees', '500+ Employees'] as $size)
                                        <option value="{{ $size }}" {{ (old('company_size', $profile->company_size ?? '') == $size) ? 'selected' : '' }} class="text-slate-900">{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">Website URL</label>
                            <input id="website" name="website" type="url" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('website', $profile->website ?? '') }}" placeholder="https://www.example.com" />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">About Company</label>
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('description', $profile->description ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="p-6 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Compliance & Legal</h3>

                        <div class="mb-4 p-3 bg-slate-900/40 rounded-xl border border-white/10">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-blue-100">PAN Number *</label>
                                    <input id="pan_number" name="pan_number" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/50 text-white uppercase" value="{{ old('pan_number', $profile->pan_number ?? '') }}" required />
                                </div>
                                <div>
                                    <label class="block text-sm text-blue-100">Upload PAN *</label>
                                    <input type="file" name="pan_file" id="pan_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                    @if(isset($profile->pan_file_path))
                                        <a href="{{ asset('storage/'.$profile->pan_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline mt-1 block">View Uploaded PAN</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm text-blue-100">TAN Number</label>
                                <input id="tan_number" name="tan_number" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/50 text-white uppercase" value="{{ old('tan_number', $profile->tan_number ?? '') }}" />
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100">Upload TAN</label>
                                <input type="file" name="tan_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                @if(isset($profile->tan_file_path))
                                    <a href="{{ asset('storage/'.$profile->tan_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline">View TAN</a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-blue-100">CIN / COI Number</label>
                                <input id="coi_number" name="coi_number" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/50 text-white uppercase" value="{{ old('coi_number', $profile->coi_number ?? '') }}" />
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100">Upload COI</label>
                                <input type="file" name="coi_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                @if(isset($profile->coi_file_path))
                                    <a href="{{ asset('storage/'.$profile->coi_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline">View COI</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Other Documents</h3>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">Upload Additional Documents (Max 10 total)</label>
                            <input type="file" name="other_docs[]" id="other_docs" multiple class="mt-1 block w-full text-sm text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600">
                            <p class="text-xs text-slate-300 mt-1">Accepted: PDF, JPG, PNG, DOCX. Max 5MB each. Uploaded files cannot be deleted.</p>
                        </div>

                        @if(isset($profile->other_docs) && count($profile->other_docs) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold text-blue-100 mb-2">Uploaded Documents ({{ count($profile->other_docs) }}/10):</h4>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($profile->other_docs as $index => $docPath)
                                        <li class="text-sm">
                                            <a href="{{ asset('storage/'.$docPath) }}" target="_blank" class="text-blue-200 hover:text-white">
                                                Document #{{ $index + 1 }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="p-6 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl">
                        <h3 class="text-lg font-bold text-white mb-4 border-b border-white/10 pb-2">Billing & Contact</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm text-blue-100">Contact Person Name</label>
                                <input id="contact_person_name" name="contact_person_name" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('contact_person_name', $profile->contact_person_name ?? '') }}" required />
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100">Contact Phone</label>
                                <input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('contact_phone', $profile->contact_phone ?? '') }}" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">GST / Tax ID (Optional)</label>
                            <input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('gst_number', $profile->gst_number ?? '') }}" />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm text-blue-100">Registered Address</label>
                            <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('address', $profile->address ?? '') }}</textarea>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-blue-100">City</label>
                                <input id="city" name="city" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('city', $profile->city ?? '') }}" />
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100">State</label>
                                <input id="state" name="state" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('state', $profile->state ?? '') }}" />
                            </div>
                            <div>
                                <label class="block text-sm text-blue-100">Pincode</label>
                                <input id="pincode" name="pincode" type="text" class="mt-1 block w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" value="{{ old('pincode', $profile->pincode ?? '') }}" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="md:col-span-1 space-y-6">
                    <div class="p-6 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl text-center">
                        <h3 class="text-lg font-bold text-white mb-4">Company Logo</h3>

                        <div class="mb-4">
                            @if(isset($profile->logo_path))
                                <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-white/20 shadow-sm mb-4">
                            @else
                                <div class="w-32 h-32 mx-auto rounded-full bg-white/10 flex items-center justify-center text-slate-300 mb-4 border-2 border-dashed border-white/20">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="relative">
                            <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 bg-white/10 border border-white/20 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-white/20 transition">
                                <i class="fa-solid fa-upload mr-2"></i> Upload New Logo
                            </label>
                            <input id="logo" name="logo" type="file" class="hidden" accept="image/*" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                        </div>
                        <p id="file-chosen" class="text-xs text-slate-300 mt-2 italic">No file chosen</p>
                    </div>

                    <div class="p-6 bg-blue-500/10 border border-blue-400/30 rounded-xl">
                        <h4 class="font-bold text-blue-100 mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Compliance Check</h4>
                        <p class="text-sm text-slate-200">Uploading PAN and COI documents speeds up verification.</p>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-3 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-600 hover:to-blue-600 transition">
                            Save Profile Changes
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection
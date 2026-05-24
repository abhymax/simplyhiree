@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-15"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen blur-[140px] opacity-15"></div>

    <style>
        .fld input, .fld select, .fld textarea {
            background: rgba(15,23,42,0.6) !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
            color: #fff !important;
            border-radius: 0.5rem !important;
            padding: 0.55rem 0.75rem !important;
            font-size: 0.875rem;
            width: 100%;
            box-sizing: border-box;
        }
        .fld input[type="text"], .fld input[type="email"], .fld input[type="url"], .fld input[type="tel"], .fld input[type="number"], .fld input[type="password"], .fld select { height: 38px !important; }
        .fld textarea { min-height: 90px !important; resize: vertical; }
        .fld input:focus, .fld select:focus, .fld textarea:focus { border-color: #22d3ee !important; box-shadow: 0 0 0 2px rgba(34,211,238,.18); outline: none; }
        .fld label:not(.btn-label) { color: #94a3b8; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; display: block; margin-bottom: 0.35rem; }
        .fld input::placeholder, .fld textarea::placeholder { color: rgba(148,163,184,.5); }
        .fld select option { background: #0f172a; color: #fff; }
    </style>

    <div class="relative z-10 max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-6 border-b border-white/10 pb-6">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Company Profile</h1>
            <p class="text-blue-200 mt-1">Keep your brand identity, compliance documents, and billing details up to date.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 rounded-xl text-sm">
                <i class="fa-solid fa-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 rounded-xl text-sm">
                <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Please fix the following:</p>
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('client.profile.update') }}" enctype="multipart/form-data" class="fld">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <div class="lg:col-span-2 space-y-5">

                    {{-- Brand Identity --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-id-badge text-cyan-400"></i> Brand Identity</h3>
                            <p class="text-xs text-slate-400 mt-0.5">The face of your company across SimplyHiree</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>Company Name</label>
                                <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $profile->company_name ?? $user->name) }}" required />
                            </div>
                            <div>
                                <label>Email Address</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>Industry / Sector</label>
                                <select name="industry" id="industry">
                                    <option value="">Select Industry</option>
                                    @foreach(['IT Services', 'Healthcare', 'Education', 'Retail', 'Manufacturing', 'Finance', 'Marketing', 'Real Estate'] as $ind)
                                        <option value="{{ $ind }}" {{ (old('industry', $profile->industry ?? '') == $ind) ? 'selected' : '' }}>{{ $ind }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Company Size</label>
                                <select name="company_size" id="company_size">
                                    <option value="">Select Size</option>
                                    @foreach(['1-10 Employees', '11-50 Employees', '51-200 Employees', '201-500 Employees', '500+ Employees'] as $size)
                                        <option value="{{ $size }}" {{ (old('company_size', $profile->company_size ?? '') == $size) ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label>Website URL</label>
                            <input id="website" name="website" type="url" value="{{ old('website', $profile->website ?? '') }}" placeholder="https://www.example.com" />
                        </div>

                        <div>
                            <label>About Company</label>
                            <textarea id="description" name="description" rows="4">{{ old('description', $profile->description ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Compliance --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-shield-halved text-amber-400"></i> Compliance &amp; Legal</h3>
                            <p class="text-xs text-slate-400 mt-0.5">PAN, TAN, and incorporation documents</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>PAN Number *</label>
                                <input id="pan_number" name="pan_number" type="text" class="uppercase" value="{{ old('pan_number', $profile->pan_number ?? '') }}" required />
                            </div>
                            <div>
                                <label>Upload PAN *</label>
                                <input type="file" name="pan_file" id="pan_file" class="text-sm text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500">
                                @if(isset($profile->pan_file_path))
                                    <a href="{{ asset('storage/'.$profile->pan_file_path) }}" target="_blank" class="text-xs text-cyan-300 hover:text-cyan-200 hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View Uploaded PAN</a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>TAN Number</label>
                                <input id="tan_number" name="tan_number" type="text" class="uppercase" value="{{ old('tan_number', $profile->tan_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload TAN</label>
                                <input type="file" name="tan_file" class="text-sm text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500">
                                @if(isset($profile->tan_file_path))
                                    <a href="{{ asset('storage/'.$profile->tan_file_path) }}" target="_blank" class="text-xs text-cyan-300 hover:text-cyan-200 hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View TAN</a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label>CIN / COI Number</label>
                                <input id="coi_number" name="coi_number" type="text" class="uppercase" value="{{ old('coi_number', $profile->coi_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload COI</label>
                                <input type="file" name="coi_file" class="text-sm text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500">
                                @if(isset($profile->coi_file_path))
                                    <a href="{{ asset('storage/'.$profile->coi_file_path) }}" target="_blank" class="text-xs text-cyan-300 hover:text-cyan-200 hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View COI</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Other Documents --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-folder-open text-indigo-400"></i> Other Documents</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Optional supporting files</p>
                        </div>

                        <label>Upload Additional Documents (Max 10 total)</label>
                        <input type="file" name="other_docs[]" id="other_docs" multiple class="text-sm text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:font-semibold file:bg-cyan-600 file:text-white hover:file:bg-cyan-500">
                        <p class="text-xs text-slate-400 mt-2">Accepted: PDF, JPG, PNG, DOCX. Max 5MB each. Uploaded files cannot be deleted.</p>

                        @if(isset($profile->other_docs) && count($profile->other_docs) > 0)
                            <div class="mt-4 p-3 rounded-lg border border-white/10 bg-slate-900/40">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Uploaded Documents ({{ count($profile->other_docs) }}/10)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($profile->other_docs as $index => $docPath)
                                        <a href="{{ asset('storage/'.$docPath) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-blue-100 hover:text-white transition">
                                            <i class="fa-regular fa-file text-cyan-300"></i> Document #{{ $index + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Billing & Contact --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-address-card text-emerald-400"></i> Billing &amp; Contact</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Where invoices and updates are sent</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>Contact Person Name</label>
                                <input id="contact_person_name" name="contact_person_name" type="text" value="{{ old('contact_person_name', $profile->contact_person_name ?? '') }}" required />
                            </div>
                            <div>
                                <label>Contact Phone</label>
                                <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $profile->contact_phone ?? '') }}" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label>GST / Tax ID (Optional)</label>
                            <input id="gst_number" name="gst_number" type="text" value="{{ old('gst_number', $profile->gst_number ?? '') }}" />
                        </div>

                        <div class="mb-4">
                            <label>Registered Address</label>
                            <textarea id="address" name="address" rows="2">{{ old('address', $profile->address ?? '') }}</textarea>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label>City</label>
                                <input id="city" name="city" type="text" value="{{ old('city', $profile->city ?? '') }}" />
                            </div>
                            <div>
                                <label>State</label>
                                <input id="state" name="state" type="text" value="{{ old('state', $profile->state ?? '') }}" />
                            </div>
                            <div>
                                <label>Pincode</label>
                                <input id="pincode" name="pincode" type="text" value="{{ old('pincode', $profile->pincode ?? '') }}" />
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right rail --}}
                <div class="lg:col-span-1 space-y-5">

                    {{-- Logo --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 text-center">
                        <h3 class="text-lg font-bold text-white flex items-center justify-center gap-2 mb-4 pb-3 border-b border-white/10"><i class="fa-solid fa-image text-cyan-400"></i> Company Logo</h3>

                        <div class="mb-4">
                            @if(isset($profile->logo_path))
                                <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-white/15">
                            @else
                                <div class="w-28 h-28 mx-auto rounded-full flex items-center justify-center text-slate-400 border-2 border-dashed border-white/20 bg-slate-900/40">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            @endif
                        </div>

                        <label for="logo" class="btn-label cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-xs text-white bg-cyan-600 hover:bg-cyan-500 transition">
                            <i class="fa-solid fa-upload"></i> Upload New Logo
                        </label>
                        <input id="logo" name="logo" type="file" class="hidden" accept="image/*" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                        <p id="file-chosen" class="text-xs text-slate-400 mt-2">No file chosen</p>
                    </div>

                    {{-- Compliance tip --}}
                    <div class="bg-slate-900/40 border border-cyan-400/30 rounded-2xl p-4">
                        <h4 class="font-bold text-white flex items-center gap-2 text-sm mb-1.5"><i class="fa-solid fa-lightbulb text-cyan-400"></i> Compliance Tip</h4>
                        <p class="text-xs text-blue-100">Uploading PAN and COI documents speeds up verification and unlocks faster invoice processing.</p>
                    </div>

                    {{-- Save --}}
                    <button type="submit" class="w-full justify-center inline-flex items-center gap-2 px-5 py-3 rounded-lg font-bold text-sm text-white bg-cyan-600 hover:bg-cyan-500 transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

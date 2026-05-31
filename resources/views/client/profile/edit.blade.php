@extends('layouts.client')

@section('client_content')


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

        /* Custom premium card style to stand out from client panel background */
        .profile-card {
            background-color: #0b1437 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 1.25rem !important;
            padding: 1.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .profile-card:hover {
            border-color: rgba(34, 211, 238, 0.3) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 0 15px rgba(6, 182, 212, 0.1) !important;
        }

        /* File Upload Buttons Styling to fix invisible / bad contrast default buttons */
        .fld input[type="file"] {
            background: transparent !important;
            border: none !important;
            color: #94a3b8 !important;
            padding: 0 !important;
            height: auto !important;
        }
        .fld input[type="file"]::file-selector-button {
            background: #0891b2 !important; /* cyan-600 */
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.9rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            transition: all 0.2s ease-in-out !important;
            margin-right: 0.75rem !important;
        }
        .fld input[type="file"]::file-selector-button:hover {
            background: #06b6d4 !important; /* cyan-500 */
            box-shadow: 0 0 12px rgba(6,182,212,0.3) !important;
        }
        .fld input[type="file"]::-webkit-file-upload-button {
            background: #0891b2 !important; /* cyan-600 */
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            padding: 0.45rem 0.9rem !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            transition: all 0.2s ease-in-out !important;
            margin-right: 0.75rem !important;
        }
        .fld input[type="file"]::-webkit-file-upload-button:hover {
            background: #06b6d4 !important; /* cyan-500 */
            box-shadow: 0 0 12px rgba(6,182,212,0.3) !important;
        }
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
                    <div class="profile-card">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-id-badge text-cyan-400"></i> Brand Identity</h3>
                            <p class="text-xs text-slate-400 mt-0.5">The face of your company across SimplyHiree</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label>Company Name</label>
                                <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $profile->company_name ?? $user->name) }}" required />
                            </div>
                            <div>
                                <label>Email Address</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                            </div>
                            <div>
                                <label>Website URL</label>
                                <input id="website" name="website" type="url" value="{{ old('website', $profile->website ?? '') }}" placeholder="https://www.example.com" />
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

                        <div>
                            <label>About Company</label>
                            <textarea id="description" name="description" rows="4">{{ old('description', $profile->description ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Compliance --}}
                    <div class="profile-card">
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
                                <input type="file" name="pan_file" id="pan_file" accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-slate-200" onchange="previewFile(this, 'pan-preview')">
                                <div id="pan-preview" class="mt-3">
                                    @if(isset($profile->pan_file_path))
                                        @php
                                            $ext = strtolower(pathinfo($profile->pan_file_path, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                        @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-slate-950/40 backdrop-blur-md">
                                            @if($isImg)
                                                <img src="{{ asset('storage/'.$profile->pan_file_path) }}" class="w-12 h-12 rounded-lg object-cover border border-white/15">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 text-xl"><i class="fa-solid fa-file-pdf"></i></div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-white font-bold truncate">Uploaded PAN Document</p>
                                                <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mt-0.5"><i class="fa-solid fa-circle-check"></i> Verified File</p>
                                            </div>
                                            <a href="{{ asset('storage/'.$profile->pan_file_path) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-bold text-slate-200 hover:bg-white/10 hover:text-white transition"><i class="fa-solid fa-eye"></i> View</a>
                                        </div>
                                    @else
                                        <p class="text-[11px] text-slate-500 italic"><i class="fa-solid fa-circle-info"></i> No file uploaded yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>TAN Number</label>
                                <input id="tan_number" name="tan_number" type="text" class="uppercase" value="{{ old('tan_number', $profile->tan_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload TAN</label>
                                <input type="file" name="tan_file" id="tan_file" accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-slate-200" onchange="previewFile(this, 'tan-preview')">
                                <div id="tan-preview" class="mt-3">
                                    @if(isset($profile->tan_file_path))
                                        @php
                                            $ext = strtolower(pathinfo($profile->tan_file_path, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                        @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-slate-950/40 backdrop-blur-md">
                                            @if($isImg)
                                                <img src="{{ asset('storage/'.$profile->tan_file_path) }}" class="w-12 h-12 rounded-lg object-cover border border-white/15">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 text-xl"><i class="fa-solid fa-file-pdf"></i></div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-white font-bold truncate">Uploaded TAN Document</p>
                                                <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mt-0.5"><i class="fa-solid fa-circle-check"></i> Verified File</p>
                                            </div>
                                            <a href="{{ asset('storage/'.$profile->tan_file_path) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-bold text-slate-200 hover:bg-white/10 hover:text-white transition"><i class="fa-solid fa-eye"></i> View</a>
                                        </div>
                                    @else
                                        <p class="text-[11px] text-slate-500 italic"><i class="fa-solid fa-circle-info"></i> No file uploaded yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label>CIN / COI Number</label>
                                <input id="coi_number" name="coi_number" type="text" class="uppercase" value="{{ old('coi_number', $profile->coi_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload COI</label>
                                <input type="file" name="coi_file" id="coi_file" accept=".pdf,.jpg,.jpeg,.png" class="text-sm text-slate-200" onchange="previewFile(this, 'coi-preview')">
                                <div id="coi-preview" class="mt-3">
                                    @if(isset($profile->coi_file_path))
                                        @php
                                            $ext = strtolower(pathinfo($profile->coi_file_path, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                        @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-slate-950/40 backdrop-blur-md">
                                            @if($isImg)
                                                <img src="{{ asset('storage/'.$profile->coi_file_path) }}" class="w-12 h-12 rounded-lg object-cover border border-white/15">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 text-xl"><i class="fa-solid fa-file-pdf"></i></div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs text-white font-bold truncate">Uploaded COI Document</p>
                                                <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mt-0.5"><i class="fa-solid fa-circle-check"></i> Verified File</p>
                                            </div>
                                            <a href="{{ asset('storage/'.$profile->coi_file_path) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-bold text-slate-200 hover:bg-white/10 hover:text-white transition"><i class="fa-solid fa-eye"></i> View</a>
                                        </div>
                                    @else
                                        <p class="text-[11px] text-slate-500 italic"><i class="fa-solid fa-circle-info"></i> No file uploaded yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Other Documents --}}
                    <div class="profile-card">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-folder-open text-indigo-400"></i> Other Documents</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Optional supporting files</p>
                        </div>

                        <label>Upload Additional Documents (Max 10 total)</label>
                        <input type="file" name="other_docs[]" id="other_docs" multiple class="text-sm text-slate-200" onchange="previewMultipleFiles(this, 'other-docs-preview')">
                        <div id="other-docs-preview" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                        <p class="text-xs text-slate-400 mt-2">Accepted: PDF, JPG, PNG, DOCX. Max 5MB each. Uploaded files cannot be deleted.</p>

                        @if(isset($profile->other_docs) && count($profile->other_docs) > 0)
                            <div class="mt-4 p-3 rounded-lg border border-white/10 bg-slate-900/40">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Uploaded Documents ({{ count($profile->other_docs) }}/10)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($profile->other_docs as $index => $docPath)
                                        @php
                                            $ext = strtolower(pathinfo($docPath, PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg','jpeg','png','webp']);
                                        @endphp
                                        <div class="flex items-center justify-between gap-3 p-2.5 rounded bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-blue-100 hover:text-white transition">
                                            <div class="flex items-center gap-2 min-w-0">
                                                @if($isImg)
                                                    <img src="{{ asset('storage/'.$docPath) }}" class="w-6 h-6 rounded object-cover border border-white/10">
                                                @else
                                                    <i class="fa-regular fa-file text-cyan-300"></i>
                                                @endif
                                                <span class="truncate">Document #{{ $index + 1 }}</span>
                                            </div>
                                            <a href="{{ asset('storage/'.$docPath) }}" target="_blank" class="px-2 py-1 rounded bg-white/5 border border-white/10 text-[10px] font-bold text-slate-200 hover:bg-white/10 hover:text-white transition"><i class="fa-solid fa-eye"></i></a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Billing & Contact --}}
                    <div class="profile-card">
                        <div class="mb-4 pb-3 border-b border-white/10">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fa-solid fa-address-card text-emerald-400"></i> Billing &amp; Contact</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Where invoices and updates are sent</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label>Contact Person Name</label>
                                <input id="contact_person_name" name="contact_person_name" type="text" value="{{ old('contact_person_name', $profile->contact_person_name ?? '') }}" required />
                            </div>
                            <div>
                                <label>Contact Phone</label>
                                <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $profile->contact_phone ?? '') }}" />
                            </div>
                            <div>
                                <label>GST / Tax ID (Optional)</label>
                                <input id="gst_number" name="gst_number" type="text" value="{{ old('gst_number', $profile->gst_number ?? '') }}" />
                            </div>
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
                    <div class="profile-card text-center">
                        <h3 class="text-lg font-bold text-white flex items-center justify-center gap-2 mb-4 pb-3 border-b border-white/10"><i class="fa-solid fa-image text-cyan-400"></i> Company Logo</h3>

                        <div class="mb-4">
                            @if(isset($profile->logo_path))
                                <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-white/15" id="logo-preview-image">
                            @else
                                <div class="w-28 h-28 mx-auto rounded-full flex items-center justify-center text-slate-400 border-2 border-dashed border-white/20 bg-slate-900/40" id="logo-placeholder">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                                <img src="" alt="Logo" class="w-28 h-28 mx-auto rounded-full object-cover border-2 border-white/15 hidden" id="logo-preview-image">
                            @endif
                        </div>

                        <label for="logo" class="btn-label cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-xs text-white bg-cyan-600 hover:bg-cyan-500 transition">
                            <i class="fa-solid fa-upload"></i> Upload New Logo
                        </label>
                        <input id="logo" name="logo" type="file" class="hidden" accept="image/*" onchange="previewLogo(this)">
                        <p id="file-chosen" class="text-xs text-slate-400 mt-2">No file chosen</p>
                    </div>

                    {{-- Compliance tip --}}
                    <div class="bg-[#051c33] border border-cyan-500/30 rounded-2xl p-4">
                        <h4 class="font-bold text-white flex items-center gap-2 text-sm mb-1.5"><i class="fa-solid fa-lightbulb text-cyan-400"></i> Compliance Tip</h4>
                        <p class="text-xs text-blue-100">Uploading PAN and COI documents speeds up verification and unlocks faster invoice processing.</p>
                    </div>

                    {{-- Save --}}
                    <button type="submit" class="w-full justify-center inline-flex items-center gap-2 px-5 py-3 rounded-lg font-bold text-sm text-white bg-cyan-600 hover:bg-cyan-500 transition shadow-lg hover:shadow-cyan-500/30">
                        <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                    </button>
                </div>

            </div>
        </form>
    </div>

<script>
    function previewFile(input, previewId) {
        const container = document.getElementById(previewId);
        if (!container) return;

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            const isImg = file.type.startsWith('image/');
            
            let iconHtml = '';
            if (isImg) {
                const imgUrl = URL.createObjectURL(file);
                iconHtml = `<img src="${imgUrl}" class="w-12 h-12 rounded-lg object-cover border border-cyan-500/30">`;
            } else {
                iconHtml = `<div class="w-12 h-12 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-xl"><i class="fa-solid fa-file-pdf"></i></div>`;
            }

            container.innerHTML = `
                <div class="flex items-center gap-3 p-3 rounded-xl border border-cyan-500/35 bg-cyan-950/30 backdrop-blur-md animate-fade-in">
                    ${iconHtml}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-cyan-200 font-bold truncate">${file.name}</p>
                        <p class="text-[10px] text-cyan-400/80 mt-0.5">${sizeInMb} MB &bull; Selected to Upload</p>
                    </div>
                    <button type="button" onclick="clearFileInput('${input.id || ''}', '${previewId}')" class="px-2 py-1 text-slate-400 hover:text-rose-400 transition text-lg">&times;</button>
                </div>
            `;
        }
    }

    function previewMultipleFiles(input, previewId) {
        const container = document.getElementById(previewId);
        if (!container) return;
        container.innerHTML = '';

        if (input.files && input.files.length) {
            Array.from(input.files).forEach((file, index) => {
                const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
                const isImg = file.type.startsWith('image/');
                
                let iconHtml = '';
                if (isImg) {
                    const imgUrl = URL.createObjectURL(file);
                    iconHtml = `<img src="${imgUrl}" class="w-10 h-10 rounded object-cover border border-cyan-500/20">`;
                } else {
                    iconHtml = `<div class="w-10 h-10 rounded bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-base"><i class="fa-solid fa-file-pdf"></i></div>`;
                }

                const card = document.createElement('div');
                card.className = 'flex items-center gap-2.5 p-2.5 rounded-xl border border-cyan-500/20 bg-cyan-950/20 backdrop-blur-sm';
                card.innerHTML = `
                    ${iconHtml}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-cyan-200 font-medium truncate">${file.name}</p>
                        <p class="text-[9px] text-cyan-400/60 mt-0.5">${sizeInMb} MB</p>
                    </div>
                `;
                container.appendChild(card);
            });
        }
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const imgPreview = document.getElementById('logo-preview-image');
            const placeholder = document.getElementById('logo-placeholder');
            const fileChosen = document.getElementById('file-chosen');
            
            if (imgPreview) {
                imgPreview.src = URL.createObjectURL(file);
                imgPreview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
            if (fileChosen) {
                fileChosen.textContent = file.name;
            }
        }
    }

    function clearFileInput(inputId, previewId) {
        if (!inputId) return;
        const input = document.getElementById(inputId);
        if (input) {
            input.value = '';
            const container = document.getElementById(previewId);
            if (container) {
                container.innerHTML = `<p class="text-[11px] text-slate-500 italic"><i class="fa-solid fa-circle-info"></i> No file selected.</p>`;
            }
        }
    }
</script>
@endsection

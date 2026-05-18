@extends('layouts.app')

@section('content')
<style>
    .gloss { position: relative; isolation: isolate; }
    .gloss::before { content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(255,255,255,.14) 0%, rgba(255,255,255,0) 35%, rgba(255,255,255,0) 65%, rgba(255,255,255,.07) 100%); pointer-events:none; border-radius:inherit; z-index:0; }
    .gloss > * { position:relative; z-index:1; }
    .ring-glow { box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 20px 60px -20px rgba(99,102,241,.55), 0 0 80px -25px rgba(34,211,238,.45); }
    .panel { transition: transform .2s ease, box-shadow .25s ease, border-color .2s ease; }
    .panel:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.25); box-shadow: 0 18px 45px -18px rgba(34,211,238,.35); }
    .neon-btn { background: linear-gradient(135deg, #06b6d4, #6366f1); box-shadow: 0 10px 30px -8px rgba(34,211,238,.55), inset 0 1px 0 rgba(255,255,255,.3); }
    .neon-btn:hover { filter: brightness(1.1); box-shadow: 0 18px 40px -10px rgba(34,211,238,.7), inset 0 1px 0 rgba(255,255,255,.35); }
    .blob { animation: float 18s ease-in-out infinite alternate; }
    @keyframes float { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(20px,-20px) scale(1.05); } }
    .fld input, .fld select, .fld textarea { background: rgba(2,6,23,.55) !important; border: 1px solid rgba(255,255,255,.18) !important; color: #fff !important; border-radius: .75rem !important; padding: 0.95rem 1.5rem !important; line-height: 1.4; font-size: .95rem; width: 100%; box-sizing: border-box; text-indent: 0; }
    .fld input[type="text"], .fld input[type="email"], .fld input[type="url"], .fld input[type="tel"], .fld input[type="number"], .fld input[type="password"], .fld select { height: 54px !important; padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
    .fld textarea { min-height: 120px !important; resize: vertical; padding: 1rem 1.5rem !important; }
    .fld input:focus, .fld select:focus, .fld textarea:focus { border-color: #22d3ee !important; box-shadow: 0 0 0 3px rgba(34,211,238,.18); outline: none; }
    .fld label { color: #bfdbfe; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; display: block; margin-bottom: .35rem; }
    .fld input::placeholder, .fld textarea::placeholder { color: rgba(191,219,254,.45); }
    .sec-icon { width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius: .9rem; }
</style>

<div class="min-h-screen text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden"
     style="background: linear-gradient(135deg, #020617 0%, #1e1b4b 50%, #0f172a 100%);">
    <div class="blob absolute -top-32 -left-32 rounded-full mix-blend-screen opacity-25"
         style="width:28rem; height:28rem; background:#06b6d4; filter:blur(140px);"></div>
    <div class="blob absolute top-1/3 right-0 rounded-full mix-blend-screen opacity-25"
         style="width:28rem; height:28rem; background:#d946ef; filter:blur(140px); animation-delay:-6s;"></div>
    <div class="blob absolute bottom-0 left-1/3 rounded-full mix-blend-screen opacity-25"
         style="width:28rem; height:28rem; background:#6366f1; filter:blur(140px); animation-delay:-12s;"></div>
    <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,.6) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.07;"></div>

    <div class="relative z-10 max-w-screen-2xl mx-auto space-y-6">

        @if(session('success'))
            <div class="gloss bg-emerald-500/15 border border-emerald-400/40 text-emerald-100 p-4 rounded-2xl ring-glow">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="gloss bg-rose-500/15 border border-rose-400/40 text-rose-100 p-4 rounded-2xl">
                <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Please fix the following:</p>
                <ul class="list-disc ml-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hero header --}}
        <div class="relative rounded-3xl overflow-hidden ring-glow"
             style="background: linear-gradient(135deg, #0c4a6e 0%, #312e81 55%, #4a1d96 100%);">
            <div class="absolute inset-0" style="background: radial-gradient(120% 60% at 0% 0%, rgba(255,255,255,.18), transparent 55%);"></div>
            <div class="absolute -right-12 -bottom-12 w-72 h-72 rounded-full" style="background: rgba(56,189,248,.18); filter: blur(60px);"></div>
            <div class="relative gloss p-7 md:p-9 grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-6 items-center">
                <div class="min-w-0">
                    <span class="px-3 py-1.5 rounded-full bg-white/15 border border-white/30 text-cyan-100 text-[10px] font-bold uppercase tracking-[0.18em] shadow-lg inline-block">
                        ✨ Client Workspace
                    </span>
                    <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white mt-3" style="text-shadow: 0 2px 12px rgba(34,211,238,.35);">Company Profile</h1>
                    <p class="text-blue-100 mt-2" style="text-shadow: 0 1px 4px rgba(0,0,0,.35);">Keep your brand identity, compliance documents, and billing details up to date so jobs go live faster and invoices flow smoothly.</p>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md flex items-center gap-3 lg:justify-self-end shrink-0 w-full lg:w-auto">
                    @if(isset($profile->logo_path))
                        <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-12 h-12 rounded-full object-cover border-2 border-white/30 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-cyan-200 shrink-0" style="background: rgba(34,211,238,.18);"><i class="fa-regular fa-building"></i></div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-[10px] text-cyan-200 font-bold uppercase tracking-widest">Account</p>
                        <p class="text-white font-bold leading-tight truncate">{{ $profile->company_name ?? $user->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('client.profile.update') }}" enctype="multipart/form-data" class="fld">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-2 space-y-6">

                    {{-- Brand Identity --}}
                    <div class="panel gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-8 ring-glow">
                        <div class="flex items-center gap-3 mb-5 border-b border-white/10 pb-4">
                            <span class="sec-icon" style="background: linear-gradient(135deg, #06b6d4, #6366f1);"><i class="fa-solid fa-id-badge text-white"></i></span>
                            <div>
                                <h3 class="text-xl font-extrabold text-white">Brand Identity</h3>
                                <p class="text-xs text-blue-200">The face of your company across SimplyHiree</p>
                            </div>
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
                                    <option value="" style="color:#0f172a;">Select Industry</option>
                                    @foreach(['IT Services', 'Healthcare', 'Education', 'Retail', 'Manufacturing', 'Finance', 'Marketing', 'Real Estate'] as $ind)
                                        <option value="{{ $ind }}" {{ (old('industry', $profile->industry ?? '') == $ind) ? 'selected' : '' }} style="color:#0f172a;">{{ $ind }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Company Size</label>
                                <select name="company_size" id="company_size">
                                    <option value="" style="color:#0f172a;">Select Size</option>
                                    @foreach(['1-10 Employees', '11-50 Employees', '51-200 Employees', '201-500 Employees', '500+ Employees'] as $size)
                                        <option value="{{ $size }}" {{ (old('company_size', $profile->company_size ?? '') == $size) ? 'selected' : '' }} style="color:#0f172a;">{{ $size }}</option>
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
                    <div class="panel gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-8 ring-glow">
                        <div class="flex items-center gap-3 mb-5 border-b border-white/10 pb-4">
                            <span class="sec-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);"><i class="fa-solid fa-shield-halved text-white"></i></span>
                            <div>
                                <h3 class="text-xl font-extrabold text-white">Compliance &amp; Legal</h3>
                                <p class="text-xs text-blue-200">PAN, TAN, and incorporation documents</p>
                            </div>
                        </div>

                        <div class="mb-4 p-4 rounded-2xl border border-amber-400/20" style="background: rgba(245,158,11,.08);">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label>PAN Number *</label>
                                    <input id="pan_number" name="pan_number" type="text" class="mt-1 block w-full uppercase" value="{{ old('pan_number', $profile->pan_number ?? '') }}" required />
                                </div>
                                <div>
                                    <label>Upload PAN *</label>
                                    <input type="file" name="pan_file" id="pan_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                    @if(isset($profile->pan_file_path))
                                        <a href="{{ asset('storage/'.$profile->pan_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View Uploaded PAN</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>TAN Number</label>
                                <input id="tan_number" name="tan_number" type="text" class="mt-1 block w-full uppercase" value="{{ old('tan_number', $profile->tan_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload TAN</label>
                                <input type="file" name="tan_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                @if(isset($profile->tan_file_path))
                                    <a href="{{ asset('storage/'.$profile->tan_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View TAN</a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label>CIN / COI Number</label>
                                <input id="coi_number" name="coi_number" type="text" class="mt-1 block w-full uppercase" value="{{ old('coi_number', $profile->coi_number ?? '') }}" />
                            </div>
                            <div>
                                <label>Upload COI</label>
                                <input type="file" name="coi_file" class="mt-1 block w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                                @if(isset($profile->coi_file_path))
                                    <a href="{{ asset('storage/'.$profile->coi_file_path) }}" target="_blank" class="text-xs text-emerald-300 font-bold hover:underline mt-1 inline-block"><i class="fa-solid fa-up-right-from-square mr-1"></i>View COI</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Other Documents --}}
                    <div class="panel gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-8 ring-glow">
                        <div class="flex items-center gap-3 mb-5 border-b border-white/10 pb-4">
                            <span class="sec-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);"><i class="fa-solid fa-folder-open text-white"></i></span>
                            <div>
                                <h3 class="text-xl font-extrabold text-white">Other Documents</h3>
                                <p class="text-xs text-blue-200">Optional supporting files</p>
                            </div>
                        </div>

                        <label>Upload Additional Documents (Max 10 total)</label>
                        <input type="file" name="other_docs[]" id="other_docs" multiple class="mt-1 block w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:font-semibold file:bg-fuchsia-500 file:text-white hover:file:bg-fuchsia-600">
                        <p class="text-xs text-blue-200 mt-2">Accepted: PDF, JPG, PNG, DOCX. Max 5MB each. Uploaded files cannot be deleted.</p>

                        @if(isset($profile->other_docs) && count($profile->other_docs) > 0)
                            <div class="mt-5 p-4 rounded-2xl border border-white/10" style="background: rgba(2,6,23,.45);">
                                <h4 class="text-xs font-bold text-cyan-200 uppercase tracking-widest mb-3">Uploaded Documents ({{ count($profile->other_docs) }}/10)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($profile->other_docs as $index => $docPath)
                                        <a href="{{ asset('storage/'.$docPath) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-sm text-blue-100 hover:text-white transition">
                                            <i class="fa-regular fa-file text-cyan-300"></i> Document #{{ $index + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Billing & Contact --}}
                    <div class="panel gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-8 ring-glow">
                        <div class="flex items-center gap-3 mb-5 border-b border-white/10 pb-4">
                            <span class="sec-icon" style="background: linear-gradient(135deg, #10b981, #14b8a6);"><i class="fa-solid fa-address-card text-white"></i></span>
                            <div>
                                <h3 class="text-xl font-extrabold text-white">Billing &amp; Contact</h3>
                                <p class="text-xs text-blue-200">Where invoices and updates are sent</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label>Contact Person Name</label>
                                <input id="contact_person_name" name="contact_person_name" type="text" class="mt-1 block w-full" value="{{ old('contact_person_name', $profile->contact_person_name ?? '') }}" required />
                            </div>
                            <div>
                                <label>Contact Phone</label>
                                <input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" value="{{ old('contact_phone', $profile->contact_phone ?? '') }}" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label>GST / Tax ID (Optional)</label>
                            <input id="gst_number" name="gst_number" type="text" class="mt-1 block w-full" value="{{ old('gst_number', $profile->gst_number ?? '') }}" />
                        </div>

                        <div class="mb-4">
                            <label>Registered Address</label>
                            <textarea id="address" name="address" rows="2" class="mt-1 block w-full">{{ old('address', $profile->address ?? '') }}</textarea>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label>City</label>
                                <input id="city" name="city" type="text" class="mt-1 block w-full" value="{{ old('city', $profile->city ?? '') }}" />
                            </div>
                            <div>
                                <label>State</label>
                                <input id="state" name="state" type="text" class="mt-1 block w-full" value="{{ old('state', $profile->state ?? '') }}" />
                            </div>
                            <div>
                                <label>Pincode</label>
                                <input id="pincode" name="pincode" type="text" class="mt-1 block w-full" value="{{ old('pincode', $profile->pincode ?? '') }}" />
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Right rail --}}
                <div class="md:col-span-1 space-y-6">

                    {{-- Logo --}}
                    <div class="panel gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-7 ring-glow text-center">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <span class="sec-icon" style="background: linear-gradient(135deg, #06b6d4, #8b5cf6); width:34px; height:34px;"><i class="fa-solid fa-image text-white text-sm"></i></span>
                            <h3 class="text-lg font-extrabold text-white">Company Logo</h3>
                        </div>

                        <div class="mb-4">
                            @if(isset($profile->logo_path))
                                <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Logo" class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-white/20 shadow-lg shadow-cyan-500/20">
                            @else
                                <div class="w-32 h-32 mx-auto rounded-full flex items-center justify-center text-cyan-200 border-2 border-dashed border-white/25" style="background: rgba(34,211,238,.08);">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            @endif
                        </div>

                        <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 rounded-xl font-bold text-xs text-white uppercase tracking-widest transition" style="background: linear-gradient(135deg, #06b6d4, #6366f1); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                            <i class="fa-solid fa-upload mr-2"></i> Upload New Logo
                        </label>
                        <input id="logo" name="logo" type="file" class="hidden" accept="image/*" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                        <p id="file-chosen" class="text-xs text-blue-200 mt-3 italic">No file chosen</p>
                    </div>

                    {{-- Compliance tip --}}
                    <div class="panel gloss rounded-3xl p-5 border border-cyan-400/30 ring-glow" style="background: linear-gradient(160deg, rgba(34,211,238,.15), rgba(99,102,241,.08));">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="sec-icon" style="background: rgba(34,211,238,.25); width:32px; height:32px;"><i class="fa-solid fa-lightbulb text-cyan-100 text-sm"></i></span>
                            <h4 class="font-extrabold text-white">Compliance Tip</h4>
                        </div>
                        <p class="text-sm text-blue-100">Uploading PAN and COI documents speeds up verification and unlocks faster invoice processing.</p>
                    </div>

                    {{-- Save --}}
                    <button type="submit" class="neon-btn w-full justify-center inline-flex items-center gap-2 px-5 py-3.5 rounded-2xl font-extrabold text-sm text-white uppercase tracking-widest transition">
                        <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

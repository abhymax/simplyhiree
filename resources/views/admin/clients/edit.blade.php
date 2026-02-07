<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-6xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex justify-between items-end">
                <div>
                    <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Client List
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Client Profile</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Update company details and compliance information for <span class="text-white font-bold">{{ $user->name }}</span>.</p>
                </div>
            </div>

            {{-- ERROR HANDLING --}}
            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-500/20 border border-rose-500/50 rounded-2xl backdrop-blur-md shadow-lg">
                    <div class="flex items-center gap-2 text-rose-300 font-bold mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i> Update Failed
                    </div>
                    <ul class="list-disc list-inside text-rose-100 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.clients.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    {{-- SECTION 1: ACCOUNT & BILLING --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-user-gear text-purple-400"></i> Account Settings
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Login Email</label>
                                <input type="email" value="{{ $user->email }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-slate-400 h-12 cursor-not-allowed" disabled>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-emerald-400 uppercase mb-2">Billable Cycle (Days)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3.5 text-emerald-500"><i class="fa-regular fa-calendar-check"></i></span>
                                    <input type="number" name="billable_period_days" value="{{ old('billable_period_days', $user->billable_period_days) }}" class="w-full pl-10 bg-slate-800 border border-emerald-500/50 rounded-xl text-white font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: COMPANY OVERVIEW --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-building text-blue-400"></i> Company Details
                        </h3>
                        
                        <div class="space-y-6">
                            {{-- Logo Upload --}}
                            <div class="flex items-center gap-4">
                                @if(isset($user->clientProfile->logo_path))
                                    <img src="{{ asset('storage/' . $user->clientProfile->logo_path) }}" alt="Logo" class="h-16 w-16 rounded-lg object-cover bg-white">
                                @endif
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Company Logo</label>
                                    <input type="file" name="logo" class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 bg-slate-800/50 rounded-xl border border-white/10 cursor-pointer">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Registered Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $user->clientProfile->company_name ?? $user->name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 transition h-12">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Industry</label>
                                    <input type="text" name="industry" value="{{ old('industry', $user->clientProfile->industry ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 transition h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Size</label>
                                    <select name="company_size" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 transition h-12">
                                        <option value="">Select Size</option>
                                        <option value="1-10" {{ (old('company_size', $user->clientProfile->company_size ?? '') == '1-10') ? 'selected' : '' }}>1-10</option>
                                        <option value="11-50" {{ (old('company_size', $user->clientProfile->company_size ?? '') == '11-50') ? 'selected' : '' }}>11-50</option>
                                        <option value="51-200" {{ (old('company_size', $user->clientProfile->company_size ?? '') == '51-200') ? 'selected' : '' }}>51-200</option>
                                        <option value="200+" {{ (old('company_size', $user->clientProfile->company_size ?? '') == '200+') ? 'selected' : '' }}>200+</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Website</label>
                                <input type="url" name="website" value="{{ old('website', $user->clientProfile->website ?? '') }}" placeholder="https://" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 transition h-12">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Description</label>
                                <textarea name="description" rows="3" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 transition">{{ old('description', $user->clientProfile->description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: CONTACT & LOCATION --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-map-location-dot text-orange-400"></i> Contact & Location
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-orange-200 uppercase mb-2">Contact Person</label>
                                <input type="text" name="contact_person_name" value="{{ old('contact_person_name', $user->clientProfile->contact_person_name ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition h-12">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-orange-200 uppercase mb-2">Phone</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $user->clientProfile->contact_phone ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition h-12">
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-orange-200 uppercase mb-2">Address</label>
                                <textarea name="address" rows="2" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition">{{ old('address', $user->clientProfile->address ?? '') }}</textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-orange-200 uppercase mb-2">City</label>
                                    <input type="text" name="city" value="{{ old('city', $user->clientProfile->city ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-orange-200 uppercase mb-2">State</label>
                                    <input type="text" name="state" value="{{ old('state', $user->clientProfile->state ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-orange-200 uppercase mb-2">Pincode</label>
                                    <input type="text" name="pincode" value="{{ old('pincode', $user->clientProfile->pincode ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-orange-500 transition h-12">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: COMPLIANCE & DOCS --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-file-contract text-emerald-400"></i> Tax & Compliance
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">GST Number</label>
                                <input type="text" name="gst_number" value="{{ old('gst_number', $user->clientProfile->gst_number ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-emerald-500 transition h-12">
                            </div>

                            {{-- PAN --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">PAN Number</label>
                                    <input type="text" name="pan_number" value="{{ old('pan_number', $user->clientProfile->pan_number ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">Upload PAN</label>
                                    <input type="file" name="pan_file" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-full file:bg-emerald-600 file:text-white bg-slate-800/50 rounded-lg border border-white/10">
                                </div>
                            </div>

                            {{-- TAN --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">TAN Number</label>
                                    <input type="text" name="tan_number" value="{{ old('tan_number', $user->clientProfile->tan_number ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">Upload TAN</label>
                                    <input type="file" name="tan_file" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-full file:bg-emerald-600 file:text-white bg-slate-800/50 rounded-lg border border-white/10">
                                </div>
                            </div>

                            {{-- COI --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">CIN / COI Number</label>
                                    <input type="text" name="coi_number" value="{{ old('coi_number', $user->clientProfile->coi_number ?? '') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">Upload COI</label>
                                    <input type="file" name="coi_file" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-full file:bg-emerald-600 file:text-white bg-slate-800/50 rounded-lg border border-white/10">
                                </div>
                            </div>

                            {{-- Other Docs --}}
                            <div class="border-t border-white/10 pt-4 mt-2">
                                <label class="block text-xs font-bold text-emerald-200 uppercase mb-2">Other Documents (Multiple)</label>
                                <input type="file" name="other_docs[]" multiple class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-emerald-600 file:text-white bg-slate-800/50 rounded-xl border border-white/10">
                                @if(isset($user->clientProfile->other_docs) && count($user->clientProfile->other_docs) > 0)
                                    <p class="text-xs text-emerald-300 mt-2"><i class="fa-solid fa-file"></i> {{ count($user->clientProfile->other_docs) }} document(s) attached.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ACTION BAR --}}
                <div class="flex justify-end gap-4 border-t border-white/10 pt-8 mt-8 pb-12">
                    <a href="{{ route('admin.clients.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
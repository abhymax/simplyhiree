<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-6xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex justify-between items-end">
                <div>
                    <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Partners
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Partner Profile</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Update complete details for <span class="text-white font-bold">{{ $user->name }}</span>.</p>
                </div>
            </div>

            {{-- ERROR LIST --}}
            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-500/20 border border-rose-500/50 rounded-2xl backdrop-blur-md shadow-lg">
                    <ul class="list-disc list-inside text-rose-100 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.partners.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                
                <div class="space-y-8">

                    {{-- SECTION 1: IDENTITY & BASIC INFO --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-id-card text-purple-400"></i> Identity & Basic Info
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            {{-- Profile Pic Upload --}}
                            <div class="md:col-span-4 flex flex-col items-center justify-center p-6 bg-white/5 rounded-2xl border border-white/10">
                                <div class="h-32 w-32 rounded-full bg-slate-800 border-4 border-purple-500/30 overflow-hidden mb-4 relative group">
                                    @if(optional($profile)->profile_picture_path)
                                        <img src="{{ asset('storage/' . $profile->profile_picture_path) }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-purple-500 text-4xl font-bold">{{ substr($user->name, 0, 1) }}</div>
                                    @endif
                                </div>
                                <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Profile Picture</label>
                                <input type="file" name="profile_picture" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-500 cursor-pointer">
                            </div>

                            {{-- Basic Text Inputs --}}
                            <div class="md:col-span-8 space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Partner Name</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 h-11" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 h-11" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Company Type</label>
                                        <select name="company_type" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 h-11">
                                            <option value="Placement Agency" {{ (old('company_type', optional($profile)->company_type) == 'Placement Agency') ? 'selected' : '' }}>Placement Agency</option>
                                            <option value="Freelancer" {{ (old('company_type', optional($profile)->company_type) == 'Freelancer') ? 'selected' : '' }}>Freelancer</option>
                                            <option value="Recruiter" {{ (old('company_type', optional($profile)->company_type) == 'Recruiter') ? 'selected' : '' }}>Recruiter</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Establishment Year</label>
                                        <input type="number" name="establishment_year" value="{{ old('establishment_year', optional($profile)->establishment_year) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Bio / Description</label>
                                    <textarea name="bio" rows="2" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white">{{ old('bio', optional($profile)->bio) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: WORK & PREFERENCES --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-briefcase text-blue-400"></i> Work Preferences & Location
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-blue-300 uppercase mb-2">Preferred Categories</label>
                                <input type="text" name="preferred_categories" value="{{ old('preferred_categories', optional($profile)->preferred_categories) }}" placeholder="e.g. IT, Healthcare" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-blue-300 uppercase mb-2">Preferred Locations</label>
                                <input type="text" name="preferred_locations" value="{{ old('preferred_locations', optional($profile)->preferred_locations) }}" placeholder="e.g. Mumbai, Remote" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-blue-300 uppercase mb-2">Working Hours</label>
                                <input type="text" name="working_hours" value="{{ old('working_hours', optional($profile)->working_hours) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Full Address</label>
                                <textarea name="address" rows="1" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white">{{ old('address', optional($profile)->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: SOCIAL MEDIA --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-share-nodes text-pink-400"></i> Social Presence
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Website</label>
                                <input type="url" name="website" value="{{ old('website', optional($profile)->website) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">LinkedIn</label>
                                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', optional($profile)->linkedin_url) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Facebook</label>
                                <input type="url" name="facebook_url" value="{{ old('facebook_url', optional($profile)->facebook_url) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Instagram</label>
                                <input type="url" name="instagram_url" value="{{ old('instagram_url', optional($profile)->instagram_url) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Twitter / X</label>
                                <input type="url" name="twitter_url" value="{{ old('twitter_url', optional($profile)->twitter_url) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10">
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: BANKING --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-wallet text-emerald-400"></i> Banking Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Beneficiary Name</label>
                                <input type="text" name="beneficiary_name" value="{{ old('beneficiary_name', optional($profile)->beneficiary_name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Account Type</label>
                                <select name="account_type" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                                    <option value="">Select Type</option>
                                    <option value="Savings" {{ (old('account_type', optional($profile)->account_type) == 'Savings') ? 'selected' : '' }}>Savings</option>
                                    <option value="Current" {{ (old('account_type', optional($profile)->account_type) == 'Current') ? 'selected' : '' }}>Current</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Account Number</label>
                                <input type="text" name="account_number" value="{{ old('account_number', optional($profile)->account_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-emerald-300 uppercase mb-2">IFSC Code</label>
                                <input type="text" name="ifsc_code" value="{{ old('ifsc_code', optional($profile)->ifsc_code) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Upload Cancelled Cheque</label>
                                <input type="file" name="cancelled_cheque" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-emerald-600 file:text-white">
                                @if(optional($profile)->cancelled_cheque_path)
                                    <p class="text-xs text-emerald-400 mt-1"><i class="fa-solid fa-check"></i> File currently uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 5: COMPLIANCE (PAN & GST) --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-file-contract text-orange-400"></i> KYC & Compliance
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- PAN --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-orange-300 uppercase mb-2">PAN Name</label>
                                    <input type="text" name="pan_name" value="{{ old('pan_name', optional($profile)->pan_name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-orange-300 uppercase mb-2">PAN Number</label>
                                    <input type="text" name="pan_number" value="{{ old('pan_number', optional($profile)->pan_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-orange-300 uppercase mb-2">Upload PAN Card</label>
                                    <input type="file" name="pan_card" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-orange-600 file:text-white">
                                </div>
                            </div>

                            {{-- GST --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-orange-300 uppercase mb-2">GST Number</label>
                                    <input type="text" name="gst_number" value="{{ old('gst_number', optional($profile)->gst_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-11">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-orange-300 uppercase mb-2">Upload GST Certificate</label>
                                    <input type="file" name="gst_certificate" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-orange-600 file:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-4 border-t border-white/10 pt-8 mt-8 pb-12">
                    <a href="{{ route('admin.partners.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-purple-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
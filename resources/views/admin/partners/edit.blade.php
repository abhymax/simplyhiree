<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-6xl mx-auto">
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Partners
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Partner Profile</h1>
            </div>

            <form action="{{ route('admin.partners.update', $user->id) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- SECTION 1: BASIC INFO --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-user-pen text-purple-400"></i> Basic Information
                        </h3>
                        <div class="space-y-6">
                            <div><label class="block text-xs font-bold text-purple-300 uppercase mb-2">Partner Name</label><input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12" required></div>
                            <div><label class="block text-xs font-bold text-purple-300 uppercase mb-2">Email Address</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12" required></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Type</label>
                                    <select name="company_type" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12">
                                        <option value="Placement Agency" {{ (old('company_type', optional($profile)->company_type) == 'Placement Agency') ? 'selected' : '' }}>Placement Agency</option>
                                        <option value="Freelancer" {{ (old('company_type', optional($profile)->company_type) == 'Freelancer') ? 'selected' : '' }}>Freelancer</option>
                                        <option value="Recruiter" {{ (old('company_type', optional($profile)->company_type) == 'Recruiter') ? 'selected' : '' }}>Recruiter</option>
                                    </select>
                                </div>
                                <div><label class="block text-xs font-bold text-purple-300 uppercase mb-2">Est. Year</label><input type="text" name="establishment_year" value="{{ old('establishment_year', optional($profile)->establishment_year) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white transition h-12"></div>
                            </div>
                            <div><label class="block text-xs font-bold text-purple-300 uppercase mb-2">Bio</label><textarea name="bio" rows="3" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white transition">{{ old('bio', optional($profile)->bio) }}</textarea></div>
                        </div>
                    </div>

                    {{-- SECTION 2: SOCIAL --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-share-nodes text-blue-400"></i> Social & Contact
                        </h3>
                        <div class="space-y-4">
                            <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">Website</label><input type="url" name="website" value="{{ old('website', optional($profile)->website) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10"></div>
                            <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">LinkedIn</label><input type="url" name="linkedin_url" value="{{ old('linkedin_url', optional($profile)->linkedin_url) }}" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white h-10"></div>
                            <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">Address</label><textarea name="address" rows="2" class="w-full bg-slate-800/50 border border-white/10 rounded-xl text-white">{{ old('address', optional($profile)->address) }}</textarea></div>
                        </div>
                    </div>

                    {{-- SECTION 3: BANKING --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl lg:col-span-2">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <i class="fa-solid fa-building-columns text-emerald-400"></i> Banking & Compliance
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div><label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Beneficiary Name</label><input type="text" name="beneficiary_name" value="{{ old('beneficiary_name', optional($profile)->beneficiary_name) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12"></div>
                            <div><label class="block text-xs font-bold text-emerald-300 uppercase mb-2">Account Number</label><input type="text" name="account_number" value="{{ old('account_number', optional($profile)->account_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12"></div>
                            <div><label class="block text-xs font-bold text-emerald-300 uppercase mb-2">IFSC Code</label><input type="text" name="ifsc_code" value="{{ old('ifsc_code', optional($profile)->ifsc_code) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12"></div>
                            <div><label class="block text-xs font-bold text-emerald-300 uppercase mb-2">PAN Number</label><input type="text" name="pan_number" value="{{ old('pan_number', optional($profile)->pan_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12"></div>
                            <div><label class="block text-xs font-bold text-emerald-300 uppercase mb-2">GST Number</label><input type="text" name="gst_number" value="{{ old('gst_number', optional($profile)->gst_number) }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white h-12"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 border-t border-white/10 pt-8 mt-8 pb-12">
                    <a href="{{ route('admin.partners.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">Cancel</a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-purple-600/30 transition transform hover:-translate-y-1 flex items-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Update Partner</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
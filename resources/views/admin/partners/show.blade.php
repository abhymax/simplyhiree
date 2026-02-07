<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        <div class="absolute top-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end">
                <div>
                    <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Partners
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg flex items-center gap-3">
                        {{ $user->name }}
                        @if(optional($profile)->company_type)
                            <span class="text-sm bg-purple-500/20 text-purple-300 px-3 py-1 rounded-full border border-purple-500/30 align-middle">
                                {{ $profile->company_type }}
                            </span>
                        @endif
                    </h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Joined on {{ $user->created_at->format('F d, Y') }}</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex gap-3">
                    @if($user->status === 'active')
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 px-5 py-2 rounded-xl font-bold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
                        </span>
                    @else
                        <span class="bg-rose-500/20 text-rose-300 border border-rose-500/50 px-5 py-2 rounded-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-ban"></i> {{ ucfirst($user->status) }}
                        </span>
                    @endif
                    <a href="{{ route('admin.partners.edit', $user->id) }}" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-xl font-bold transition flex items-center gap-2 shadow-lg">
                        <i class="fa-solid fa-pen"></i> Edit Profile
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: IDENTITY & SOCIAL --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gradient-to-b from-slate-800 to-slate-900 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl text-center">
                        {{-- Profile Picture --}}
                        <div class="relative inline-block mb-4">
                            @if(optional($profile)->profile_picture_path)
                                <img src="{{ asset('storage/' . $profile->profile_picture_path) }}" class="h-32 w-32 rounded-full object-cover border-4 border-slate-700 shadow-xl bg-white">
                            @else
                                <div class="h-32 w-32 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-slate-700 shadow-xl">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="text-purple-300 text-sm mt-1">{{ $user->email }}</p>

                        {{-- SOCIAL ICONS --}}
                        <div class="flex justify-center gap-4 mt-6 pt-6 border-t border-white/10">
                            @if(optional($profile)->website) 
                                <a href="{{ $profile->website }}" target="_blank" class="text-slate-400 hover:text-white transition text-2xl" title="Website"><i class="fa-solid fa-globe"></i></a> 
                            @endif
                            @if(optional($profile)->linkedin_url) 
                                <a href="{{ $profile->linkedin_url }}" target="_blank" class="text-slate-400 hover:text-blue-400 transition text-2xl" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a> 
                            @endif
                            @if(optional($profile)->facebook_url) 
                                <a href="{{ $profile->facebook_url }}" target="_blank" class="text-slate-400 hover:text-blue-600 transition text-2xl" title="Facebook"><i class="fa-brands fa-facebook"></i></a> 
                            @endif
                            @if(optional($profile)->twitter_url) 
                                <a href="{{ $profile->twitter_url }}" target="_blank" class="text-slate-400 hover:text-sky-400 transition text-2xl" title="Twitter"><i class="fa-brands fa-twitter"></i></a> 
                            @endif
                            @if(optional($profile)->instagram_url) 
                                <a href="{{ $profile->instagram_url }}" target="_blank" class="text-slate-400 hover:text-pink-500 transition text-2xl" title="Instagram"><i class="fa-brands fa-instagram"></i></a> 
                            @endif
                        </div>

                        {{-- QUICK INFO (Website Added Here) --}}
                        <div class="mt-6 text-left space-y-3 bg-white/5 p-4 rounded-xl border border-white/5">
                            <div class="flex justify-between items-center border-b border-white/5 pb-2 mb-2">
                                <span class="text-xs text-slate-500 uppercase font-bold">Est. Year</span>
                                <span class="text-white font-medium">{{ optional($profile)->establishment_year ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-white/5 pb-2 mb-2">
                                <span class="text-xs text-slate-500 uppercase font-bold">Hours</span>
                                <span class="text-white font-medium">{{ optional($profile)->working_hours ?? 'Standard' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-500 uppercase font-bold">Website</span>
                                <a href="{{ optional($profile)->website ?? '#' }}" target="_blank" class="text-blue-400 hover:underline truncate max-w-[120px] text-right text-sm block">
                                    {{ optional($profile)->website ?? 'N/A' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: DETAILS --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- 1. Bio & Preferences --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-user-tag text-purple-400"></i> About & Preferences
                        </h3>
                        
                        <div class="mb-6">
                            <p class="text-xs font-bold text-slate-500 uppercase mb-2">Bio / Description</p>
                            <div class="bg-white/5 p-4 rounded-xl border border-white/5 text-slate-300 text-sm leading-relaxed italic">
                                "{{ optional($profile)->bio ?? 'No bio provided.' }}"
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Preferred Categories</span>
                                <span class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">
                                    {{ optional($profile)->preferred_categories ?? 'Any' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Preferred Locations</span>
                                <span class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">
                                    {{ optional($profile)->preferred_locations ?? 'Any' }}
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Address</span>
                                <span class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">
                                    {{ optional($profile)->address ?? 'No address provided' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Banking Details --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <i class="fa-solid fa-building-columns text-9xl text-white"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-emerald-400"></i> Banking Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-slate-400 uppercase font-bold">Beneficiary Name</p>
                                <p class="text-white font-mono text-lg">{{ optional($profile)->beneficiary_name ?? '-' }}</p>
                            </div>
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-slate-400 uppercase font-bold">Account Type</p>
                                <p class="text-white font-mono text-lg">{{ optional($profile)->account_type ?? '-' }}</p>
                            </div>
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-slate-400 uppercase font-bold">Account Number</p>
                                <p class="text-emerald-400 font-mono text-lg tracking-wider">{{ optional($profile)->account_number ?? '-' }}</p>
                            </div>
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-slate-400 uppercase font-bold">IFSC Code</p>
                                <p class="text-white font-mono text-lg">{{ optional($profile)->ifsc_code ?? '-' }}</p>
                            </div>
                            
                            {{-- Cheque Download --}}
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5 md:col-span-2 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">Cancelled Cheque</p>
                                    <p class="text-white text-sm">{{ optional($profile)->cancelled_cheque_path ? 'Document Available' : 'Not Uploaded' }}</p>
                                </div>
                                @if(optional($profile)->cancelled_cheque_path)
                                    <a href="{{ asset('storage/' . $profile->cancelled_cheque_path) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                                        <i class="fa-solid fa-download"></i> View / Download
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 3. Compliance & KYC --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-file-shield text-orange-400"></i> KYC & Compliance
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            {{-- PAN --}}
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-white/5 rounded-xl border border-white/10 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">PAN Details</p>
                                    <div class="flex flex-col">
                                        <span class="text-white font-mono text-lg">{{ optional($profile)->pan_number ?? 'N/A' }}</span>
                                        <span class="text-slate-400 text-xs">{{ optional($profile)->pan_name ?? '' }}</span>
                                    </div>
                                </div>
                                @if(optional($profile)->pan_card_path)
                                    <a href="{{ asset('storage/' . $profile->pan_card_path) }}" target="_blank" class="text-orange-400 hover:text-white transition flex items-center gap-2 text-sm font-bold">
                                        <i class="fa-solid fa-eye"></i> View Card
                                    </a>
                                @else
                                    <span class="text-slate-500 text-xs italic">No file uploaded</span>
                                @endif
                            </div>

                            {{-- GST --}}
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-white/5 rounded-xl border border-white/10 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">GST Details</p>
                                    <span class="text-white font-mono text-lg">{{ optional($profile)->gst_number ?? 'N/A' }}</span>
                                </div>
                                @if(optional($profile)->gst_certificate_path)
                                    <a href="{{ asset('storage/' . $profile->gst_certificate_path) }}" target="_blank" class="text-orange-400 hover:text-white transition flex items-center gap-2 text-sm font-bold">
                                        <i class="fa-solid fa-eye"></i> View Certificate
                                    </a>
                                @else
                                    <span class="text-slate-500 text-xs italic">No file uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
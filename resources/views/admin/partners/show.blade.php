<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end">
                <div>
                    <a href="{{ route('admin.partners.index') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Partners
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">{{ $user->name }}</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">{{ $profile->company_type ?? 'Recruitment Partner' }} Profile</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex gap-3">
                    @if($user->status === 'active')
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 px-5 py-2 rounded-xl font-bold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active Partner
                        </span>
                    @else
                        <span class="bg-rose-500/20 text-rose-300 border border-rose-500/50 px-5 py-2 rounded-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-ban"></i> {{ ucfirst($user->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- LEFT COLUMN: PROFILE CARD --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gradient-to-b from-slate-800 to-slate-900 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl text-center">
                        {{-- Avatar --}}
                        <div class="relative inline-block mb-4">
                            @if($profile && $profile->profile_picture_path)
                                <img src="{{ asset('storage/'.$profile->profile_picture_path) }}" class="h-32 w-32 rounded-full object-cover border-4 border-slate-700 shadow-xl">
                            @else
                                <div class="h-32 w-32 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-slate-700 shadow-xl">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="text-purple-300 text-sm mt-1">{{ $user->email }}</p>

                        {{-- Social Links --}}
                        <div class="flex justify-center gap-4 mt-6">
                            @if(!empty($profile->linkedin_url)) <a href="{{ $profile->linkedin_url }}" target="_blank" class="text-slate-400 hover:text-blue-400 transition text-2xl"><i class="fa-brands fa-linkedin"></i></a> @endif
                            @if(!empty($profile->facebook_url)) <a href="{{ $profile->facebook_url }}" target="_blank" class="text-slate-400 hover:text-blue-600 transition text-2xl"><i class="fa-brands fa-facebook"></i></a> @endif
                            @if(!empty($profile->twitter_url)) <a href="{{ $profile->twitter_url }}" target="_blank" class="text-slate-400 hover:text-sky-400 transition text-2xl"><i class="fa-brands fa-twitter"></i></a> @endif
                            @if(!empty($profile->instagram_url)) <a href="{{ $profile->instagram_url }}" target="_blank" class="text-slate-400 hover:text-pink-500 transition text-2xl"><i class="fa-brands fa-instagram"></i></a> @endif
                        </div>

                        {{-- Quick Info --}}
                        <div class="mt-8 text-left space-y-3 bg-white/5 p-4 rounded-xl border border-white/5">
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold">Est. Year</p>
                                <p class="text-white font-medium">{{ $profile->establishment_year ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold">Website</p>
                                <a href="{{ $profile->website ?? '#' }}" target="_blank" class="text-blue-400 hover:underline truncate block">{{ $profile->website ?? 'N/A' }}</a>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold">Location</p>
                                <p class="text-white font-medium text-sm">{{ $profile->address ?? 'No address provided' }}</p>
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
                            <p class="text-slate-300 text-sm leading-relaxed italic">"{{ $profile->bio ?? 'No bio provided.' }}"</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Preferred Locations</span>
                                <span class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">{{ $profile->preferred_locations ?? 'Any' }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Working Hours</span>
                                <span class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">{{ $profile->working_hours ?? 'Standard' }}</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-xs font-bold text-slate-500 uppercase block mb-1">Preferred Categories</span>
                            <div class="text-white text-sm bg-white/5 px-3 py-2 rounded-lg block border border-white/5">
                                {{ $profile->preferred_categories ?? 'General Recruitment' }}
                            </div>
                        </div>
                    </div>

                    {{-- 2. Banking Details (Secured Look) --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <i class="fa-solid fa-building-columns text-8xl text-emerald-500"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-emerald-400"></i> Banking Information
                        </h3>
                        
                        @if($profile)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                                <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                    <p class="text-xs text-slate-400 uppercase font-bold">Beneficiary Name</p>
                                    <p class="text-white font-mono text-lg">{{ $profile->beneficiary_name ?? '-' }}</p>
                                </div>
                                <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                    <p class="text-xs text-slate-400 uppercase font-bold">Account Number</p>
                                    <p class="text-emerald-400 font-mono text-lg tracking-wider">{{ $profile->account_number ?? '-' }}</p>
                                </div>
                                <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                    <p class="text-xs text-slate-400 uppercase font-bold">IFSC Code</p>
                                    <p class="text-white font-mono text-lg">{{ $profile->ifsc_code ?? '-' }}</p>
                                </div>
                                <div class="bg-black/20 p-4 rounded-xl border border-white/5 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-slate-400 uppercase font-bold">Cheque Copy</p>
                                        <p class="text-white text-sm">{{ $profile->cancelled_cheque_path ? 'Uploaded' : 'Pending' }}</p>
                                    </div>
                                    @if($profile->cancelled_cheque_path)
                                        <a href="{{ asset('storage/'.$profile->cancelled_cheque_path) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">View</a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-slate-400 italic">No banking details submitted.</p>
                        @endif
                    </div>

                    {{-- 3. Compliance Docs --}}
                    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-lg">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-file-shield text-blue-400"></i> KYC & Compliance
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- PAN --}}
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/10">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">PAN Number</p>
                                    <p class="text-white font-mono">{{ $profile->pan_number ?? 'N/A' }}</p>
                                </div>
                                @if($profile->pan_card_path)
                                    <a href="{{ asset('storage/'.$profile->pan_card_path) }}" target="_blank" class="text-blue-400 hover:text-white transition"><i class="fa-solid fa-eye text-xl"></i></a>
                                @endif
                            </div>

                            {{-- GST --}}
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/10">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold">GST Number</p>
                                    <p class="text-white font-mono">{{ $profile->gst_number ?? 'N/A' }}</p>
                                </div>
                                @if($profile->gst_certificate_path)
                                    <a href="{{ asset('storage/'.$profile->gst_certificate_path) }}" target="_blank" class="text-blue-400 hover:text-white transition"><i class="fa-solid fa-eye text-xl"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
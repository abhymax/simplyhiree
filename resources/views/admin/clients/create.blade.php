<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-4xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Client List
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Onboard New Client</h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Create a new client account for job postings.</p>
            </div>

            {{-- ERROR HANDLING --}}
            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-500/20 border border-rose-500/50 rounded-2xl backdrop-blur-md shadow-lg">
                    <div class="flex items-center gap-2 text-rose-300 font-bold mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i> Submission Failed
                    </div>
                    <ul class="list-disc list-inside text-rose-100 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                
                {{-- MAIN FORM CONTAINER --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-user-tie text-emerald-400"></i> Client Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Name --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Company / Client Name <span class="text-rose-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Acme Solutions" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white text-lg font-bold placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-14" required>
                        </div>

                        {{-- Email --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Email Address (Login ID) <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="client@company.com" class="w-full pl-10 bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12" required>
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Phone Number (India) <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="9876543210" pattern="[6-9][0-9]{9}" class="w-full pl-10 bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12" required>
                            </div>
                        </div>

                        {{-- Billable Days --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Billable Period Days</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-regular fa-calendar"></i></span>
                                <input type="number" name="billable_period_days" value="{{ old('billable_period_days', 30) }}" min="1" max="365" class="w-full pl-10 bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Password <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="w-full pl-10 bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12" required>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Confirm Password <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password_confirmation" class="w-full pl-10 bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-12" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-4 border-t border-white/10 pt-8 pb-12">
                    <a href="{{ route('admin.clients.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl font-bold shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Register Client
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

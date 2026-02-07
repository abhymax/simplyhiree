<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-2xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6 text-center">
                <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Onboard New Partner</h1>
                <p class="text-purple-200 mt-2 text-lg">Create access for agencies or freelance recruiters.</p>
            </div>

            {{-- ERROR HANDLING --}}
            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-500/20 border border-rose-500/50 rounded-2xl backdrop-blur-md shadow-lg">
                    <ul class="list-disc list-inside text-rose-100 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.partners.store') }}" method="POST">
                @csrf
                
                {{-- MAIN FORM CONTAINER --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl space-y-6">
                    
                    {{-- Name --}}
                    <div>
                        <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Partner / Agency Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Apex Recruiters" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white text-lg font-bold placeholder-slate-500 focus:ring-2 focus:ring-purple-500 transition h-14" required autofocus>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="partner@example.com" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 transition h-12" required>
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Partner Type</label>
                        <select name="company_type" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12" required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="Placement Agency" {{ old('company_type') == 'Placement Agency' ? 'selected' : '' }}>Placement Agency</option>
                            <option value="Freelancer" {{ old('company_type') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                            <option value="Recruiter" {{ old('company_type') == 'Recruiter' ? 'selected' : '' }}>Recruiter</option>
                        </select>
                    </div>

                    {{-- Password Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Password</label>
                            <input type="password" name="password" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-purple-300 uppercase mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 transition h-12" required>
                        </div>
                    </div>

                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-4 mt-8">
                    <a href="{{ route('admin.partners.index') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Cancel
                    </a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-purple-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> Create Partner
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
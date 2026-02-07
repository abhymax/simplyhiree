<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.jobs.pending') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Cancel & Return
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Create New Vacancy</h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Post a job, configure visibility, and set commercials.</p>
            </div>

            {{-- ERROR HANDLING --}}
            @if ($errors->any())
                <div class="mb-8 p-6 bg-rose-500/20 border border-rose-500/50 rounded-2xl backdrop-blur-md shadow-lg">
                    <div class="flex items-center gap-2 text-rose-300 font-bold mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i> Please fix the following errors:
                    </div>
                    <ul class="list-disc list-inside text-rose-100 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.jobs.store') }}" method="POST" x-data="{ visibility: 'all', clientMode: 'simplyhiree' }">
                @csrf
                
                {{-- SECTION 1: POSTING CONTEXT --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-building-user text-blue-400"></i> Posting Context
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Post On Behalf Of</label>
                            <select name="client_id" x-model="clientMode" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12">
                                <option value="" class="text-gray-400">SimplyHiree (Internal)</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} (Client)</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="clientMode == ''" x-transition>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Display Company Name</label>
                            <input type="text" name="company_name" value="Simplyhiree" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Company Website <span class="text-slate-500 normal-case">(Optional)</span></label>
                            <input type="url" name="company_website" value="{{ old('company_website') }}" placeholder="https://" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition h-12">
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: JOB SPECIFICATION --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-briefcase text-purple-400"></i> Job Specification
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Job Title <span class="text-rose-400">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Senior Laravel Developer" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white text-lg font-bold placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-14" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Category <span class="text-rose-400">*</span></label>
                            <select name="category_id" required class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Job Type <span class="text-rose-400">*</span></label>
                            <select name="job_type" required class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Select Type...</option>
                                <option value="Full-time" {{ old('job_type') == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                <option value="Part-time" {{ old('job_type') == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                <option value="Contract" {{ old('job_type') == 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Internship" {{ old('job_type') == 'Internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Location <span class="text-rose-400">*</span></label>
                            <input type="text" name="location" value="{{ old('location') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Salary / CTC</label>
                            <input type="text" name="salary" value="{{ old('salary') }}" placeholder="e.g. 5-7 LPA" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>

                        {{-- Education Level --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Education <span class="text-rose-400">*</span></label>
                            <select name="education_level_id" required class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Select Education...</option>
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}" {{ old('education_level_id') == $edu->id ? 'selected' : '' }}>{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Application Deadline --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Application Deadline</label>
                            <input type="date" name="application_deadline" value="{{ old('application_deadline') }}" min="{{ date('Y-m-d') }}" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>

                        {{-- Total Openings --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Total Openings</label>
                            <input type="number" name="openings" value="{{ old('openings', 1) }}" min="1" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>

                        {{-- Experience Range (Row) --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Experience Range (Years) <span class="text-rose-400">*</span></label>
                            <div class="flex space-x-3">
                                <div class="w-1/2">
                                    <input type="number" name="min_experience" placeholder="Min" value="{{ old('min_experience') }}" min="0" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12" required>
                                </div>
                                <div class="w-1/2">
                                    <input type="number" name="max_experience" placeholder="Max" value="{{ old('max_experience') }}" min="0" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Job Description <span class="text-rose-400">*</span></label>
                        <textarea name="description" rows="5" required class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Skills Required (Comma separated)</label>
                        <input type="text" name="skills_required" value="{{ old('skills_required') }}" placeholder="e.g. PHP, Laravel, MySQL" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                    </div>
                </div>

                {{-- SECTION 3: COMMERCIALS --}}
                <div class="bg-gradient-to-br from-amber-900/40 to-orange-900/40 backdrop-blur-xl border border-amber-500/30 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-amber-300 mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-coins text-amber-400"></i> Payout Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-amber-200 uppercase mb-2">Payout Amount (₹)</label>
                            <input type="number" name="payout_amount" value="{{ old('payout_amount') }}" class="w-full bg-slate-900/80 border border-amber-500/30 rounded-xl text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-amber-200 uppercase mb-2">Maturity Period (Days)</label>
                            <input type="number" name="minimum_stay_days" value="{{ old('minimum_stay_days', 30) }}" class="w-full bg-slate-900/80 border border-amber-500/30 rounded-xl text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-12">
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: PARTNER VISIBILITY --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-eye text-emerald-400"></i> Partner Visibility
                    </h3>
                    
                    <div class="flex items-center gap-6 mb-6">
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="partner_visibility" value="all" x-model="visibility" class="text-emerald-500 bg-slate-800 border-slate-600 focus:ring-emerald-500">
                            <span class="ml-2 text-white group-hover:text-emerald-300 transition">Visible to All Active Partners</span>
                        </label>
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="partner_visibility" value="selected" x-model="visibility" class="text-emerald-500 bg-slate-800 border-slate-600 focus:ring-emerald-500">
                            <span class="ml-2 text-white group-hover:text-emerald-300 transition">Restrict to Specific Partners</span>
                        </label>
                    </div>

                    {{-- Partner Selection Logic --}}
                    <div x-show="visibility === 'selected'" class="mt-4 p-6 bg-slate-800/50 rounded-2xl border border-white/10" x-transition>
                        <p class="text-sm font-bold text-emerald-300 mb-4 uppercase">Select Allowed Partners:</p>
                        <div class="h-48 overflow-y-auto grid grid-cols-1 md:grid-cols-3 gap-3 pr-2 custom-scrollbar">
                            @foreach($partners as $partner)
                                <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 transition cursor-pointer border border-transparent hover:border-emerald-500/30">
                                    <input type="checkbox" name="allowed_partners[]" value="{{ $partner->id }}" class="rounded bg-slate-900 border-slate-600 text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-200">{{ $partner->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SECTION 5: RESTRICTED CANDIDATES --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-user-lock text-rose-400"></i> Restricted Candidates
                    </h3>
                    <p class="text-sm text-blue-200 mb-6">Select candidates who should <strong>NOT</strong> see this job.</p>
                    
                    <div class="h-48 overflow-y-auto p-4 bg-slate-800/50 rounded-2xl border border-white/10 custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($candidates as $candidate)
                                <label class="flex items-center space-x-3 p-2 rounded hover:bg-rose-500/10 transition cursor-pointer group">
                                    <input type="checkbox" name="restricted_candidates[]" value="{{ $candidate->id }}" class="rounded bg-slate-900 border-slate-600 text-rose-500 focus:ring-rose-500">
                                    <span class="text-sm text-slate-300 group-hover:text-white">
                                        {{ $candidate->first_name }} {{ $candidate->last_name }} 
                                        <span class="text-xs text-slate-500">({{ $candidate->email }})</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end gap-4 border-t border-white/10 pt-8 pb-12">
                    <a href="{{ route('admin.jobs.pending') }}" class="px-8 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold transition border border-white/10">
                        Discard
                    </a>
                    <button type="submit" class="px-10 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Publish Job
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Custom Scrollbar Style --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
    </style>
</x-app-layout>
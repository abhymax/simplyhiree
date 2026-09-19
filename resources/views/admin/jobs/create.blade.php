<x-app-layout>
    @php
        $jobTypeOptions = ['Full-time','Part-time','Contract','Contract-to-Hire (C2H)','Internship','Freelance','Temporary','Permanent','Trainee','Consultant'];
        $workModeOptions = ['On-site','Hybrid','Remote','Field Job','Work From Home (WFH)'];
        $shiftOptions = ['Day Shift','Night Shift','Rotational Shift','Flexible Shift','Weekend Shift'];
        $benefitOptions = ['PF','ESIC','Insurance','Food','Transport','Accommodation','Laptop','Mobile','Joining Bonus','Relocation'];
        $jobBenefits = old('benefits', []);
        if (!is_array($jobBenefits)) { $jobBenefits = []; }
        $travelRequiredVal = (string) old('travel_required', '');
    @endphp
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

            <form action="{{ route('admin.jobs.store') }}" method="POST" x-data="{ visibility: 'all', clientMode: 'simplyhiree', candidateSearch: '' }">
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

                    {{-- Confidentiality toggle --}}
                    <div class="mt-5 pt-4 border-t border-white/10">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="hidden" name="is_company_confidential" value="0">
                            <input type="checkbox" name="is_company_confidential" value="1"
                                   {{ old('is_company_confidential') ? 'checked' : '' }}
                                   class="mt-1 h-5 w-5 rounded border-white/40 bg-slate-800 text-blue-500 focus:ring-2 focus:ring-blue-400">
                            <div>
                                <div class="text-white font-bold text-sm flex items-center gap-2">
                                    <i class="fa-solid fa-user-secret text-amber-300"></i> Keep Company Name Confidential
                                </div>
                                <p class="text-slate-400 text-xs mt-0.5">Vendors and candidates will see this posting as "Confidential" until the company is revealed.</p>
                            </div>
                        </label>
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
                                @foreach($jobTypeOptions as $jt)
                                    <option value="{{ $jt }}" {{ old('job_type') == $jt ? 'selected' : '' }}>{{ $jt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Location(s) <span class="text-rose-400">*</span></label>
                            <input type="hidden" name="location" id="job-location" value="{{ old('location') }}">
                            <div id="job-location-chipbox"
                                class="flex min-h-[48px] flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-slate-800/80 px-3 py-2 focus-within:ring-2 focus-within:ring-purple-500 focus-within:border-purple-500">
                                <input type="text" id="job-location-search" autocomplete="off"
                                    class="flex-1 min-w-[160px] border-0 bg-transparent text-white placeholder-slate-500 focus:outline-none focus:ring-0 p-1"
                                    placeholder="Type a city, press Enter to add">
                            </div>
                            <div id="job-location-suggestions"
                                class="absolute left-0 right-0 top-full z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-slate-600 bg-slate-900 shadow-2xl ring-1 ring-slate-700"></div>
                            <p class="mt-2 text-xs" style="color:#fff;">Pick one or more cities. Type any custom location and press <kbd style="color:#fff;background:rgba(255,255,255,0.18);padding:1px 6px;border-radius:4px;font-size:10px;">Enter</kbd> or <kbd style="color:#fff;background:rgba(255,255,255,0.18);padding:1px 6px;border-radius:4px;font-size:10px;">,</kbd> to add it.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Salary Range (INR / Annum)</label>
                            <div class="flex space-x-3">
                                <input type="number" name="min_salary" placeholder="Min Salary" value="{{ old('min_salary') }}" min="0" class="w-1/2 bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <input type="number" name="max_salary" placeholder="Max Salary" value="{{ old('max_salary') }}" min="0" class="w-1/2 bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Gender Preference <span class="text-rose-400">*</span></label>
                            <select name="gender_preference" required class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                @foreach(['Any','Male','Female','Other'] as $gp)
                                    <option value="{{ $gp }}" {{ old('gender_preference', 'Any') === $gp ? 'selected' : '' }}>{{ $gp }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Age Range (optional) --}}
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Age Range (optional)</label>
                            <div class="flex space-x-3">
                                <input type="number" name="min_age" placeholder="Min Age" value="{{ old('min_age') }}" min="18" max="80" class="w-1/2 bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <input type="number" name="max_age" placeholder="Max Age" value="{{ old('max_age') }}" min="18" max="80" class="w-1/2 bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                            </div>
                            @error('min_age') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                            @error('max_age') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <input type="hidden" name="description" id="job-description-input" value="{{ old('description') }}">
                        <div id="job-description-editor" class="bg-slate-800/80 rounded-xl border border-white/10 text-white"></div>
                        <p class="mt-2 text-xs" style="color:#fff;">Paste from Word or use the advanced toolbar for fonts, tables, images, case conversion, links, alignment, lists and more.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Skills Required (Comma separated)</label>
                        <input type="text" name="skills_required" value="{{ old('skills_required') }}" placeholder="e.g. PHP, Laravel, MySQL" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                    </div>
                </div>

                {{-- SECTION 2B: ADDITIONAL DETAILS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-cyan-400"></i> Additional Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Work Mode</label>
                            <select name="work_mode" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Select Work Mode...</option>
                                @foreach($workModeOptions as $wm)
                                    <option value="{{ $wm }}" {{ old('work_mode') === $wm ? 'selected' : '' }}>{{ $wm }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Shift</label>
                            <select name="shift" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Select Shift...</option>
                                @foreach($shiftOptions as $sh)
                                    <option value="{{ $sh }}" {{ old('shift') === $sh ? 'selected' : '' }}>{{ $sh }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Specialization</label>
                            <input type="text" name="specialization" value="{{ old('specialization') }}" placeholder="e.g. Taxation, Frontend, Cardiology" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Notice Period</label>
                            <input type="text" name="notice_period" value="{{ old('notice_period') }}" placeholder="e.g. Immediate, 30 days, 60 days" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Languages</label>
                            <input type="text" name="languages" value="{{ old('languages') }}" placeholder="e.g. English, Hindi, Tamil" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Industry</label>
                            <input type="text" name="industry" value="{{ old('industry') }}" placeholder="e.g. IT, Manufacturing, Healthcare" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Department</label>
                            <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Finance, Engineering, Sales" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Reporting Manager</label>
                            <input type="text" name="reporting_manager" value="{{ old('reporting_manager') }}" placeholder="e.g. Head of Finance" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-2">Travel Required</label>
                            <select name="travel_required" class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition h-12">
                                <option value="">Not specified</option>
                                <option value="1" {{ $travelRequiredVal === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $travelRequiredVal === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-3">Benefits</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                            @foreach($benefitOptions as $benefit)
                                <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-800/60 px-3 py-2.5 cursor-pointer hover:border-cyan-400/40 transition">
                                    <input type="checkbox" name="benefits[]" value="{{ $benefit }}" @checked(in_array($benefit, $jobBenefits, true)) class="rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                    <span class="text-sm text-slate-200">{{ $benefit }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SECTION: ASSESSMENT QUESTIONNAIRES --}}
                <div class="mb-8">
                    @include('jobs.partials.assessment-stages', ['availableAssessments' => $availableAssessments ?? collect(), 'job' => null, 'assessmentCreateRoute' => 'admin.assessments.create'])
                </div>

                {{-- SECTION 3: PAYOUT SETTINGS --}}
                <div class="bg-gradient-to-br from-amber-900/40 to-orange-900/40 backdrop-blur-xl border border-amber-500/30 rounded-3xl p-8 mb-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-amber-300 mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-coins text-amber-400"></i> Payout Settings
                    </h3>
                    <p class="text-amber-200/70 text-xs mb-6">Payout amount per successful hire, the maturity period before release, and the replacement-guarantee window.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-amber-200 uppercase mb-2">Payout Amount (₹)</label>
                            <input type="number" name="payout_amount" value="{{ old('payout_amount') }}" class="w-full bg-slate-900/80 border border-amber-500/30 rounded-xl text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-12 px-3">
                            @error('payout_amount') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-amber-200 uppercase mb-2">Maturity Period (Days)</label>
                            <input type="number" name="minimum_stay_days" value="{{ old('minimum_stay_days', 30) }}" class="w-full bg-slate-900/80 border border-amber-500/30 rounded-xl text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-12 px-3">
                            @error('minimum_stay_days') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-amber-200 uppercase mb-2">Replacement Guarantee (Days)</label>
                            <input type="number" name="replacement_guarantee_days" min="0" max="365" value="{{ old('replacement_guarantee_days', 90) }}" class="w-full bg-slate-900/80 border border-amber-500/30 rounded-xl text-white font-bold focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition h-12 px-3">
                            @error('replacement_guarantee_days') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
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
                    <p class="text-sm text-blue-200 mb-4">Select candidates who should <strong>NOT</strong> see this job.</p>
                    
                    {{-- Candidate search input --}}
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" 
                                   x-model="candidateSearch" 
                                   placeholder="Search candidates by name or email..." 
                                   class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition h-10 px-4 pl-10 text-sm">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </div>
                            <button type="button" 
                                    x-show="candidateSearch" 
                                    @click="candidateSearch = ''" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="h-48 overflow-y-auto p-4 bg-slate-800/50 rounded-2xl border border-white/10 custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($candidates as $candidate)
                                <label data-search="{{ strtolower($candidate->first_name . ' ' . $candidate->last_name . ' ' . $candidate->email) }}"
                                       x-show="!candidateSearch || $el.getAttribute('data-search').includes(candidateSearch.toLowerCase())"
                                       class="flex items-center space-x-3 p-2 rounded hover:bg-rose-500/10 transition cursor-pointer group">
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

    @include('jobs.partials.advanced-editor', ['uploadUrl' => route('admin.jobs.description-images.store')])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Multi-location chip input ──────────────────────────────────────
        const hidden     = document.getElementById('job-location');
        const chipbox    = document.getElementById('job-location-chipbox');
        const search     = document.getElementById('job-location-search');
        const suggestions= document.getElementById('job-location-suggestions');
        if (!hidden || !chipbox || !search || !suggestions) return;

        let cities = [];
        let selected = (hidden.value || '').split(',').map(s => s.trim()).filter(Boolean);

        const hideSuggestions = () => { suggestions.classList.add('hidden'); suggestions.innerHTML = ''; };
        const syncHidden = () => { hidden.value = selected.join(', '); };

        const renderChips = () => {
            chipbox.querySelectorAll('.loc-chip').forEach(c => c.remove());
            selected.forEach((city, idx) => {
                const chip = document.createElement('span');
                chip.className = 'loc-chip inline-flex items-center gap-1.5 rounded-full bg-purple-500/30 border border-purple-400/40 px-3 py-1 text-sm text-white';
                chip.innerHTML = '<span>' + city.replace(/</g,'&lt;') + '</span><button type="button" aria-label="Remove" class="hover:text-rose-300 leading-none text-base">&times;</button>';
                chip.querySelector('button').addEventListener('click', () => { selected.splice(idx, 1); syncHidden(); renderChips(); });
                chipbox.insertBefore(chip, search);
            });
        };

        const addCity = (raw) => {
            const city = (raw || '').trim().replace(/,+$/, '');
            if (!city) return;
            if (!selected.some(s => s.toLowerCase() === city.toLowerCase())) {
                selected.push(city);
                syncHidden();
                renderChips();
            }
            search.value = '';
            hideSuggestions();
        };

        const showSuggestions = (matches) => {
            if (!matches.length) { hideSuggestions(); return; }
            suggestions.innerHTML = matches.map(c =>
                '<button type="button" class="job-location-option block w-full border-b border-slate-700 px-4 py-3 text-left text-sm font-medium text-white hover:bg-purple-600 transition last:border-b-0">' + c + '</button>'
            ).join('');
            suggestions.classList.remove('hidden');
            suggestions.querySelectorAll('.job-location-option').forEach(btn => {
                btn.addEventListener('click', () => addCity(btn.textContent));
            });
        };

        const updateSuggestions = () => {
            const q = search.value.trim().toLowerCase();
            if (q.length < 2) { hideSuggestions(); return; }
            const matches = cities
                .filter(c => c.toLowerCase().includes(q) && !selected.some(s => s.toLowerCase() === c.toLowerCase()))
                .slice(0, 12);
            showSuggestions(matches);
        };

        search.addEventListener('input', updateSuggestions);
        search.addEventListener('focus', updateSuggestions);
        search.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); if (search.value.trim()) addCity(search.value); }
            else if (e.key === 'Backspace' && !search.value && selected.length) { selected.pop(); syncHidden(); renderChips(); }
            else if (e.key === 'Escape') hideSuggestions();
        });
        chipbox.addEventListener('click', (e) => { if (e.target === chipbox) search.focus(); });
        document.addEventListener('click', (e) => {
            if (!suggestions.contains(e.target) && e.target !== search) hideSuggestions();
        });

        const form = chipbox.closest('form');
        if (form) form.addEventListener('submit', (e) => {
            if (search.value.trim()) addCity(search.value);
            if (!selected.length) {
                e.preventDefault();
                search.focus();
                alert('Please add at least one location.');
            }
        });

        renderChips();

        fetch(@js(asset('data/indian-cities.json')))
            .then(r => r.ok ? r.json() : [])
            .then(d => { if (Array.isArray(d)) cities = d; })
            .catch(() => { cities = []; });
    });
    </script>
</x-app-layout>

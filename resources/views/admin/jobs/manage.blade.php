<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glow Effects --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-15 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>

        <div class="relative z-10 max-w-5xl mx-auto">
            
            {{-- HEADER --}}
            <div class="mb-8 border-b border-white/10 pb-6">
                <a href="{{ route('admin.jobs.pending') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-4 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Pending Jobs
                </a>
                <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg flex items-center gap-3">
                    <i class="fa-solid fa-handshake text-cyan-400"></i> Manage Partners
                </h1>
                <p class="text-blue-200 mt-1 text-lg font-medium">Configure allowed recruitment agencies for this job vacancy.</p>
            </div>

            {{-- JOB CONTEXT CARD --}}
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 mb-8 shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10">
                    <i class="fa-solid fa-briefcase text-8xl text-white"></i>
                </div>
                
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-white mb-1">{{ $job->title }}</h2>
                    <div class="flex items-center gap-2 text-amber-300 font-bold mb-6 text-sm">
                        <i class="fa-solid fa-building"></i> {{ $job->company_name }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Location</span>
                            <span class="text-white font-medium"><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i> {{ $job->location }}</span>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Experience</span>
                            <span class="text-white font-medium">{{ $job->experienceLevel->name ?? 'Not Specified' }}</span>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-xl border border-white/10">
                            <span class="block text-xs font-bold text-blue-300 uppercase mb-1">Education</span>
                            <span class="text-white font-medium">{{ $job->educationLevel->name ?? 'Not Specified' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INCLUSION FORM --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden p-8">
                
                <form action="{{ route('admin.jobs.exclusions.update', $job->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-8">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-white mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-check-double text-emerald-500"></i> Include Partners
                                </h3>
                                <p class="text-xs text-slate-300 font-medium">Only selected partners will be able to see and submit candidates for this job vacancy.</p>
                            </div>
                            
                            {{-- Dynamic Select All / Clear All buttons --}}
                            <div class="flex items-center gap-3">
                                <button type="button" id="select-all-partners" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-cyan-300 rounded-lg border border-white/10 transition">
                                    Select All
                                </button>
                                <button type="button" id="clear-all-partners" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-rose-300 rounded-lg border border-white/10 transition">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        {{-- Search Input with High Contrast Typography & Spaced Icon --}}
                        <div class="mb-5 relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-lg"></i>
                            </div>
                            <input type="text" id="partner-search-input" placeholder="Search sourcing partners by name or email..." 
                                   class="block w-full pl-14 pr-4 py-3.5 bg-slate-950 border border-white/10 rounded-2xl placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all shadow-inner"
                                   style="background-color: #0c152b !important; color: #ffffff !important; font-size: 1rem !important;">
                        </div>

                        {{-- FILTER & QUICK-SELECT CONTROLS --}}
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Filter Pills --}}
                            <div class="bg-slate-950/40 p-4 rounded-2xl border border-white/10 shadow-lg">
                                <span class="block text-xs font-extrabold text-cyan-300 uppercase tracking-wider mb-1"><i class="fa-solid fa-filter mr-1.5 text-cyan-400"></i> Filter Grid View</span>
                                <span class="block text-[10px] text-slate-400 mb-3">(Shows or hides partners on the screen below)</span>
                                <div class="flex flex-wrap gap-2" id="filter-pills-container">
                                    <button type="button" data-filter="all" class="filter-btn px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-full transition-all border border-emerald-500/30 shadow-md">All</button>
                                    <button type="button" data-filter-plan="free" class="filter-btn px-4 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-full transition-all border border-white/5 hover:border-slate-500/50">Free</button>
                                    <button type="button" data-filter-plan="basic" class="filter-btn px-4 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-full transition-all border border-white/5 hover:border-slate-500/50">Basic</button>
                                    <button type="button" data-filter-plan="pro" class="filter-btn px-4 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-full transition-all border border-white/5 hover:border-slate-500/50">Pro</button>
                                    <button type="button" data-filter-plan="enterprise" class="filter-btn px-4 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-full transition-all border border-white/5 hover:border-slate-500/50">Enterprise</button>
                                    <button type="button" data-filter-type="freelancer" class="filter-btn px-4 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-full transition-all border border-white/5 hover:border-slate-500/50">Freelancer</button>
                                </div>
                            </div>

                            {{-- Category Wise Quick Select --}}
                            <div class="bg-slate-950/40 p-4 rounded-2xl border border-white/10 shadow-lg">
                                <span class="block text-xs font-extrabold text-emerald-400 uppercase tracking-wider mb-1"><i class="fa-solid fa-square-check mr-1.5 text-emerald-400"></i> Category-wise Selection</span>
                                <span class="block text-[10px] text-slate-400 mb-3">(Instantly selects or clears checkboxes of matching partners)</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" data-select-plan="free" class="category-select-btn px-3 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-white/5 hover:border-slate-500/50 transition-all active:scale-95">Free</button>
                                    <button type="button" data-select-plan="basic" class="category-select-btn px-3 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-white/5 hover:border-slate-500/50 transition-all active:scale-95">Basic</button>
                                    <button type="button" data-select-plan="pro" class="category-select-btn px-3 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-white/5 hover:border-slate-500/50 transition-all active:scale-95">Pro</button>
                                    <button type="button" data-select-plan="enterprise" class="category-select-btn px-3 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-white/5 hover:border-slate-500/50 transition-all active:scale-95">Enterprise</button>
                                    <button type="button" data-select-type="freelancer" class="category-select-btn px-3 py-1.5 bg-slate-800/85 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-white/5 hover:border-slate-500/50 transition-all active:scale-95">Freelancer</button>
                                </div>
                            </div>
                        </div>

                        @if($allPartners->isEmpty())
                            <div class="bg-amber-500/20 border border-amber-500/50 p-6 rounded-2xl text-center">
                                <p class="text-amber-300 font-bold text-lg">No partners found in the system.</p>
                                <p class="text-amber-200 text-sm mt-1">Onboard partners first to manage visibility.</p>
                            </div>
                        @else
                            {{-- Partner Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[480px] overflow-y-auto pr-2 custom-scrollbar" id="partners-grid-container">
                                @foreach($allPartners as $partner)
                                    @php
                                        $isAllowed = in_array($partner->id, $allowedPartnerIds);
                                    @endphp
                                    <label class="partner-card relative flex flex-col justify-between p-4 rounded-2xl border cursor-pointer transition-all duration-300 group select-none hover:scale-[1.01]
                                        {{ $isAllowed ? 'bg-emerald-950/30 border-emerald-500/50 shadow-lg shadow-emerald-500/5' : 'bg-slate-800/40 border-white/10 hover:border-slate-500/50' }}"
                                        data-name="{{ strtolower($partner->name) }}"
                                        data-email="{{ strtolower($partner->email) }}"
                                        data-partner-plan="{{ strtolower($partner->partner_plan ?? 'free') }}"
                                        data-company-type="{{ strtolower($partner->partnerProfile->company_type ?? 'freelancer') }}">
                                        
                                        <div class="flex items-start w-full">
                                            {{-- Safe Spacing and Non-Shrinking Checkbox wrapper --}}
                                            <div class="flex items-center h-5 mt-0.5 mr-4 flex-shrink-0">
                                                <input id="partner_{{ $partner->id }}" 
                                                       name="allowed_partners[]" 
                                                       type="checkbox" 
                                                       value="{{ $partner->id }}"
                                                       class="partner-checkbox w-5 h-5 text-emerald-600 bg-slate-950 border-white/10 rounded focus:ring-emerald-500 focus:ring-2 focus:ring-offset-slate-900 transition"
                                                       {{ $isAllowed ? 'checked' : '' }}>
                                            </div>
                                            
                                            <div class="text-sm flex-1">
                                                <span class="block font-bold text-white group-hover:text-emerald-300 transition-colors">
                                                    {{ $partner->name }}
                                                </span>
                                                <span class="block text-slate-400 text-xs mt-0.5 break-all">{{ $partner->email }}</span>
                                            </div>
                                        </div>

                                        {{-- CATEGORIES & BADGES SECTION --}}
                                        <div class="mt-4 pt-3 border-t border-white/5 flex flex-wrap gap-1.5 items-center justify-between w-full">
                                            {{-- Tier & Type Badges --}}
                                            <div class="flex items-center gap-1.5">
                                                @php
                                                    $plan = $partner->partner_plan ?? 'Free';
                                                    $planClass = match(strtolower($plan)) {
                                                        'enterprise' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                                        'pro' => 'bg-blue-500/15 text-blue-300 border-blue-500/30',
                                                        'basic' => 'bg-slate-500/15 text-slate-400 border-slate-500/30',
                                                        default => 'bg-slate-600/15 text-slate-400 border-white/10' // Free
                                                    };
                                                    
                                                    $type = $partner->partnerProfile->company_type ?? 'Freelancer';
                                                    $isFreelancer = strtolower($type) === 'freelancer';
                                                @endphp
                                                <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded border {{ $planClass }}" title="Partner Plan Level">
                                                    {{ $plan }}
                                                </span>
                                                @if($isFreelancer)
                                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded border bg-cyan-500/15 text-cyan-300 border-cyan-500/30" title="Sourcing Company Type">
                                                        Freelancer
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Status Indicator Badge --}}
                                            <div class="status-badge-container">
                                                @if($isAllowed)
                                                    <span class="status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] uppercase font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 tracking-wider">
                                                        <i class="fa-solid fa-circle-check"></i> Included
                                                    </span>
                                                @else
                                                    <span class="status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] uppercase font-extrabold bg-slate-800 text-slate-400 border border-white/5 tracking-wider">
                                                        <i class="fa-solid fa-eye-slash"></i> Hidden
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            
                            {{-- Search Empty State --}}
                            <div id="search-empty-state" class="hidden bg-slate-800/30 border border-white/5 p-8 rounded-2xl text-center my-4">
                                <i class="fa-solid fa-user-slash text-4xl text-slate-500 mb-3 block"></i>
                                <p class="text-slate-400 font-medium">No partners found matching "<span id="search-query-display" class="font-bold text-slate-300"></span>"</p>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-between items-center pt-6 border-t border-white/10">
                        <div class="text-xs text-slate-400">
                            <span id="selected-count" class="font-bold text-emerald-400">0</span> of <span class="font-bold text-slate-300">{{ count($allPartners) }}</span> partners selected.
                        </div>
                        <div class="flex items-center">
                            <a href="{{ route('admin.jobs.pending') }}" class="mr-4 px-6 py-3 rounded-xl text-sm font-bold text-white hover:bg-white/10 transition border border-transparent hover:border-white/10">
                                Cancel
                            </a>
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Save Visibility
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Interactive Dynamic Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('partners-grid-container');
            const cards = document.querySelectorAll('.partner-card');
            const searchInput = document.getElementById('partner-search-input');
            const selectAllBtn = document.getElementById('select-all-partners');
            const clearAllBtn = document.getElementById('clear-all-partners');
            const selectedCountEl = document.getElementById('selected-count');
            const emptyState = document.getElementById('search-empty-state');
            const queryDisplay = document.getElementById('search-query-display');

            // Dynamically count and calculate the numbers of partners for each filter/selection button
            function calculateCategoryCounts() {
                const planCounts = { free: 0, basic: 0, pro: 0, enterprise: 0 };
                let freelancerCount = 0;
                
                cards.forEach(card => {
                    const plan = card.getAttribute('data-partner-plan');
                    const type = card.getAttribute('data-company-type');
                    
                    if (plan in planCounts) {
                        planCounts[plan]++;
                    }
                    if (type === 'freelancer') {
                        freelancerCount++;
                    }
                });
                
                // Inject counts into Filter Grid View Pills
                document.querySelector('[data-filter-plan="free"]').innerHTML = `Free <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.free})</span>`;
                document.querySelector('[data-filter-plan="basic"]').innerHTML = `Basic <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.basic})</span>`;
                document.querySelector('[data-filter-plan="pro"]').innerHTML = `Pro <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.pro})</span>`;
                document.querySelector('[data-filter-plan="enterprise"]').innerHTML = `Enterprise <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.enterprise})</span>`;
                document.querySelector('[data-filter-type="freelancer"]').innerHTML = `Freelancer <span class="ml-1 text-[10px] opacity-75 font-normal">(${freelancerCount})</span>`;
                
                // Inject counts into Category-wise Selection Pills
                document.querySelector('[data-select-plan="free"]').innerHTML = `Free <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.free})</span>`;
                document.querySelector('[data-select-plan="basic"]').innerHTML = `Basic <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.basic})</span>`;
                document.querySelector('[data-select-plan="pro"]').innerHTML = `Pro <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.pro})</span>`;
                document.querySelector('[data-select-plan="enterprise"]').innerHTML = `Enterprise <span class="ml-1 text-[10px] opacity-75 font-normal">(${planCounts.enterprise})</span>`;
                document.querySelector('[data-select-type="freelancer"]').innerHTML = `Freelancer <span class="ml-1 text-[10px] opacity-75 font-normal">(${freelancerCount})</span>`;
            }

            // Function to sort checked cards to the beginning (top) of the grid
            function reorderPartners() {
                if (!container) return;
                
                const cardsArray = Array.from(container.querySelectorAll('.partner-card'));
                
                cardsArray.sort((a, b) => {
                    const aChecked = a.querySelector('.partner-checkbox').checked ? 1 : 0;
                    const bChecked = b.querySelector('.partner-checkbox').checked ? 1 : 0;
                    
                    if (aChecked !== bChecked) {
                        return bChecked - aChecked; // Checked cards come first
                    }
                    
                    // Maintain alphabetical sorting for cards with same check status
                    const aName = a.getAttribute('data-name') || '';
                    const bName = b.getAttribute('data-name') || '';
                    return aName.localeCompare(bName);
                });
                
                // Re-append sorted cards back into the container
                cardsArray.forEach(card => {
                    container.appendChild(card);
                });
            }

            // Function to dynamically sync Category-wise Selection button active highlights
            function syncSelectionButtonHighlights() {
                const plans = ['free', 'basic', 'pro', 'enterprise'];
                
                plans.forEach(plan => {
                    const btn = document.querySelector(`[data-select-plan="${plan}"]`);
                    if (!btn) return;
                    
                    const matchingCards = Array.from(cards).filter(card => card.getAttribute('data-partner-plan') === plan);
                    if (matchingCards.length === 0) return;
                    
                    const allChecked = matchingCards.every(card => card.querySelector('.partner-checkbox').checked);
                    
                    if (allChecked) {
                        btn.classList.remove('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border-white/5');
                        btn.classList.add('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border-emerald-500/30', 'shadow-md');
                    } else {
                        btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border-emerald-500/30', 'shadow-md');
                        btn.classList.add('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border-white/5', 'hover:border-slate-500/50');
                    }
                });

                // Sync Freelancer selection button
                const freelancerBtn = document.querySelector('[data-select-type="freelancer"]');
                if (freelancerBtn) {
                    const matchingCards = Array.from(cards).filter(card => card.getAttribute('data-company-type') === 'freelancer');
                    if (matchingCards.length > 0) {
                        const allChecked = matchingCards.every(card => card.querySelector('.partner-checkbox').checked);
                        if (allChecked) {
                            freelancerBtn.classList.remove('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border-white/5');
                            freelancerBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border-emerald-500/30', 'shadow-md');
                        } else {
                            freelancerBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border-emerald-500/30', 'shadow-md');
                            freelancerBtn.classList.add('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border-white/5', 'hover:border-slate-500/50');
                        }
                    }
                }
            }

            // Update the selected partners count dynamically
            function updateSelectedCount() {
                if (!selectedCountEl) return;
                const checkedCount = document.querySelectorAll('.partner-checkbox:checked').length;
                selectedCountEl.textContent = checkedCount;
            }

            // Function to update card styles dynamically on change
            function updateCardStyle(card) {
                const checkbox = card.querySelector('.partner-checkbox');
                const badgeContainer = card.querySelector('.status-badge-container');
                
                if (checkbox.checked) {
                    card.classList.remove('bg-slate-800/40', 'border-white/10', 'hover:border-slate-500/50');
                    card.classList.add('bg-emerald-950/30', 'border-emerald-500/50', 'shadow-lg', 'shadow-emerald-500/5');
                    
                    badgeContainer.innerHTML = `
                        <span class="status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] uppercase font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 tracking-wider">
                            <i class="fa-solid fa-circle-check"></i> Included
                        </span>
                    `;
                } else {
                    card.classList.add('bg-slate-800/40', 'border-white/10', 'hover:border-slate-500/50');
                    card.classList.remove('bg-emerald-950/30', 'border-emerald-500/50', 'shadow-lg', 'shadow-emerald-500/5');
                    
                    badgeContainer.innerHTML = `
                        <span class="status-badge inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] uppercase font-extrabold bg-slate-800 text-slate-400 border border-white/5 tracking-wider">
                            <i class="fa-solid fa-eye-slash"></i> Hidden
                        </span>
                    `;
                }
                updateSelectedCount();
                syncSelectionButtonHighlights();
                reorderPartners(); // INSTANT SORT TO THE TOP
            }

            // Attach listeners to checkboxes
            cards.forEach(card => {
                const checkbox = card.querySelector('.partner-checkbox');
                
                checkbox.addEventListener('change', function () {
                    updateCardStyle(card);
                });
            });

            // Initialize numbers, buttons, sorting on page load
            calculateCategoryCounts();
            updateSelectedCount();
            syncSelectionButtonHighlights();
            reorderPartners(); // Load all pre-selected ones at the top immediately!

            // Filter state variables
            const filterBtns = document.querySelectorAll('.filter-btn');
            let currentFilterType = 'all'; // all, plan, type
            let currentFilterValue = 'all';

            // Combined filtering function (handles both text search & pills)
            function applyFilters() {
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                let visibleCount = 0;

                cards.forEach(card => {
                    const name = card.getAttribute('data-name');
                    const email = card.getAttribute('data-email');
                    const plan = card.getAttribute('data-partner-plan');
                    const type = card.getAttribute('data-company-type');

                    // Match text search
                    const matchesSearch = name.includes(query) || email.includes(query);

                    // Match active category pill
                    let matchesFilter = false;
                    if (currentFilterType === 'all') {
                        matchesFilter = true;
                    } else if (currentFilterType === 'plan') {
                        matchesFilter = (plan === currentFilterValue);
                    } else if (currentFilterType === 'type') {
                        matchesFilter = (type === currentFilterValue);
                    }

                    if (matchesSearch && matchesFilter) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                // Manage empty state
                if (visibleCount === 0 && (query !== '' || currentFilterValue !== 'all')) {
                    emptyState.classList.remove('hidden');
                    queryDisplay.textContent = query !== '' ? query : `Filtered: ${currentFilterValue}`;
                } else {
                    emptyState.classList.add('hidden');
                }
            }

            // Live text search event listener
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }

            // Bind click events on filter pills
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => {
                        b.classList.remove('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border-emerald-500/30', 'shadow-md');
                        b.classList.add('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border', 'border-white/5', 'hover:border-slate-500/50');
                    });

                    this.classList.remove('bg-slate-800/85', 'hover:bg-slate-700', 'text-slate-200', 'border', 'border-white/5', 'hover:border-slate-500/50');
                    this.classList.add('bg-emerald-600', 'hover:bg-emerald-500', 'text-white', 'border', 'border-emerald-500/30', 'shadow-md');

                    if (this.hasAttribute('data-filter')) {
                        currentFilterType = 'all';
                        currentFilterValue = 'all';
                    } else if (this.hasAttribute('data-filter-plan')) {
                        currentFilterType = 'plan';
                        currentFilterValue = this.getAttribute('data-filter-plan');
                    } else if (this.hasAttribute('data-filter-type')) {
                        currentFilterType = 'type';
                        currentFilterValue = this.getAttribute('data-filter-type');
                    }

                    applyFilters();
                });
            });

            // Bind click events on category selection buttons (Quick Select)
            const selectBtns = document.querySelectorAll('.category-select-btn');
            selectBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    let selectorType = '';
                    let selectorValue = '';

                    if (this.hasAttribute('data-select-plan')) {
                        selectorType = 'plan';
                        selectorValue = this.getAttribute('data-select-plan');
                    } else if (this.hasAttribute('data-select-type')) {
                        selectorType = 'type';
                        selectorValue = this.getAttribute('data-select-type');
                    }

                    // Find all matching cards (only currently filtered/visible ones)
                    const targetCards = Array.from(cards).filter(card => {
                        if (card.classList.contains('hidden')) return false;
                        const plan = card.getAttribute('data-partner-plan');
                        const type = card.getAttribute('data-company-type');
                        
                        if (selectorType === 'plan') {
                            return plan === selectorValue;
                        } else if (selectorType === 'type') {
                            return type === selectorValue;
                        }
                        return false;
                    });

                    if (targetCards.length === 0) return;

                    // Toggle logic: If all visible matching are checked, uncheck all. Otherwise, check all.
                    const allChecked = targetCards.every(card => card.querySelector('.partner-checkbox').checked);
                    
                    targetCards.forEach(card => {
                        const checkbox = card.querySelector('.partner-checkbox');
                        checkbox.checked = !allChecked;
                        updateCardStyle(card);
                    });
                });
            });

            // Select All Sourcing Partners
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    // Only select currently filtered/visible cards to make it intuitive
                    const visibleCards = document.querySelectorAll('.partner-card:not(.hidden)');
                    visibleCards.forEach(card => {
                        const checkbox = card.querySelector('.partner-checkbox');
                        if (!checkbox.checked) {
                            checkbox.checked = true;
                            updateCardStyle(card);
                        }
                    });
                });
            }

            // Clear All Sourcing Partners
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    // Only clear currently filtered/visible cards to keep it contextual
                    const visibleCards = document.querySelectorAll('.partner-card:not(.hidden)');
                    visibleCards.forEach(card => {
                        const checkbox = card.querySelector('.partner-checkbox');
                        if (checkbox.checked) {
                            checkbox.checked = false;
                            updateCardStyle(card);
                        }
                    });
                });
            }
        });
    </script>

    {{-- Custom Scrollbar Style for this page --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</x-app-layout>
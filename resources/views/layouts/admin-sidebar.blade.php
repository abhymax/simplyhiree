{{-- Vertical sidebar for Superadmin / Manager. Fixed 256px on lg+,
     slide-in panel on small screens triggered by the topbar hamburger. --}}
<div x-data="{ open: false }">
    {{-- Mobile topbar --}}
    <div class="admin-mobile-only fixed top-0 left-0 right-0 z-50 bg-slate-900 border-b border-white/10 h-14 flex items-center justify-between px-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-sky-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">SH</div>
            <span class="font-bold text-white text-sm">SimplyHiree</span>
        </a>
        <button @click="open = !open" type="button" class="text-white p-2"><i class="fa-solid fa-bars text-xl"></i></button>
    </div>

    {{-- Backdrop on mobile --}}
    <div x-show="open" @click="open = false" class="admin-mobile-only fixed inset-0 bg-black/60 z-40" x-transition></div>

    {{-- Sidebar itself --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="admin-sidebar-aside fixed top-0 left-0 z-50 w-64 h-screen bg-slate-900 border-r border-white/10 text-slate-200 shadow-2xl transition-transform duration-200 flex flex-col">

        <div class="h-16 flex items-center justify-between px-4 border-b border-white/10 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
                <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-sky-500 rounded-lg flex items-center justify-center text-white font-bold text-base shadow-lg flex-shrink-0">SH</div>
                <span class="font-bold text-white text-base tracking-tight truncate">SimplyHiree</span>
            </a>
            <button type="button" @click="open = false" class="admin-mobile-only text-slate-400 hover:text-white p-1"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <nav class="flex-1 overflow-y-auto py-3 space-y-0.5">
            @php
                $links = [
                    ['route'=>'admin.dashboard',              'label'=>'Dashboard',           'icon'=>'fa-gauge-high',          'active'=>['admin.dashboard','dashboard']],
                    ['route'=>'admin.applications.index',     'label'=>'All Applications',    'icon'=>'fa-file-lines',          'active'=>['admin.applications.*'], 'can'=>'view_application_data'],
                    ['route'=>'admin.jobs.pending',           'label'=>'Pending Jobs',        'icon'=>'fa-briefcase-clock',     'active'=>['admin.jobs.pending'], 'can'=>'view_pending_jobs'],
                    ['route'=>'admin.jobs.archived',          'label'=>'Archived Jobs',       'icon'=>'fa-box-archive',         'active'=>['admin.jobs.archived*'], 'can'=>'view_pending_jobs'],
                    ['route'=>'admin.billing.index',          'label'=>'Billing Report',      'icon'=>'fa-file-invoice-dollar', 'active'=>['admin.billing.*'], 'can'=>'view_billing_data'],
                    ['route'=>'admin.replacements.index',     'label'=>'Replacements',        'icon'=>'fa-rotate',              'active'=>['admin.replacements.*'], 'can'=>'view_billing_data'],
                    ['route'=>'admin.credit-notes.index',     'label'=>'Credit Notes',        'icon'=>'fa-receipt',             'active'=>['admin.credit-notes.*'], 'can'=>'view_billing_data'],
                    ['route'=>'admin.plan-requests.index',    'label'=>'Plan Requests',       'icon'=>'fa-rocket',              'active'=>['admin.plan-requests.*'], 'can'=>'view_billing_data'],
                    ['route'=>'admin.reports.jobs',           'label'=>'Master Job Report',   'icon'=>'fa-chart-bar',           'active'=>['admin.reports.jobs*'], 'can'=>'view_billing_data'],
                    ['route'=>'admin.clients.index',          'label'=>'Clients',             'icon'=>'fa-building',            'active'=>['admin.clients.*']],
                    ['route'=>'admin.partners.index',         'label'=>'Partners',            'icon'=>'fa-handshake',           'active'=>['admin.partners.*']],
                    ['route'=>'admin.candidates.index',       'label'=>'Candidates',          'icon'=>'fa-users',               'active'=>['admin.candidates.*']],
                    ['route'=>'admin.managers.index',         'label'=>'Managers',            'icon'=>'fa-user-shield',         'active'=>['admin.managers.*']],
                    ['route'=>'admin.landing-pages.index',    'label'=>'Landing Pages',       'icon'=>'fa-globe',               'active'=>['admin.landing-pages.*']],
                ];
            @endphp

            @foreach($links as $l)
                @php
                    if (!\Illuminate\Support\Facades\Route::has($l['route'])) continue;
                    if (!empty($l['can']) && !auth()->user()->can($l['can'])) continue;
                    $isActive = false;
                    foreach ($l['active'] as $pattern) {
                        if (request()->routeIs($pattern)) { $isActive = true; break; }
                    }
                @endphp
                <a href="{{ route($l['route']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-lg transition {{ $isActive ? 'bg-indigo-600 text-white shadow' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                    <i class="fa-solid {{ $l['icon'] }} text-base w-5 text-center flex-shrink-0"></i>
                    <span class="text-sm font-semibold truncate">{{ $l['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3 flex-shrink-0">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-white text-sm font-bold truncate">{{ auth()->user()->name }}</div>
                    <div class="text-slate-400 text-[10px] truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="block w-full text-center text-xs text-slate-300 hover:text-white py-1.5 rounded hover:bg-white/5">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-center text-xs text-rose-300 hover:text-rose-200 py-1.5 rounded hover:bg-rose-500/10 mt-1">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Log Out
                </button>
            </form>
        </div>
    </aside>
</div>

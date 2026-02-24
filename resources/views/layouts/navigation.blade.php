<nav x-data="{ open: false }" class="glass-nav sticky top-0 z-50 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-sky-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg transform group-hover:rotate-12 transition-transform">
                            SH
                        </div>
                        <span class="font-bold text-xl text-slate-800 tracking-tight hidden md:block">SimplyHiree</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Generic Dashboard Link --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-600 hover:text-indigo-600 border-transparent hover:border-indigo-600 transition-all">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- *** ROLE-SPECIFIC LINKS *** --}}
                    {{-- ADMIN & SUB-ADMIN LINKS --}}
                    @if(auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager')))
                        @can('view_application_data')
                            <x-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.index')" class="text-slate-600 hover:text-indigo-600">
                                All Applications
                            </x-nav-link>
                        @endcan

                        @can('view_pending_jobs')
                            <x-nav-link :href="route('admin.jobs.pending')" :active="request()->routeIs('admin.jobs.pending')" class="text-slate-600 hover:text-indigo-600">
                                Pending Jobs
                            </x-nav-link>
                        @endcan

                        @can('view_billing_data')
                            <x-nav-link :href="route('admin.billing.index')" :active="request()->routeIs('admin.billing.index')" class="text-slate-600 hover:text-indigo-600">
                                Billing Report
                            </x-nav-link>
                            <x-nav-link :href="route('admin.reports.jobs')" :active="request()->routeIs('admin.reports.jobs')" class="text-slate-600 hover:text-indigo-600">
                                Master Job Report
                            </x-nav-link>
                        @endcan

                        @can('manage_sub_admins')
                            <x-nav-link :href="route('admin.sub_admins.index')" :active="request()->routeIs('admin.sub_admins.index')" class="text-slate-600 hover:text-indigo-600">
                                Managers
                            </x-nav-link>
                        @endcan

                        <x-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.index')" class="text-slate-600 hover:text-indigo-600">
                            Activity Logs
                        </x-nav-link>
                    @endif

                    {{-- CLIENT LINKS --}}
                    @role('client')
                        <x-nav-link :href="route('client.jobs.create')" :active="request()->routeIs('client.jobs.create')" class="text-slate-600 hover:text-indigo-600">
                            Post New Job
                        </x-nav-link>
                        <x-nav-link :href="route('client.billing')" :active="request()->routeIs('client.billing')" class="text-slate-600 hover:text-indigo-600">
                            Billing
                        </x-nav-link>
                        <x-nav-link :href="route('client.profile.company')" :active="request()->routeIs('client.profile.company')" class="text-slate-600 hover:text-indigo-600">
                            Company Profile
                        </x-nav-link>
                    @endrole

                    {{-- PARTNER LINKS --}}
                    @role('partner')
                        <x-nav-link :href="route('partner.jobs')" :active="request()->routeIs('partner.jobs')" class="text-slate-600 hover:text-indigo-600">
                            Browse Jobs
                        </x-nav-link>
                        <x-nav-link :href="route('partner.applications')" :active="request()->routeIs('partner.applications')" class="text-slate-600 hover:text-indigo-600">
                            My Applications
                        </x-nav-link>
                        <x-nav-link :href="route('partner.candidates.index')" :active="request()->routeIs('partner.candidates.index')" class="text-slate-600 hover:text-indigo-600">
                            My Candidates
                        </x-nav-link>
                        <x-nav-link :href="route('partner.profile.business')" :active="request()->routeIs('partner.profile.business')" class="text-slate-600 hover:text-indigo-600">
                            My Account
                        </x-nav-link>
                    @endrole

                    {{-- CANDIDATE LINKS --}}
                    @role('candidate')
                        <x-nav-link :href="route('candidate.applications')" :active="request()->routeIs('candidate.applications')" class="text-slate-600 hover:text-indigo-600">
                            My Applications
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <livewire:notifications-bell />

                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-slate-600 bg-slate-50 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                @php
                                    $profileRoute = route('profile.edit');
                                    if (auth()->user()->hasRole('candidate')) {
                                        $profileRoute = route('candidate.profile.edit');
                                    } elseif (auth()->user()->hasRole('partner')) {
                                        $profileRoute = route('partner.profile.business');
                                    } elseif (auth()->user()->hasRole('client')) {
                                        $profileRoute = route('client.profile.company');
                                    }
                                @endphp

                                <div class="block px-4 py-2 text-xs text-slate-400">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link :href="$profileRoute" class="hover:bg-indigo-50 hover:text-indigo-600">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @role('client')
                                    <x-dropdown-link :href="route('client.profile.company')" class="hover:bg-indigo-50 hover:text-indigo-600">
                                        {{ __('Company Profile') }}
                                    </x-dropdown-link>
                                @endrole

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="text-red-600 hover:bg-red-50">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">Login</a>
                        <a href="{{ route('register.candidate') }}" class="inline-flex items-center rounded-full bg-indigo-600 text-white px-4 py-2 text-sm font-semibold hover:bg-indigo-500 transition-colors">Register</a>
                    </div>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/95 backdrop-blur-xl border-t border-slate-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-600">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- *** ROLE-SPECIFIC LINKS (Responsive) *** --}}
            @if(auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager')))
                @can('view_application_data')
                    <x-responsive-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.index')">
                        All Applications
                    </x-responsive-nav-link>
                @endcan

                @can('view_pending_jobs')
                    <x-responsive-nav-link :href="route('admin.jobs.pending')" :active="request()->routeIs('admin.jobs.pending')">
                        Pending Jobs
                    </x-responsive-nav-link>
                @endcan

                @can('view_billing_data')
                    <x-responsive-nav-link :href="route('admin.billing.index')" :active="request()->routeIs('admin.billing.index')">
                        Billing Report
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reports.jobs')" :active="request()->routeIs('admin.reports.jobs')">
                        Master Job Report
                    </x-responsive-nav-link>
                @endcan

                @can('manage_sub_admins')
                    <x-responsive-nav-link :href="route('admin.sub_admins.index')" :active="request()->routeIs('admin.sub_admins.index')">
                        Managers
                    </x-responsive-nav-link>
                @endcan

                <x-responsive-nav-link :href="route('admin.activity-logs.index')" :active="request()->routeIs('admin.activity-logs.index')">
                    Activity Logs
                </x-responsive-nav-link>
            @endif

            @role('client')
                <x-responsive-nav-link :href="route('client.jobs.create')" :active="request()->routeIs('client.jobs.create')">
                    Post New Job
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.billing')" :active="request()->routeIs('client.billing')">
                    Billing
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.profile.company')" :active="request()->routeIs('client.profile.company')">
                    Company Profile
                </x-responsive-nav-link>
            @endrole

            @role('partner')
                <x-responsive-nav-link :href="route('partner.jobs')" :active="request()->routeIs('partner.jobs')">
                    Browse Jobs
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.applications')" :active="request()->routeIs('partner.applications')">
                    My Applications
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.candidates.index')" :active="request()->routeIs('partner.candidates.index')">
                    My Candidates
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('partner.profile.business')" :active="request()->routeIs('partner.profile.business')">
                    My Account
                </x-responsive-nav-link>
            @endrole

            @role('candidate')
                <x-responsive-nav-link :href="route('candidate.applications')" :active="request()->routeIs('candidate.applications')">
                    My Applications
                </x-responsive-nav-link>
            @endrole
        </div>

        <div class="pt-4 pb-4 border-t border-slate-200 bg-slate-50">
            @auth
                <div class="px-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    @php
                        $responsiveProfileRoute = route('profile.edit');
                        if (auth()->user()->hasRole('candidate')) {
                            $responsiveProfileRoute = route('candidate.profile.edit');
                        } elseif (auth()->user()->hasRole('partner')) {
                            $responsiveProfileRoute = route('partner.profile.business');
                        } elseif (auth()->user()->hasRole('client')) {
                            $responsiveProfileRoute = route('client.profile.company');
                        }
                    @endphp

                    <x-responsive-nav-link :href="$responsiveProfileRoute" class="hover:text-indigo-600 hover:bg-indigo-50">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-red-600 hover:bg-red-50 hover:text-red-700">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 space-y-2">
                    <x-responsive-nav-link :href="route('login')" class="hover:text-indigo-600 hover:bg-indigo-50">
                        Login
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register.candidate')" class="hover:text-indigo-600 hover:bg-indigo-50">
                        Register
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>

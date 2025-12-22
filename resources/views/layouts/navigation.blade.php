<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                    {{-- Generic Dashboard Link --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- *** ROLE-SPECIFIC LINKS *** --}}
                    
                    {{-- ADMIN & SUB-ADMIN LINKS --}}
                    {{-- Check if user is either Superadmin OR Manager --}}
                    @if(auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'))
                        
                        {{-- Applications --}}
                        @can('view_application_data')
                        <x-nav-link :href="route('admin.applications.index')" :active="request()->routeIs('admin.applications.index')">
                            All Applications
                        </x-nav-link>
                        @endcan

                        {{-- Jobs --}}
                        @can('view_pending_jobs')
                        <x-nav-link :href="route('admin.jobs.pending')" :active="request()->routeIs('admin.jobs.pending')">
                            Pending Jobs
                        </x-nav-link>
                        @endcan

                        {{-- Billing & Reports --}}
                        @can('view_billing_data')
                        <x-nav-link :href="route('admin.billing.index')" :active="request()->routeIs('admin.billing.index')">
                            Billing Report
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reports.jobs')" :active="request()->routeIs('admin.reports.jobs')">
                            Master Job Report
                        </x-nav-link>
                        @endcan

                        {{-- Sub-Admins (Superadmin Only usually) --}}
                        @can('manage_sub_admins')
                        <x-nav-link :href="route('admin.sub_admins.index')" :active="request()->routeIs('admin.sub_admins.index')">
                            Managers
                        </x-nav-link>
                        @endcan

                    @endif
                    
                    {{-- CLIENT LINKS --}}
                    @role('client')
                        <x-nav-link :href="route('client.jobs.create')" :active="request()->routeIs('client.jobs.create')">
                            Post New Job
                        </x-nav-link>
                        <x-nav-link :href="route('client.billing')" :active="request()->routeIs('client.billing')">
                            Billing
                        </x-nav-link>
                        <x-nav-link :href="route('client.profile.company')" :active="request()->routeIs('client.profile.company')">
                            Company Profile
                        </x-nav-link>
                    @endrole

                    {{-- PARTNER LINKS --}}
                    @role('partner')
                         <x-nav-link :href="route('partner.jobs')" :active="request()->routeIs('partner.jobs')">
                            Browse Jobs
                        </x-nav-link>
                         <x-nav-link :href="route('partner.applications')" :active="request()->routeIs('partner.applications')">
                            My Applications
                        </x-nav-link>
                        <x-nav-link :href="route('partner.candidates.index')" :active="request()->routeIs('partner.candidates.index')">
                            My Candidates
                        </x-nav-link>
                        <x-nav-link :href="route('partner.profile.business')" :active="request()->routeIs('partner.profile.business')">
                            My Account
                        </x-nav-link>
                    @endrole

                    {{-- CANDIDATE LINKS --}}
                    @role('candidate')
                         <x-nav-link :href="route('candidate.applications')" :active="request()->routeIs('candidate.applications')">
                            My Applications
                        </x-nav-link>
                    @endrole

                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                <livewire:notifications-bell />

                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @role('client')
                                <x-dropdown-link :href="route('client.profile.company')">
                                    {{ __('Company Profile') }}
                                </x-dropdown-link>
                            @endrole

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- *** ROLE-SPECIFIC LINKS (Responsive) *** --}}
            
            {{-- ADMIN & SUB-ADMIN LINKS (Mobile) --}}
            @if(auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'))
                
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

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
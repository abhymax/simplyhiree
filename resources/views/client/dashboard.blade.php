@extends('layouts.app')

@section('content')
<style>
    /* Absolute exact color and layout replication from JPEG */
    nav.glass-nav { display: none !important; }
    footer { display: none !important; }
    body { background-color: #0a1b46 !important; font-family: 'Outfit', sans-serif; overflow-x: hidden; margin: 0; padding: 0; }
    main { padding: 0 !important; }

    /* Scrollbar Styling */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #0a1b46; }
    ::-webkit-scrollbar-thumb { background: #111827; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #1f2937; }

    /* Left Sidebar - Exact Match */
    .custom-sidebar {
        width: 250px;
        background-color: #10275a;
        border-right: 1px solid rgba(59, 130, 246, 0.08);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        z-index: 100;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .custom-sidebar-logo {
        height: 70px;
        display: flex;
        align-items: center;
        padding: 0 24px;
        gap: 10px;
        border-bottom: 1px solid rgba(59, 130, 246, 0.08);
        flex-shrink: 0;
    }

    .custom-sidebar-nav {
        flex: 1;
        padding: 20px 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        overflow-y: auto;
    }

    .custom-sidebar-link {
        display: flex;
        align-items: center;
        justify-content: justify;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 500;
        color: #f8fafc;
        text-decoration: none;
        transition: color 0.22s ease, background-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid transparent;
    }

    .custom-sidebar-link:hover {
        background-color: rgba(96, 165, 250, 0.18);
        color: #ffffff;
        border-color: rgba(125, 211, 252, 0.26);
        transform: translateX(3px);
        box-shadow: 0 8px 18px rgba(2, 6, 23, 0.24);
    }

    .custom-sidebar-link.active {
        background: linear-gradient(100deg, #2563eb, #4f46e5);
        color: #ffffff;
        font-weight: 700;
        border-color: rgba(147, 197, 253, 0.42);
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.26);
    }
    .custom-sidebar-link::after { content: ''; position: absolute; top: 0; bottom: 0; left: -45%; width: 30%; transform: skewX(-18deg); background: linear-gradient(90deg, transparent, rgba(255,255,255,.28), transparent); opacity: 0; }
    .custom-sidebar-link:hover::after, .custom-sidebar-link.active::after { animation: client-nav-sweep 2.8s ease-in-out infinite; }
    @keyframes client-nav-sweep { 0%, 65% { left: -45%; opacity: 0; } 75% { opacity: 1; } 100% { left: 125%; opacity: 0; } }

    .custom-sidebar-footer {
        padding: 16px;
        border-top: 1px solid rgba(59, 130, 246, 0.08);
        background-color: rgba(0, 0, 0, 0.25);
        flex-shrink: 0;
    }

    /* Main Workspace Layout */
    .custom-main-content {
        margin-left: 250px;
        margin-right: 0;
        padding-inline: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background: linear-gradient(135deg, #10224d 0%, #183a7a 54%, #162d66 100%);
        position: relative;
    }

    /* Broad ambient bloom, matching the super-admin workspace treatment. */
    .custom-main-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 384px;
        height: 384px;
        border-radius: 50%;
        background: #3b82f6;
        mix-blend-mode: screen;
        filter: blur(150px);
        opacity: 0.30;
        animation: client-ambient-bloom 3.2s ease-in-out infinite;
        pointer-events: none;
        z-index: 0;
    }
    .custom-main-content > header,
    .custom-main-content > main { position: relative; z-index: 1; }
    @keyframes client-ambient-bloom { 0%, 100% { opacity: .18; transform: scale(.94); } 50% { opacity: .36; transform: scale(1.05); } }

    .custom-header {
        height: 70px;
        background: transparent;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        border-bottom: 1px solid rgba(59, 130, 246, 0.08);
        padding: 0 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 90;
    }

    .custom-main-body {
        background: transparent !important;
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 32px;
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
        position: relative;
    }


    /* Dashboard surfaces: distinct, readable colour families with a shared interaction language. */
    .glass-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(135deg, #0b1a45 0%, #0b2451 58%, #10265a 100%) !important;
        border: 1px solid rgba(96, 165, 250, 0.24) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        box-shadow: 0 14px 30px rgba(2, 6, 23, 0.18);
        transition: transform 260ms ease, border-color 260ms ease, box-shadow 260ms ease;
    }

    .glass-card::before,
    .metric-card-indigo::before,
    .metric-card-purple::before,
    .metric-card-emerald::before,
    .metric-card-amber::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 -42%;
        width: 26%;
        pointer-events: none;
        transform: skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.16), transparent);
        opacity: 0;
        z-index: -1;
    }

    .glass-card:hover,
    .metric-card-indigo:hover,
    .metric-card-purple:hover,
    .metric-card-emerald:hover,
    .metric-card-amber:hover {
        transform: translateY(-5px);
        border-color: rgba(125, 211, 252, 0.5) !important;
        box-shadow: 0 20px 38px rgba(2, 6, 23, 0.32), 0 0 0 1px rgba(96, 165, 250, 0.08);
    }

    .glass-card:hover::before,
    .metric-card-indigo:hover::before,
    .metric-card-purple:hover::before,
    .metric-card-emerald:hover::before,
    .metric-card-amber:hover::before {
        animation: dashboard-card-sweep 850ms ease-out;
    }

    @keyframes dashboard-card-sweep {
        0% { left: -42%; opacity: 0; }
        18% { opacity: 0.92; }
        100% { left: 122%; opacity: 0; }
    }

    /* Card Outlines & Backgrounds (Exact Match) */
    .metric-card-indigo {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(135deg, #102a70 0%, #12357d 58%, #1e40af 100%);
        border: 1px solid #3b82f6;
    }
    .metric-card-purple {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(135deg, #2b155d 0%, #3c1d75 58%, #5b21b6 100%);
        border: 1px solid #8b5cf6;
    }
    .metric-card-emerald {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(135deg, #064e3b 0%, #075d48 58%, #047857 100%);
        border: 1px solid #34d399;
    }
    .metric-card-amber {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: linear-gradient(135deg, #71320c 0%, #8a410c 58%, #b45309 100%);
        border: 1px solid #f59e0b;
    }

    /* Card decor background icons styling */
    .card-decor-icon {
        position: absolute !important;
        right: 20px !important;
        top: 20px !important;
        font-size: 36px !important;
        color: inherit !important;
        opacity: 0.15 !important;
        z-index: 1 !important;
        transition: transform 0.3s ease !important;
        pointer-events: none !important;
    }
    .group:hover .card-decor-icon {
        transform: scale(1.1) !important;
    }

    /* High-contrast AI recruitment badge */
    .custom-ai-badge {
        background-color: rgba(99, 102, 241, 0.2) !important;
        color: #c7d2fe !important;
        border: 1px solid rgba(129, 140, 248, 0.35) !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
    }

    /* Pipeline funnel stage colors from JPEG */
    .funnel-stage { transition: transform .24s ease, filter .24s ease, box-shadow .24s ease; transform-origin: center; }
    .funnel-stage-1 { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); } /* Applied */
    .funnel-stage-2 { background: linear-gradient(135deg, #10b981 0%, #059669 100%); } /* Shortlisted */
    .funnel-stage-3 { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); } /* Maybe */
    .funnel-stage-4 { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); } /* Interviewed */
    .funnel-stage-5 { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); } /* Offered */
    .funnel-stage-6 { background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%); } /* Joined */
    .funnel-stage-1:hover { transform: translateY(-3px) scale(1.01); filter: brightness(1.14); box-shadow: 0 0 20px rgba(59,130,246,.42); }
    .funnel-stage-2:hover { transform: translateX(5px) scale(1.01); filter: brightness(1.13); box-shadow: 0 0 20px rgba(16,185,129,.42); }
    .funnel-stage-3:hover { transform: translateX(-5px) scale(1.01); filter: brightness(1.13); box-shadow: 0 0 20px rgba(245,158,11,.42); }
    .funnel-stage-4:hover { transform: translateY(-2px) scale(1.02); filter: brightness(1.13); box-shadow: 0 0 20px rgba(139,92,246,.42); }
    .funnel-stage-5:hover { transform: translateX(4px) scale(1.02); filter: brightness(1.13); box-shadow: 0 0 20px rgba(236,72,153,.42); }
    .funnel-stage-6:hover { transform: translateY(3px) scale(1.025); filter: brightness(1.14); box-shadow: 0 0 20px rgba(6,182,212,.42); }

    /* Quick Workspace Border styles */
    .glass-card.workspace-card-blue { background: linear-gradient(135deg, #0d255d, #12376d) !important; border-color: #2563eb !important; }
    .glass-card.workspace-card-orange { background: linear-gradient(135deg, #4a260f, #75401a) !important; border-color: #f97316 !important; }
    .glass-card.workspace-card-purple { background: linear-gradient(135deg, #32165f, #512686) !important; border-color: #a855f7 !important; }
    .glass-card.workspace-card-indigo { background: linear-gradient(135deg, #172554, #30327a) !important; border-color: #818cf8 !important; }
    .glass-card.workspace-card-emerald { background: linear-gradient(135deg, #064e3b, #0b6650) !important; border-color: #2dd4bf !important; }

    /* The header weather state stays calm, but visibly live. */
    .weather-float { animation: weather-float 3.2s ease-in-out infinite; }
    .weather-spin { animation: weather-spin 14s linear infinite; }
    .weather-rain-drop { animation: weather-rain 1.05s ease-in-out infinite; }
    .weather-rain-drop:nth-child(2) { animation-delay: .24s; }
    .weather-rain-drop:nth-child(3) { animation-delay: .5s; }
    .weather-flash { animation: weather-flash 2.7s ease-in-out infinite; }
    .weather-status-live { animation: weather-status-glow 3s ease-in-out infinite; }
    @keyframes weather-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
    @keyframes weather-spin { to { transform: rotate(360deg); } }
    @keyframes weather-rain { 0% { opacity: 0; transform: translateY(-2px); } 30% { opacity: 1; } 100% { opacity: 0; transform: translateY(7px); } }
    @keyframes weather-flash { 0%, 78%, 100% { opacity: .25; } 82%, 87% { opacity: 1; } }
    @keyframes weather-status-glow { 0%, 100% { box-shadow: 0 0 0 rgba(96, 165, 250, 0); } 50% { box-shadow: 0 0 12px rgba(96, 165, 250, .24); } }

    @media (prefers-reduced-motion: reduce) {
        .glass-card, .metric-card-indigo, .metric-card-purple, .metric-card-emerald, .metric-card-amber, .custom-sidebar-link { transition: none !important; }
        .glass-card::before, .metric-card-indigo::before, .metric-card-purple::before, .metric-card-emerald::before, .metric-card-amber::before, .custom-sidebar-link::after, .weather-float, .weather-spin, .weather-rain-drop, .weather-flash, .weather-status-live, .custom-main-content::before { animation: none !important; }
    }

    .sidebar-logout-btn {
        background-color: rgba(239, 68, 68, 0.16) !important;
        border: 1px solid rgba(239, 68, 68, 0.35) !important;
        color: #f87171 !important;
        font-weight: 700 !important;
    }
    .sidebar-logout-btn:hover {
        background-color: rgba(239, 68, 68, 0.28) !important;
        border-color: rgba(248, 113, 113, 0.58) !important;
        color: #fecaca !important;
        box-shadow: 0 10px 26px rgba(127, 29, 29, 0.2);
    }

    /* Mobile view toggle */
    @media (max-width: 1024px) {
        .custom-sidebar {
            transform: translateX(-100%);
        }
        .custom-sidebar.open {
            transform: translateX(0);
        }
        .custom-main-content {
            margin-left: 0;
            margin-right: 0;
            padding-inline: 16px;
        }
        .custom-main-content::before { left: 0; }
    }
</style>

<div class="min-h-screen bg-[#0a1b46] text-[#f8fafc] flex" x-data="{ sidebarOpen: false }">

    {{-- 1. LEFT SIDEBAR PANEL --}}
    <aside class="custom-sidebar" :class="sidebarOpen ? 'open' : ''">
        
        {{-- Logo Section --}}
        <div class="custom-sidebar-logo">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center text-white font-extrabold text-sm shadow-md">
                SH
            </div>
            <div class="font-bold text-lg text-white tracking-tight">SimplyHiree</div>
        </div>

        {{-- Navigation Menu (Replicating exact sidebar menu from JPEG) --}}
        <nav class="custom-sidebar-nav">
            @php
                $menu = [
                    ['icon' => 'fa-solid fa-chart-line', 'label' => 'Dashboard', 'route' => route('client.dashboard'), 'active' => request()->routeIs('client.dashboard')],
                    ['icon' => 'fa-solid fa-briefcase', 'label' => 'My Jobs', 'route' => route('client.jobs.index'), 'active' => request()->is('client/jobs*')],
                    ['icon' => 'fa-solid fa-file-lines', 'label' => 'Applications', 'route' => route('client.applications.index'), 'active' => request()->is('client/applications*') && !request()->has('joined_status')],
                    ['icon' => 'fa-solid fa-video', 'label' => 'Interviews', 'route' => route('client.interviews.calendar'), 'active' => request()->is('client/interviews*')],
                    ['icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Replacements', 'route' => route('client.applications.index', ['joined_status' => 'Left']), 'active' => request()->is('client/applications*') && request('joined_status') === 'Left'],
                    ['icon' => 'fa-solid fa-handshake', 'label' => 'Sourcing Partners', 'route' => route('client.vendors.browse'), 'active' => request()->is('client/vendors*') || request()->is('client/vendor-performance*')],
                    ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Invoices & Billing', 'route' => route('client.billing'), 'active' => request()->is('client/billing*')],
                    ['icon' => 'fa-solid fa-share-nodes', 'label' => Auth::user()->hasRole('referral_partner') ? 'Referral Dashboard' : 'Refer & Earn', 'route' => Auth::user()->hasRole('referral_partner') ? route('referral.dashboard') : route('referral.enroll'), 'active' => request()->routeIs('referral.*')],
                    ['icon' => 'fa-solid fa-gear', 'label' => 'Settings', 'route' => route('client.profile.company'), 'active' => request()->is('client/profile*')],
                    ['icon' => 'fa-solid fa-circle-question', 'label' => 'Help & Support', 'route' => route('support'), 'active' => request()->is('support*')],
                ];
            @endphp

            @foreach($menu as $item)
                <a href="{{ $item['route'] }}" class="custom-sidebar-link {{ $item['active'] ? 'active' : '' }} flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <i class="{{ $item['icon'] }} w-4 text-center"></i>
                        <span>{{ $item['label'] }}</span>
                    </div>
                    @if(isset($item['badge']))
                        <span class="bg-purple-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div class="custom-sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 rounded-xl sidebar-logout-btn transition duration-200 font-bold text-sm tracking-wider uppercase">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Sidebar overlay for mobile --}}
    <div class="fixed inset-0 bg-black/60 z-30 lg:hidden" x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:opacity style="display: none;"></div>

    {{-- 2. MAIN LAYOUT AREA --}}
    <div class="custom-main-content">
        
        {{-- Header Bar --}}
        <header class="custom-header">
            {{-- Search & Mobile Toggle --}}
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <button class="lg:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/5 transition shrink-0" @click="sidebarOpen = true">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="relative w-full hidden sm:block">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input type="text" placeholder="Search candidates, jobs, clients..."
                           class="w-full h-10 bg-slate-900/60 border border-white/5 rounded-lg pl-10 pr-4 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition">
                </div>
            </div>

            {{-- Right Controls --}}
            <div class="flex items-center gap-5">
                {{-- Live time and local weather --}}
                <div id="dashboard-weather-widget" class="hidden md:flex items-center gap-4 px-3.5 py-1.5 rounded-xl bg-slate-950/30 border border-white/5 backdrop-blur-md text-xs">
                    <div class="flex flex-col items-end pr-3.5 border-r border-white/10 shrink-0">
                        <div id="dashboard-widget-time" class="font-extrabold text-white tracking-wider text-[12.5px] leading-tight">--:--:-- --</div>
                        <div class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ date('l, M j, Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <div id="dashboard-weather-icon" class="relative w-8 h-8 flex items-center justify-center shrink-0" aria-label="Live weather"></div>
                        <div class="flex flex-col select-none">
                            <div class="flex items-center gap-1.5 leading-none">
                                <span id="dashboard-weather-temp" class="font-extrabold text-white text-[13px]">--&deg;C</span>
                                <span id="dashboard-weather-desc" class="text-[8.5px] text-slate-300 font-bold uppercase tracking-wide">Loading</span>
                            </div>
                            <span id="dashboard-weather-status" class="weather-status-live mt-1 text-[7.5px] font-extrabold bg-blue-500/10 text-blue-300 border border-blue-500/20 px-1.5 py-0.5 rounded uppercase tracking-wider">Local weather</span>
                        </div>
                    </div>
                </div>

                {{-- Live notification control --}}
                <div class="text-slate-200">
                    <livewire:notifications-bell />
                </div>

                {{-- User Avatar --}}
                <a href="{{ route('client.profile.company') }}" class="w-9 h-9 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-extrabold text-xs ring-2 ring-white/10 shrink-0">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </a>
            </div>
        </header>

        {{-- Content Area --}}
        <main class="custom-main-body">

            {{-- Dashboard Intro --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 id="dynamic-welcome-greeting" class="text-2xl font-extrabold text-white tracking-tight">Welcome back, {{ Auth::user()->name }}</h1>
                    <p id="dynamic-welcome-subtitle" class="text-xs text-slate-300 mt-1">Here's what's happening with your business today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#my-jobs" class="px-4 py-2 bg-slate-900/60 hover:bg-slate-900 border border-white/5 text-slate-200 hover:text-white text-xs font-bold rounded-lg transition">
                        <i class="fa-solid fa-list mr-1.5"></i> My Jobs
                    </a>
                    <a href="{{ route('client.jobs.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-plus mr-1.5"></i> Post Job
                    </a>
                </div>
            </div>

            {{-- Row 1: Core Metrics Cards (Matches color combinations of JPEG exactly) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Metric Card 1: Open Requirements (Indigo) --}}
                <a href="{{ route('client.jobs.index') }}" class="metric-card-indigo p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-blue-500/5">
                    <p class="text-white text-xs font-semibold uppercase tracking-wider relative z-10">Open Requirements</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $activeJobs ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-blue-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Active Job Vacancies</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-blue-500"><i class="fa-solid fa-briefcase"></i></div>
                </a>

                {{-- Metric Card 2: Interviews Today (Purple) --}}
                <a href="{{ route('client.interviews.calendar') }}" class="metric-card-purple p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-purple-500/5">
                    <p class="text-white text-xs font-semibold uppercase tracking-wider relative z-10">Interviews Today</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $todayInterviews ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-purple-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Scheduled for Today</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-purple-500"><i class="fa-solid fa-video"></i></div>
                </a>

                {{-- Metric Card 3: Active Applicants / Earnings (Emerald) --}}
                <a href="{{ route('client.applications.index') }}" class="metric-card-emerald p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-emerald-500/5">
                    <p class="text-white text-xs font-semibold uppercase tracking-wider relative z-10">Active Applicants</p>
                    <h3 class="text-3xl font-extrabold text-emerald-400 mt-3 relative z-10">{{ $totalApplicants ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-emerald-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Approved Candidates Only</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-emerald-500"><i class="fa-solid fa-user-check"></i></div>
                </a>

                {{-- Metric Card 4: Invoices Due / Replacement Requests (Amber) --}}
                <a href="{{ route('client.billing') }}" class="metric-card-amber p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-amber-500/5">
                    <p class="text-white text-xs font-semibold uppercase tracking-wider relative z-10">Invoices Due</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $dueInvoicesCount ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-amber-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Outstanding: ₹{{ number_format($totalOutstandingInvoices ?? 145000) }}</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-amber-500"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </a>
            </div>

            {{-- Row 2: Daily Pulse Submission Trend --}}
            <div class="glass-card rounded-2xl p-6 sm:p-8 flex flex-col gap-6 w-full">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-heart-pulse text-blue-400"></i> Daily Pulse
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Your activity summary for today</p>
                    </div>
                    <span class="text-xs font-bold bg-blue-600/10 text-blue-400 px-3 py-1 rounded-full border border-blue-500/10">Active Pipeline</span>
                </div>

                {{-- Activity Badges --}}
                <div class="flex flex-wrap lg:flex-nowrap gap-3 pt-2">
                    @php
                        // Real Daily Pulse metrics computed in ClientController@index ($dailyPulse).
                        $pulseRgb = [
                            'blue'    => '59, 130, 246',
                            'indigo'  => '99, 102, 241',
                            'emerald' => '16, 185, 129',
                            'amber'   => '245, 158, 11',
                            'rose'    => '244, 63, 94',
                            'purple'  => '139, 92, 246',
                            'cyan'    => '6, 182, 212',
                        ];
                        $pulseItems = $dailyPulse ?? [];
                    @endphp

                    @foreach($pulseItems as $badge)
                        @php $rgb = $pulseRgb[$badge['color'] ?? 'blue'] ?? '59, 130, 246'; @endphp
                        <a href="{{ $badge['link'] ?? '#' }}" class="flex-grow basis-[45%] lg:basis-0 flex items-center gap-4 p-3.5 rounded-xl transition duration-300 hover:scale-[1.02] shadow-md"
                             style="background: linear-gradient(135deg, rgba({{ $rgb }}, 0.08) 0%, rgba({{ $rgb }}, 0.02) 100%); border: 1px solid rgba({{ $rgb }}, 0.18);">
                            <div class="w-11 h-11 rounded-lg text-center flex items-center justify-center shrink-0" style="background-color: rgba({{ $rgb }}, 0.15);">
                                <i class="fa-solid {{ $badge['icon'] }} text-base" style="color: rgb({{ $rgb }});"></i>
                            </div>
                            <div class="min-w-0 flex flex-col justify-center gap-1 text-left">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider block" style="color: rgba({{ $rgb }}, 0.85);">{{ $badge['label'] }}</span>
                                <span class="text-base font-black leading-tight text-white block">{{ $badge['value'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Activity Trend (real: last 14 days of scheduled-interview activity, $submissionTrend) --}}
                @php
                    $trend = $submissionTrend ?? [];
                    $tn = count($trend);
                    $counts = array_map(fn ($p) => (int) ($p['count'] ?? 0), $trend);
                    $trendTotal = array_sum($counts);
                    $maxC = max(1, $counts ? max($counts) : 0);
                    $H = 120; $padTop = 15; $padBot = 15; $xStart = 10; $xEnd = 590; $xSpan = $xEnd - $xStart;
                    $pts = [];
                    foreach ($counts as $i => $ct) {
                        $x = $tn > 1 ? round($xStart + $i * $xSpan / ($tn - 1)) : $xStart;
                        $y = round($H - $padBot - ($ct / $maxC) * ($H - $padTop - $padBot));
                        $pts[] = $x . ' ' . $y;
                    }
                    $linePath = $pts ? ('M ' . implode(' L ', $pts)) : '';
                    $areaPath = $pts ? ($linePath . ' L ' . $xEnd . ' ' . $H . ' L ' . $xStart . ' ' . $H . ' Z') : '';
                    $lastCount = $tn ? $counts[$tn - 1] : 0;
                    $lastX = $tn > 1 ? $xEnd : $xStart;
                    $lastY = round($H - $padBot - ($lastCount / $maxC) * ($H - $padTop - $padBot));
                    $labelIdx = [];
                    for ($j = 0; $j < 7; $j++) { $labelIdx[] = $tn > 1 ? (int) round($j * ($tn - 1) / 6) : 0; }
                @endphp
                <div class="mt-2">
                    <div class="flex justify-between items-baseline mb-3">
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Interview Activity (Last 14 Days)</p>
                        <span class="text-xs font-bold text-blue-400">{{ $trendTotal }} {{ \Illuminate\Support\Str::plural('Interview', $trendTotal) }}</span>
                    </div>
                    <div class="h-28 bg-slate-950/50 rounded-xl border border-white/5 relative p-3 flex flex-col justify-between overflow-hidden">
                        <div class="absolute inset-0 flex flex-col justify-between p-3 pointer-events-none opacity-5">
                            <div class="w-full border-t border-white"></div>
                            <div class="w-full border-t border-white"></div>
                            <div class="w-full border-t border-white"></div>
                        </div>

                        <div class="relative z-10 w-full h-14 mt-2">
                            @if($trendTotal > 0)
                                <svg class="w-full h-full" viewBox="0 0 600 120" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="gradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.3"></stop>
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"></stop>
                                        </linearGradient>
                                    </defs>
                                    <path d="{{ $areaPath }}" fill="url(#gradient)"></path>
                                    <path d="{{ $linePath }}" fill="none" stroke="#60a5fa" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <circle cx="{{ $lastX }}" cy="{{ $lastY }}" r="5" fill="#60a5fa" stroke="#ffffff" stroke-width="2"></circle>
                                </svg>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[11px] text-slate-500 font-semibold">No interview activity in the last 14 days</div>
                            @endif
                        </div>

                        <div class="flex justify-between text-[9px] text-slate-500 font-bold uppercase mt-1 px-1">
                            @foreach($labelIdx as $li)
                                <span>{{ $trend[$li]['label'] ?? '' }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 3: Funnel & Pipeline Chart Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Left Widget: Interview Pipeline (Horizontal Funnel matching JPEG shape exactly) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Interview Pipeline</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Hiring funnel overview</p>
                    </div>

                    <div class="flex flex-col items-center gap-0 pt-2 w-full">
                        @php
                            $stages = [
                                [
                                    'label' => 'Applied',
                                    'count' => $funnel[0]['count'],
                                    'clip' => 'polygon(0% 0%, 100% 0%, 91.67% 100%, 8.33% 100%)',
                                    'class' => 'funnel-stage-1',
                                    'link' => $funnel[0]['link'],
                                    'padding' => 'py-3'
                                ],
                                [
                                    'label' => 'Shortlisted',
                                    'count' => $funnel[1]['count'],
                                    'clip' => 'polygon(8.33% 0%, 91.67% 0%, 83.33% 100%, 16.67% 100%)',
                                    'class' => 'funnel-stage-2',
                                    'link' => $funnel[1]['link'],
                                    'padding' => 'py-3'
                                ],
                                [
                                    'label' => 'Maybe',
                                    'count' => $funnel[2]['count'],
                                    'clip' => 'polygon(16.67% 0%, 83.33% 0%, 75% 100%, 25% 100%)',
                                    'class' => 'funnel-stage-3',
                                    'link' => $funnel[2]['link'],
                                    'padding' => 'py-3'
                                ],
                                [
                                    'label' => 'Interviewed',
                                    'count' => $funnel[3]['count'],
                                    'clip' => 'polygon(25% 0%, 75% 0%, 66.67% 100%, 33.33% 100%)',
                                    'class' => 'funnel-stage-4',
                                    'link' => $funnel[3]['link'],
                                    'padding' => 'py-3'
                                ],
                                [
                                    'label' => 'Offered',
                                    'count' => $funnel[4]['count'],
                                    'clip' => 'polygon(33.33% 0%, 66.67% 0%, 58.33% 100%, 41.67% 100%)',
                                    'class' => 'funnel-stage-5',
                                    'link' => $funnel[4]['link'],
                                    'padding' => 'py-3'
                                ],
                                [
                                    'label' => 'Joined',
                                    'count' => $funnel[5]['count'],
                                    'clip' => 'polygon(41.67% 0%, 58.33% 0%, 50% 100%, 50% 100%)',
                                    'class' => 'funnel-stage-6',
                                    'link' => $funnel[5]['link'],
                                    'padding' => 'pt-2 pb-5'
                                ],
                            ];
                        @endphp

                        @foreach($stages as $stg)
                            <a href="{{ $stg['link'] }}" style="width: 100%; clip-path: {{ $stg['clip'] }};" class="block group relative active:scale-[0.99] transition-transform duration-100">
                                <div class="funnel-stage {{ $stg['class'] }} {{ $stg['padding'] }} flex flex-col items-center justify-center border-t border-white/10 group-hover:brightness-110 transition duration-200">
                                    <span class="text-xs font-black text-white uppercase tracking-wider group-hover:text-cyan-200 transition">{{ $stg['label'] }}</span>
                                    <span class="text-[10px] font-black text-white bg-black/20 px-2 py-0.5 rounded-full mt-1 min-w-[24px] text-center">{{ $stg['count'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Right Widget: Top Sourcing Channels --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Sourcing Channel Performance</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Metrics from active recruitment channels</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-slate-300">Sourcing Partner Networks</span>
                            <span class="text-xs font-bold text-emerald-400">88% Match</span>
                        </div>
                        <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: 88%"></div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs font-medium text-slate-300">Direct Applicant Pool</span>
                            <span class="text-xs font-bold text-blue-400">65% Match</span>
                        </div>
                        <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: 65%"></div>
                        </div>
                    </div>

                    <div class="p-3 bg-blue-600/10 border border-blue-500/20 rounded-xl text-center text-[10px] font-extrabold text-blue-400 uppercase tracking-wide">
                        Verified agency sourcing network outperforms direct streams
                    </div>
                </div>
            </div>

            {{-- Row 4: Quick Workspace --}}
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Workspace
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    {{-- Workspace 1: Candidate Pool --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-blue flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-white uppercase font-bold tracking-wider">Candidate Pool</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">1,280</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Total active network</p>
                        </div>
                        <a href="{{ route('client.vendors.browse') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Start Sourcing
                        </a>
                    </div>

                    {{-- Workspace 2: Pending Applications --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-orange flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-white uppercase font-bold tracking-wider">Applications</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $totalApplicants ?? 54 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pending approved candidates</p>
                        </div>
                        <a href="{{ route('client.applications.index') }}" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Review Applications
                        </a>
                    </div>

                    {{-- Workspace 3: Interviews Scheduled --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-purple flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-white uppercase font-bold tracking-wider">Interviews</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $todayInterviews ?? 38 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Scheduled upcoming boards</p>
                        </div>
                        <a href="{{ route('client.interviews.calendar') }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Calendar
                        </a>
                    </div>

                    {{-- Workspace 4: Active Openings --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-indigo flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-white uppercase font-bold tracking-wider">Openings</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $activeJobs ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Active vacancies live</p>
                        </div>
                        <a href="#my-jobs" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Jobs
                        </a>
                    </div>

                    {{-- Workspace 5: Billing & Invoices --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-emerald flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-white uppercase font-bold tracking-wider">Invoices & Billing</p>
                            <h4 class="text-2xl font-extrabold text-emerald-400 mt-1">₹{{ number_format($totalOutstandingInvoices) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Outstanding Invoices</p>
                        </div>
                        <a href="{{ route('client.billing') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Manage Invoices
                        </a>
                    </div>

                </div>
            </div>

            {{-- Row 5: My Job Postings Table Section (Requirements Workspace) --}}
            <div id="my-jobs" class="glass-card rounded-2xl overflow-hidden" style="scroll-margin-top: 100px;">
                <div class="p-5 border-b border-white/5 flex flex-col sm:flex-row justify-between gap-4 sm:items-center bg-slate-900/20">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span> My Job Requirements
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 ml-3">Currently listing <span class="text-white font-bold">{{ $totalJobs ?? 0 }}</span> requirements, including <span class="text-emerald-400 font-bold">{{ $activeJobs ?? 0 }}</span> active vacancies.</p>
                    </div>
                    <a href="{{ route('client.jobs.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 shrink-0 shadow-md">
                        <i class="fa-solid fa-plus"></i> Post New Vacancy
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="jobs-table min-w-full text-left text-sm">
                        <thead class="bg-slate-950/40 text-cyan-400 uppercase font-extrabold border-b border-white/5 text-[11px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Designation / Role</th>
                                <th class="px-6 py-4">Requirements</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Posted On</th>
                                <th class="px-6 py-4 text-right" style="min-width:220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white">
                            @forelse($jobs as $job)
                                @php
                                    $jobCode = $job->job_code ?? ('SH-JOB-' . str_pad((string) $job->id, 6, '0', STR_PAD_LEFT));
                                    $jobInitial = strtoupper(substr($job->title, 0, 1)) ?: 'J';
                                    $approvedCount = $job->jobApplications->where('status', 'Approved')->count();
                                @endphp
                                <tr class="hover:bg-white/5 transition duration-200">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold ring-2 ring-white/10 shrink-0 text-sm">{{ $jobInitial }}</div>
                                            <div class="min-w-0">
                                                <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-white hover:text-cyan-300 transition text-sm">{{ $job->title }}</a>
                                                <div class="text-cyan-200 text-xs truncate mt-0.5"><i class="fa-solid fa-location-dot mr-1 text-slate-500"></i> {{ $job->location }} · {{ $job->job_type }}</div>
                                                <div class="text-[10px] text-slate-500 mt-1">{{ $jobCode }} · {{ $job->openings ?? 1 }} opening(s)</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="text-white font-semibold text-xs">{{ $job->formatted_experience }} exp</div>
                                        <div class="text-[11px] text-slate-400 mt-1">Gender: {{ $job->gender_preference ?? 'Any' }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @php $st = $job->status; @endphp
                                        @if($st === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-check"></i> Active</span>
                                        @elseif($st === 'pending_approval')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold animate-pulse"><i class="fa-regular fa-clock"></i> Pending Approval</span>
                                        @elseif($st === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                                        @elseif($st === 'on_hold')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-pause"></i> On Hold</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-500/20 text-slate-400 border border-slate-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-info"></i> {{ ucwords(str_replace('_',' ',$st)) }}</span>
                                        @endif
                                        @if($job->deactivation_requested_at)
                                            <div class="mt-1.5 inline-flex items-center gap-1 text-[9px] font-bold bg-rose-500/25 text-rose-300 border border-rose-500/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="fa-solid fa-hourglass-half"></i> Closing Requested
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $job->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-3.5 text-right" style="min-width:220px;">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($job->status === 'pending_approval')
                                                <a href="{{ route('client.jobs.edit', $job) }}"
                                                   class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-lg text-xs font-bold border border-amber-300 shadow-md transition animate-pulse"
                                                   style="padding: 0.45rem 0.9rem;">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit Pending
                                                </a>
                                            @else
                                                <a href="{{ route('client.jobs.applicants', $job) }}"
                                                   class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold border border-indigo-400 shadow-md transition"
                                                   style="padding: 0.45rem 0.9rem;">
                                                    <i class="fa-regular fa-eye"></i> View Applicants ({{ $approvedCount }})
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="bg-white/5 inline-block p-6 rounded-full mb-4 border border-white/5">
                                            <i class="fa-regular fa-folder-open text-5xl text-blue-400"></i>
                                        </div>
                                        <p class="font-bold text-white text-lg">No requirements posted yet</p>
                                        <a href="{{ route('client.jobs.create') }}" class="text-blue-400 hover:text-white underline mt-2 inline-block text-sm">Post your first requirement</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination links --}}
                @if($jobs->hasPages())
                    <div class="px-5 py-4 border-t border-white/5 bg-slate-900/10">
                        {{ $jobs->links() }}
                    </div>
                @endif
            </div>

            {{-- Row 6: Detailed Widgets Grid (Exact Copy of JPEG sections & data) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                {{-- Widget 1: Recent Activities (Dynamic Candidate Submissions & Interviews) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Recent Activities</h4>
                        <a href="{{ route('client.applications.index') }}" class="text-xs font-bold text-blue-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-4 pt-1">
                        @php
                            $activitiesList = collect();

                            if (isset($recentApplications)) {
                                foreach($recentApplications as $app) {
                                    $activitiesList->push([
                                        'icon' => 'fa-user-plus',
                                        'color' => 'bg-blue-500/20 text-blue-400',
                                        'text' => 'New profile ' . ($app->candidateUser->name ?? $app->candidate->first_name ?? '') . ' submitted for ' . ($app->job->title ?? ''),
                                        'time' => $app->created_at->diffForHumans()
                                    ]);
                                }
                            }

                            if (isset($recentInterviews)) {
                                foreach($recentInterviews as $interview) {
                                    $activitiesList->push([
                                        'icon' => 'fa-video',
                                        'color' => 'bg-purple-500/20 text-purple-400',
                                        'text' => 'Interview scheduled for ' . ($interview->candidateUser->name ?? $interview->candidate->first_name ?? '') . ' - ' . ($interview->job->title ?? ''),
                                        'time' => \Carbon\Carbon::parse($interview->interview_at)->diffForHumans()
                                    ]);
                                }
                            }

                            // Fallback if no real activities
                            if ($activitiesList->isEmpty()) {
                                $activitiesList = collect([
                                    ['icon' => 'fa-user-plus', 'color' => 'bg-blue-500/20 text-blue-400', 'text' => 'No recent candidate submissions found.', 'time' => 'System Idle'],
                                    ['icon' => 'fa-briefcase', 'color' => 'bg-indigo-500/20 text-indigo-400', 'text' => 'Post a new job requirement to start sourcing.', 'time' => 'System Idle']
                                ]);
                            }
                        @endphp

                        @foreach($activitiesList as $act)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $act['color'] }} flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid {{ $act['icon'] }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-300 font-medium leading-relaxed">{{ $act['text'] }}</p>
                                    <span class="text-[9px] text-slate-500 font-bold block mt-1 uppercase tracking-wide">{{ $act['time'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Widget 2: Top Requirements (Dynamic Job Openings) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Top Requirements</h4>
                        <a href="#my-jobs" class="text-xs font-bold text-blue-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-4 pt-1">
                        @php
                            $topJobs = collect();
                            if (isset($jobs)) {
                                $topJobs = $jobs->sortByDesc(function($j) {
                                    return $j->jobApplications->where('status', 'Approved')->count();
                                })->take(4);
                            }
                        @endphp

                        @foreach($topJobs as $job)
                            <a href="{{ route('client.jobs.index') }}" class="flex items-center justify-between gap-3 p-3 bg-white/5 border border-white/5 rounded-xl hover:bg-white/10 transition block cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-600/10 text-blue-400 flex items-center justify-center text-sm"><i class="fa-solid fa-briefcase"></i></div>
                                    <div>
                                        <h5 class="font-bold text-xs text-white">{{ $job->title }}</h5>
                                        <p class="text-[9px] text-slate-500 mt-0.5">{{ $job->job_location }} · {{ $job->job_type }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-extrabold bg-blue-600/20 text-blue-400 px-2 py-0.5 rounded border border-blue-500/20 shadow-md shrink-0">{{ $job->jobApplications->where('status', 'Approved')->count() }} Submissions</span>
                            </a>
                        @endforeach

                        @if($topJobs->isEmpty())
                            <div class="p-6 bg-white/5 border border-white/5 rounded-xl text-center text-xs text-slate-400">
                                No job postings found. Post a job to see requirements here!
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Widget 3: Performance Overview (Replicating JPEG data & circular gauge exactly) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5 col-span-1 md:col-span-2 xl:col-span-1">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Performance Overview</h4>
                        <span class="text-xs font-bold text-slate-400">This Month</span>
                    </div>

                    {{-- Circular progress ring --}}
                    <div class="flex items-center justify-center gap-6 pt-2">
                        <div class="relative w-24 h-24 flex items-center justify-center shrink-0 shadow-lg shadow-cyan-500/10 rounded-full">
                            <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="40" fill="transparent" stroke="#111827" stroke-width="7"></circle>
                                <circle cx="48" cy="48" r="40" fill="transparent" stroke="#06b6d4" stroke-width="7"
                                        stroke-dasharray="251.2" stroke-dashoffset="45.2" stroke-linecap="round"></circle>
                            </svg>
                            <div class="text-center relative z-10">
                                <span class="text-xl font-black text-white block">82%</span>
                                <span class="text-[8px] text-cyan-400 uppercase font-bold tracking-wider">Excellent</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-300">You are performing great!</p>
                            <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">Keep it up to unlock top metrics and earn gold tier placement perks.</p>
                        </div>
                    </div>

                    {{-- Progress Bars --}}
                    <div class="space-y-2 pt-1">
                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Profile Selection Ratio</span>
                                <span class="text-white">82%</span>
                            </div>
                            <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: 82%"></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Response Time</span>
                                <span class="text-white">90%</span>
                            </div>
                            <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: 90%"></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>Client Satisfaction</span>
                                <span class="text-white">78%</span>
                            </div>
                            <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: 78%"></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
                                <span>On-time Submissions</span>
                                <span class="text-white">85%</span>
                            </div>
                            <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Gold Client Banner --}}
                    <div class="p-2.5 bg-gradient-to-r from-blue-600/10 to-indigo-600/10 rounded-xl border border-blue-500/20 text-center flex items-center justify-center gap-2">
                        <i class="fa-solid fa-medal text-amber-400 text-xs"></i>
                        <span class="text-[9px] font-extrabold text-blue-300 uppercase tracking-wider">Top Performer #12 This Month</span>
                    </div>
                </div>

            </div>

            {{-- Row 7: Trust Cards --}}
            <div class="pt-4">
                <h3 class="text-xl font-bold text-white mb-6 text-center">Why Vendors Love SimplyHiree?</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <div class="glass-card p-6 rounded-2xl text-center flex flex-col items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-lg"><i class="fa-solid fa-users-viewfinder"></i></div>
                        <h4 class="font-bold text-sm text-white">More Business</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Access to 1000+ active requirements and open vacancies.</p>
                    </div>

                    <div class="glass-card p-6 rounded-2xl text-center flex flex-col items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg"><i class="fa-solid fa-bolt"></i></div>
                        <h4 class="font-bold text-sm text-white">Faster Placements</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">AI matching helps you submit the right candidates instantly.</p>
                    </div>

                    <div class="glass-card p-6 rounded-2xl text-center flex flex-col items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg"><i class="fa-solid fa-eye"></i></div>
                        <h4 class="font-bold text-sm text-white">Timely Payments</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Transparent earnings tracker and fast replacement guarantee protection.</p>
                    </div>

                    <div class="glass-card p-6 rounded-2xl text-center flex flex-col items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg"><i class="fa-solid fa-sliders"></i></div>
                        <h4 class="font-bold text-sm text-white">Smart Tools</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Unified workflow panel to manage resumes, rounds, and schedules.</p>
                    </div>

                </div>
            </div>

            {{-- Row 8: Mobile Promo Mock --}}
            <div class="glass-card rounded-2xl p-8 flex flex-col lg:flex-row items-center justify-between gap-8 glow-indigo relative overflow-hidden">
                <div class="absolute right-0 top-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

                <div class="flex-1 max-w-lg">
                    <h3 class="text-xl font-black text-white tracking-tight">Access Anywhere, Anytime</h3>
                    <p class="text-slate-400 mt-2 leading-relaxed text-sm">Download the SimplyHiree App to post vacancies, monitor incoming candidate profiles, schedule video calls, and release invoice payouts instantly from your smartphone.</p>
                    
                    <div class="flex items-center gap-4 mt-6">
                        <div class="w-14 h-14 bg-white p-1 rounded-xl shrink-0 flex items-center justify-center shadow-lg">
                            <div class="w-full h-full border-2 border-slate-900 bg-slate-900 relative">
                                <div class="absolute inset-1.5 border border-white flex flex-wrap gap-1 p-0.5 justify-between">
                                    @for($i = 0; $i < 9; $i++)
                                        <div class="w-2.5 h-2.5 bg-white rounded-[1px]"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white uppercase tracking-wider">Scan to Download</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Compatible with iOS &amp; Android devices</p>
                        </div>
                    </div>
                </div>

                {{-- Mock Phones --}}
                <div class="flex gap-4 shrink-0 overflow-hidden select-none pointer-events-none">
                    <div class="w-32 h-56 bg-slate-950 border-4 border-slate-800 rounded-2xl relative shadow-2xl shrink-0 flex flex-col justify-between p-2">
                        <div class="h-1 w-10 bg-slate-800 rounded-full mx-auto mb-1 shrink-0"></div>
                        <div class="flex-1 rounded-lg bg-slate-900 p-2 flex flex-col gap-2 overflow-hidden">
                            <div class="h-3 w-10 bg-blue-600/30 rounded-md"></div>
                            <div class="grid grid-cols-2 gap-1.5 mt-1">
                                <div class="h-6 bg-white/5 rounded p-1 flex flex-col justify-between"><div class="h-1 w-4 bg-slate-500 rounded"></div><div class="h-1.5 w-6 bg-white rounded"></div></div>
                                <div class="h-6 bg-white/5 rounded p-1 flex flex-col justify-between"><div class="h-1 w-4 bg-slate-500 rounded"></div><div class="h-1.5 w-6 bg-white rounded"></div></div>
                            </div>
                            <div class="h-10 bg-white/5 rounded-lg mt-2 p-1.5 flex flex-col justify-between">
                                <div class="h-1 w-8 bg-slate-500 rounded"></div>
                                <div class="h-1.5 w-12 bg-blue-400 rounded"></div>
                            </div>
                        </div>
                    </div>

                    <div class="w-32 h-56 bg-slate-950 border-4 border-slate-800 rounded-2xl relative shadow-2xl shrink-0 flex flex-col justify-between p-2 hidden sm:flex">
                        <div class="h-1 w-10 bg-slate-800 rounded-full mx-auto mb-1 shrink-0"></div>
                        <div class="flex-1 rounded-lg bg-slate-900 p-2 flex flex-col gap-2 overflow-hidden">
                            <div class="h-3 w-12 bg-emerald-600/30 rounded-md"></div>
                            <div class="space-y-1.5 mt-1">
                                <div class="h-5 bg-white/5 rounded p-1 flex items-center justify-between"><div class="h-1 w-10 bg-white rounded"></div><div class="h-2 bg-emerald-500/20 rounded"></div></div>
                                <div class="h-5 bg-white/5 rounded p-1 flex items-center justify-between"><div class="h-1 w-8 bg-white rounded"></div><div class="h-2 bg-amber-500/20 rounded"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 9: Dynamically rendering statistics brand stats footer --}}
            <div class="pt-4 flex flex-col md:flex-row items-center justify-between border-t border-white/5 gap-6 text-slate-500 text-xs">
                <div class="flex items-center gap-3">
                    <div class="font-extrabold text-white text-sm">SimplyHiree</div>
                    <span class="text-slate-600">|</span>
                    <span>Your Growth. Our Platform.</span>
                </div>
                <div class="flex flex-wrap items-center gap-6 justify-center">
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-users text-blue-500/40"></i> <strong class="text-slate-300 font-bold">1000+</strong> Active Clients</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-500/40"></i> <strong class="text-slate-300 font-bold">50K+</strong> Placements</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-handshake text-purple-500/40"></i> <strong class="text-slate-300 font-bold">10K+</strong> Trusted Partners</div>
                </div>
                <a href="{{ route('client.jobs.create') }}" class="font-extrabold text-blue-400 hover:text-blue-300 transition flex items-center gap-1">
                    Grow your business with SimplyHiree <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

        </main>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const greetingEl = document.getElementById('dynamic-welcome-greeting');
        const greetingSubtitleEl = document.getElementById('dynamic-welcome-subtitle');
        const clientName = @json(Auth::user()->name);

        if (greetingEl) {
            const hour = new Date().getHours();
            const period = hour < 12 ? 'morning' : (hour < 17 ? 'afternoon' : 'evening');
            const greetings = {
                morning: [
                    `Good morning, ${clientName}. Fresh roles, fresh possibilities.`,
                    `Morning, ${clientName}. Your hiring day is ready when you are.`,
                    `Welcome back, ${clientName}. Let's give great talent a reason to say yes.`
                ],
                afternoon: [
                    `Good afternoon, ${clientName}. The best hires rarely wait around.`,
                    `Welcome back, ${clientName}. Your next standout candidate may already be in motion.`,
                    `Hello, ${clientName}. Let's keep the hiring momentum crisp.`
                ],
                evening: [
                    `Good evening, ${clientName}. A little progress now goes a long way tomorrow.`,
                    `Welcome back, ${clientName}. Great teams are built one smart decision at a time.`,
                    `Evening, ${clientName}. Your hiring pipeline is still working for you.`
                ]
            };
            const options = greetings[period];
            const storageKey = `simplyhiree-client-greeting-${period}`;
            const previousGreeting = window.localStorage.getItem(storageKey);
            const available = options.filter(function (message) { return message !== previousGreeting; });
            const selectedGreeting = (available.length ? available : options)[Math.floor(Math.random() * (available.length ? available.length : options.length))];

            window.localStorage.setItem(storageKey, selectedGreeting);
            greetingEl.textContent = selectedGreeting;
            if (greetingSubtitleEl) {
                greetingSubtitleEl.textContent = 'A clear view of your open roles, interviews, and hiring activity.';
            }
        }

        const timeEl = document.getElementById('dashboard-widget-time');
        const weatherIcon = document.getElementById('dashboard-weather-icon');
        const weatherTemp = document.getElementById('dashboard-weather-temp');
        const weatherDesc = document.getElementById('dashboard-weather-desc');
        const weatherStatus = document.getElementById('dashboard-weather-status');

        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            timeEl.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }

        function renderWeather(latitude, longitude) {
            fetch(`https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,weather_code`)
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    const current = data.current;
                    if (!current) throw new Error('No current weather data');

                    const code = current.weather_code;
                    const isRain = (code >= 51 && code <= 67) || (code >= 80 && code <= 82) || code === 95;
                    const isCloudy = (code >= 1 && code <= 3) || (code >= 45 && code <= 48);
                    const description = code === 0 ? 'Clear sky' : (isRain ? 'Rain showers' : (isCloudy ? 'Cloudy' : 'Partly cloudy'));

                    if (code === 0) {
                        weatherIcon.innerHTML = '<i class="fa-solid fa-sun weather-spin text-amber-300 text-xl"></i>';
                    } else if (code === 95) {
                        weatherIcon.innerHTML = '<div class="weather-float relative w-8 h-8"><i class="fa-solid fa-cloud text-slate-300 text-xl absolute left-0 top-0"></i><i class="fa-solid fa-bolt weather-flash absolute left-3 bottom-0 text-amber-300 text-xs"></i></div>';
                    } else if (isRain) {
                        weatherIcon.innerHTML = '<div class="weather-float relative w-8 h-8"><i class="fa-solid fa-cloud text-blue-300 text-xl absolute left-0 top-0"></i><span class="weather-rain-drop absolute left-2 bottom-0 w-px h-2 bg-cyan-200 rounded-full"></span><span class="weather-rain-drop absolute left-4 bottom-0 w-px h-2 bg-cyan-200 rounded-full"></span><span class="weather-rain-drop absolute left-6 bottom-0 w-px h-2 bg-cyan-200 rounded-full"></span></div>';
                    } else if (isCloudy) {
                        weatherIcon.innerHTML = '<div class="weather-float relative w-8 h-8"><i class="fa-solid fa-cloud text-slate-200 text-xl absolute right-0 bottom-0"></i><i class="fa-solid fa-sun weather-spin text-amber-300 text-xs absolute left-0 top-0"></i></div>';
                    } else {
                        weatherIcon.innerHTML = '<div class="weather-float relative w-8 h-8"><i class="fa-solid fa-cloud-sun text-amber-300 text-xl absolute left-0 top-0"></i></div>';
                    }
                    weatherTemp.textContent = `${Math.round(current.temperature_2m)}\u00b0C`;
                    weatherDesc.textContent = description;
                    weatherStatus.textContent = isRain ? 'Take an umbrella' : 'Perfect weather';
                })
                .catch(function () {
                    weatherDesc.textContent = 'Unavailable';
                    weatherStatus.textContent = 'Weather unavailable';
                });
        }

        updateClock();
        window.setInterval(updateClock, 1000);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) { renderWeather(position.coords.latitude, position.coords.longitude); },
                function () { renderWeather(28.61, 77.20); },
                { timeout: 7000 }
            );
        } else {
            renderWeather(28.61, 77.20);
        }

        document.querySelectorAll('a[href="#my-jobs"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                const target = document.getElementById('my-jobs');
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.remove('flash-target');
                void target.offsetWidth; // force reflow so animation can restart
                target.classList.add('flash-target');
                history.replaceState(null, '', '#my-jobs');
            });
        });
    });
</script>
@endsection

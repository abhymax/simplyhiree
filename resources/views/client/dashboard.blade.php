@extends('layouts.app')

@section('content')
<style>
    /* Absolute exact color and layout replication from JPEG */
    nav.glass-nav { display: none !important; }
    footer { display: none !important; }
    html, body { background-color: #06123b !important; font-family: 'Outfit', sans-serif; overflow-x: hidden; margin: 0; padding: 0; }
    /* Neutralise the app layout's light body class + any wrapper bg on this page */
    body.bg-slate-50 { background-color: #06123b !important; }
    .flex.flex-col.min-h-screen { background-color: #06123b !important; }
    main { background-color: #06123b !important; }
    main { padding: 0 !important; }

    /* Scrollbar Styling */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #06123b; }
    ::-webkit-scrollbar-thumb { background: #111827; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #1f2937; }

    /* Left Sidebar - Exact Match */
    .custom-sidebar {
        width: 250px;
        background-color: #06123b;
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
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .custom-sidebar-link:hover {
        background-color: rgba(255, 255, 255, 0.02);
        color: #f8fafc;
    }

    .custom-sidebar-link.active {
        background-color: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
        font-weight: 600;
    }

    .custom-sidebar-footer {
        padding: 16px;
        border-top: 1px solid rgba(59, 130, 246, 0.08);
        background-color: rgba(0, 0, 0, 0.25);
        flex-shrink: 0;
    }

    /* Main Workspace Layout */
    .custom-main-content {
        margin-left: 250px;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #06123b;
    }

    .custom-header {
        height: 70px;
        background-color: rgba(6, 18, 59, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
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
        padding: 28px;
        display: flex;
        flex-direction: column;
        gap: 28px;
        max-width: 1180px;   /* contained, not full-width */
        width: 100%;
        margin: 0 auto;
    }

    /* Section cards — slightly translucent so the page bg shows through */
    .glass-card {
        background-color: rgba(35, 27, 128, 0.55) !important;
        border: 1px solid rgba(59, 130, 246, 0.15) !important;
        backdrop-filter: blur(6px) !important;
        -webkit-backdrop-filter: blur(6px) !important;
    }

    /* Card Outlines & Backgrounds (Exact Match) */
    .metric-card-indigo {
        background: #081035;
        border: 1px solid #1E40AF;
    }
    .metric-card-purple {
        background: #120d24;
        border: 1px solid #5B21B6;
    }
    .metric-card-emerald {
        background: #041610;
        border: 1px solid #065F46;
    }
    .metric-card-amber {
        background: #1c0d06;
        border: 1px solid #92400E;
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
    .funnel-stage-1 { background-color: #1e3a8a; } /* Submitted */
    .funnel-stage-2 { background-color: #3b82f6; } /* Shortlisted */
    .funnel-stage-3 { background-color: #4f46e5; } /* Interview */
    .funnel-stage-4 { background-color: #0d9488; } /* Offered */
    .funnel-stage-5 { background-color: #ea580c; } /* Joined */

    /* Quick Workspace Border styles */
    .workspace-card-blue { border: 1px solid #1e3a8a; }
    .workspace-card-orange { border: 1px solid #78350f; }
    .workspace-card-purple { border: 1px solid #4c1d95; }
    .workspace-card-indigo { border: 1px solid #312e81; }
    .workspace-card-emerald { border: 1px solid #064e3b; }

    /* Mobile view toggle */
    @media (max-width: 1024px) {
        .custom-sidebar {
            transform: translateX(-100%);
        }
        .custom-sidebar.open {
            transform: translateX(0);
        }
    }

    /* Sidebar Logout Contrast Button */
    .sidebar-logout-btn {
        background-color: rgba(239, 68, 68, 0.16) !important;
        border: 1px solid rgba(239, 68, 68, 0.35) !important;
        color: #f87171 !important;
        font-weight: 700 !important;
    }
    .sidebar-logout-btn:hover {
        background-color: rgba(239, 68, 68, 0.28) !important;
        color: #ffffff !important;
        border-color: rgba(239, 68, 68, 0.5) !important;
    }

    /* SVG Circle Premium Micro-Animations */
    svg circle {
        transition: r 0.2s cubic-bezier(0.4, 0, 0.2, 1), stroke-width 0.2s ease, stroke 0.2s ease;
    }
    svg circle:hover {
        r: 7.5 !important;
        stroke-width: 3.5 !important;
        stroke: #ffffff !important;
    }

    /* Live Weather Widget & Keyframe Animations */
    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    @keyframes rain-drop {
        0% { transform: translateY(-5px); opacity: 0; }
        30% { opacity: 1; }
        100% { transform: translateY(12px); opacity: 0; }
    }
    @keyframes thunder-flash {
        0%, 90%, 94%, 98%, 100% { opacity: 0.15; }
        92%, 96% { opacity: 1; filter: drop-shadow(0 0 4px #eab308); }
    }
    @keyframes pulse-slow {
        0%, 100% { opacity: 0.9; }
        50% { opacity: 1; }
    }

    .animate-spin-slow {
        animation: spin-slow 15s linear infinite;
    }
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
    .animate-rain-1 {
        animation: rain-drop 1.4s linear infinite;
    }
    .animate-rain-2 {
        animation: rain-drop 1.4s linear infinite 0.45s;
    }
    .animate-rain-3 {
        animation: rain-drop 1.4s linear infinite 0.9s;
    }
    .animate-flash {
        animation: thunder-flash 3.5s ease-in-out infinite;
    }
    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
</style>

<div class="min-h-screen bg-[#06123b] text-[#f8fafc] flex" x-data="{ sidebarOpen: false }">

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
                    ['icon' => 'fa-solid fa-chart-line', 'label' => 'Dashboard', 'route' => route('client.dashboard'), 'active' => true],
                    ['icon' => 'fa-solid fa-briefcase', 'label' => 'My Jobs', 'route' => route('client.jobs.index'), 'active' => false],
                    ['icon' => 'fa-solid fa-file-lines', 'label' => 'Applications', 'route' => route('client.applications.index'), 'active' => false],
                    ['icon' => 'fa-solid fa-video', 'label' => 'Interviews', 'route' => route('client.interviews.calendar'), 'active' => false],
                    ['icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Replacements', 'route' => route('client.applications.index', ['joined_status' => 'Left']), 'active' => false],
                    ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Invoices & Billing', 'route' => route('client.billing'), 'active' => false],
                    ['icon' => 'fa-solid fa-gear', 'label' => 'Settings', 'route' => route('client.profile.company'), 'active' => false],
                    ['icon' => 'fa-solid fa-circle-question', 'label' => 'Help & Support', 'route' => route('support'), 'active' => false],
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

        {{-- User Section --}}
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
            {{-- Search --}}
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <div class="relative w-full hidden sm:block">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm z-10"></i>
                    <input type="text" placeholder="Search candidates, jobs, clients..."
                           style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.25);"
                           class="w-full h-10 rounded-lg pl-10 pr-4 text-sm text-white placeholder-slate-300 focus:outline-none focus:border-blue-400 transition">
                </div>
            </div>

            {{-- Right Controls --}}
            <div class="flex items-center gap-5">
                {{-- Live Weather & Date-Time Widget --}}
                <div id="live-weather-widget" class="hidden md:flex items-center gap-4 px-3.5 py-1.5 rounded-xl bg-slate-950/30 border border-white/5 backdrop-blur-md text-xs">
                    <!-- Live Time & Date -->
                    <div class="flex flex-col items-end pr-3.5 border-r border-white/10 shrink-0">
                        <div id="widget-time" class="font-extrabold text-white tracking-wider text-[12.5px] leading-tight">--:--:-- --</div>
                        <div id="widget-date" class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ date('l, M j, Y') }}</div>
                    </div>
                    
                    <!-- Weather Data & Animation -->
                    <div id="weather-loading" class="flex items-center gap-2 text-slate-400 font-bold text-[10px] uppercase tracking-wider shrink-0">
                        <svg class="animate-spin h-3 w-3 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading...</span>
                    </div>
                    
                    <div id="weather-info" class="hidden items-center gap-2.5 shrink-0">
                        <!-- Animated Weather Icon Container -->
                        <div id="weather-icon-container" class="relative w-8 h-8 flex items-center justify-center shrink-0">
                            <!-- Injected by JS -->
                        </div>
                        
                        <div class="flex flex-col select-none">
                            <div class="flex items-center gap-1.5 leading-none">
                                <span id="weather-temp" class="font-extrabold text-white text-[13px]">--°C</span>
                                <span id="weather-desc" class="text-[8.5px] text-slate-300 font-bold uppercase tracking-wide">--</span>
                            </div>
                            
                            <!-- Warning Badge -->
                            <div id="weather-warning" class="hidden mt-1 text-[7.5px] font-extrabold bg-blue-500/10 text-blue-300 border border-blue-500/20 px-1.5 py-0.5 rounded uppercase tracking-wider items-center gap-1 animate-pulse-slow">
                                <span id="warning-text">Forecast clear</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Real notifications bell (Livewire) --}}
                <div class="text-slate-300">
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
                    <h1 id="dynamic-welcome-greeting" class="text-2xl font-extrabold text-white tracking-tight">Welcome back, {{ Auth::user()->name }} 👋</h1>
                    <p class="text-xs text-slate-400 mt-1">Here's what's happening with your business today.</p>
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
                
                {{-- Metric Card 1: Awaiting Review (candidates needing the client's decision) --}}
                <a href="{{ route('client.applications.index', ['status' => 'Approved']) }}" class="metric-card-indigo p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-blue-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Awaiting Review</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $awaitingReview ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-blue-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-user-clock"></i>
                        <span>Candidates need your decision</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-blue-500"><i class="fa-solid fa-user-clock"></i></div>
                </a>

                {{-- Metric Card 2: Interviews Today (Purple) --}}
                <a href="{{ route('client.interviews.calendar') }}" class="metric-card-purple p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-purple-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Interviews Today</p>
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
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Active Applicants</p>
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
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Invoices Due</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $dueInvoicesCount ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-amber-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Outstanding: ₹{{ number_format($totalOutstandingInvoices ?? 145000) }}</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-amber-500"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </a>
            </div>

            {{-- Row 2: Daily Pulse + Submission Trend (compact) --}}
            <div class="glass-card rounded-2xl p-5 flex flex-col gap-5 w-full">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-heart-pulse text-blue-400"></i> Daily Pulse
                        </h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Your activity summary for today</p>
                    </div>
                    <span class="text-[10px] font-bold bg-blue-600/15 text-blue-300 px-2.5 py-1 rounded-full border border-blue-500/20">Active Pipeline</span>
                </div>

                {{-- Compact metric row: solid colour icon + value + label --}}
                @php
                    // [solid icon colour, tinted card bg, border tint]
                    $pulseSolid = [
                        'blue'    => ['#3b82f6', 'rgba(59,130,246,0.12)',  'rgba(59,130,246,0.30)'],
                        'indigo'  => ['#6366f1', 'rgba(99,102,241,0.12)',  'rgba(99,102,241,0.30)'],
                        'emerald' => ['#10b981', 'rgba(16,185,129,0.12)',  'rgba(16,185,129,0.30)'],
                        'amber'   => ['#f59e0b', 'rgba(245,158,11,0.12)',  'rgba(245,158,11,0.30)'],
                        'rose'    => ['#f43f5e', 'rgba(244,63,94,0.12)',   'rgba(244,63,94,0.30)'],
                    ];
                @endphp
                <div style="display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px;">
                    @foreach($dailyPulse ?? [] as $pulse)
                        @php $sc = $pulseSolid[$pulse['color']] ?? $pulseSolid['blue']; @endphp
                        <a href="{{ $pulse['link'] ?? '#' }}"
                           class="flex flex-col items-center text-center gap-1.5 px-2 py-3 rounded-xl transition hover:scale-[1.03] hover:brightness-110"
                           style="background: {{ $sc[1] }}; border: 1px solid {{ $sc[2] }};">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $sc[0] }};">
                                <i class="fa-solid {{ $pulse['icon'] }} text-white text-[11px]"></i>
                            </div>
                            <div class="text-base font-extrabold text-white leading-none">{{ $pulse['value'] }}</div>
                            <div class="text-[10px] text-slate-300 font-semibold leading-tight">{{ $pulse['label'] }}</div>
                        </a>
                    @endforeach
                </div>

                {{-- Interview Activity Trend — real SVG line chart --}}
                @php
                    $trend = $submissionTrend ?? [];
                    $n = max(count($trend), 1);
                    $trendMax = max(1, collect($trend)->max('count') ?? 0);
                    $trend7Total = collect($trend)->sum('count');
                    // Build polyline points across a 600x100 viewBox (10px top/bottom padding)
                    $pts = [];
                    foreach (array_values($trend) as $idx => $pt) {
                        $x = $n > 1 ? round($idx / ($n - 1) * 580 + 10, 1) : 300;
                        $y = round(90 - ($pt['count'] / $trendMax) * 78, 1); // 90 baseline, 78 usable height
                        $pts[] = ['x' => $x, 'y' => $y, 'c' => $pt['count'], 'l' => $pt['label']];
                    }
                    $linePath = collect($pts)->map(fn($p) => $p['x'].','.$p['y'])->implode(' ');
                    $areaPath = '10,100 '.$linePath.' '.($pts[count($pts)-1]['x'] ?? 590).',100';
                @endphp
                <div>
                    <div class="flex justify-between items-baseline mb-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Interview Activity · Last 2 Weeks</p>
                        <span class="text-[10px] font-bold text-blue-400">{{ $trend7Total }} interviews this fortnight</span>
                    </div>
                    <div class="h-28 bg-black/20 rounded-xl border border-white/5 relative px-2 pt-2">
                        <!-- Custom Tooltip -->
                        <div id="chart-tooltip" class="absolute pointer-events-none opacity-0 bg-slate-950/95 text-white text-[10px] px-2.5 py-1.5 rounded-lg border border-blue-500/40 shadow-2xl transition-all duration-150 backdrop-blur-md z-20 flex flex-col gap-0.5" style="left: 0; top: 0;">
                            <span class="font-bold text-blue-400" id="tooltip-date"></span>
                            <span class="text-slate-200" id="tooltip-stats"></span>
                        </div>
                        
                        <svg class="w-full h-[78px]" viewBox="0 0 600 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.35"></stop>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"></stop>
                                </linearGradient>
                            </defs>
                            <polygon points="{{ $areaPath }}" fill="url(#trendGrad)"></polygon>
                            <polyline points="{{ $linePath }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"></polyline>
                            @foreach($pts as $p)
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="5" fill="#0b1020" stroke="#60a5fa" stroke-width="2" style="cursor:pointer"
                                        onmouseover="showChartTooltip(event, '{{ $p['l'] }}', '{{ $p['c'] }}')"
                                        onmouseout="hideChartTooltip()">
                                </circle>
                            @endforeach
                        </svg>
                        <div class="flex justify-between text-[6.5px] sm:text-[7.5px] text-slate-500 font-normal px-1 mt-1 tracking-tighter">
                            @foreach($pts as $p)
                                <span class="{{ $p['l'] === 'Today' ? 'text-blue-300 font-semibold' : '' }}">{{ $p['l'] }}</span>
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

                    @php
                        $funnelData = array_values($funnel ?? []);
                        $funnelShortlisted = $funnelShortlisted ?? (collect($funnelData)->firstWhere('label', 'Shortlisted')['count'] ?? 0);
                        $funnelJoined = $funnelJoined ?? (collect($funnelData)->firstWhere('label', 'Joined')['count'] ?? 0);
                        // Reference palette: blue shades -> teal -> orange tip
                        $funnelFill = ['#2563eb', '#3b82f6', '#0d9488', '#ea580c'];
                        // Cone centred at cx; tapers from 140 half-width to a point (last band = triangle).
                        $cx = 150; $halfW = [140, 105, 70, 35, 0]; $bandH = 55;
                    @endphp

                    <style>
                        .funnel-svg a { cursor: pointer; }
                        .funnel-svg a polygon { transition: filter .2s ease; }
                        .funnel-svg a:hover polygon { filter: brightness(1.18); }
                    </style>
                    <svg class="funnel-svg w-full" viewBox="0 0 380 232" style="max-height: 250px;">
                        @foreach($funnelData as $i => $stg)
                            @php
                                $yTop = $i * $bandH; $yBot = $yTop + $bandH + 1; // +1 overlap removes hairline seams
                                $tl = $cx - $halfW[$i];   $tr = $cx + $halfW[$i];
                                $bl = $cx - $halfW[$i+1]; $br = $cx + $halfW[$i+1];
                                $midY = $yTop + ($bandH / 2) + 4;
                            @endphp
                            <a href="{{ $stg['link'] ?? '#' }}">
                                <title>{{ $stg['label'] }}: {{ $stg['count'] }} — click to view</title>
                                <polygon points="{{ $tl }},{{ $yTop }} {{ $tr }},{{ $yTop }} {{ $br }},{{ $yBot }} {{ $bl }},{{ $yBot }}"
                                         fill="{{ $funnelFill[$i] ?? '#ea580c' }}"></polygon>
                                {{-- stage label centred inside the cone --}}
                                <text x="{{ $cx }}" y="{{ $midY }}" text-anchor="middle" fill="#fff" font-size="12.5" font-weight="700">{{ $stg['label'] }}</text>
                                {{-- count right-aligned in its own column --}}
                                <text x="372" y="{{ $midY }}" text-anchor="end" fill="#fff" font-size="16" font-weight="900">{{ $stg['count'] }}</text>
                            </a>
                        @endforeach
                    </svg>

                    {{-- conversion footnote --}}
                    @php
                        $subVal = $funnelData[0]['count'] ?? 0;
                        $joinVal = $funnelData[count($funnelData)-1]['count'] ?? 0;
                        $conv = $subVal > 0 ? round($joinVal / $subVal * 100, 1) : 0;
                    @endphp
                    <div class="mt-3 text-center text-[10px] text-slate-400 font-semibold">
                        Overall conversion: <span class="text-emerald-400 font-bold">{{ $conv }}%</span>
                        ({{ $joinVal }} joined of {{ $subVal }} shortlisted)
                    </div>
                </div>

                {{-- Right Widget: Billing & Hiring Analytics --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Billing &amp; Hiring Analytics</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Financial &amp; performance summary</p>
                    </div>

                    @php
                        $totalBilled = $totalPaidInvoices + $totalOutstandingInvoices;
                        $billingPaidPct = $totalBilled > 0 ? (int) round($totalPaidInvoices / $totalBilled * 100) : 0;
                        $hiringSuccessRate = $funnelShortlisted > 0 ? (int) round($funnelJoined / $funnelShortlisted * 100) : 0;
                        $reviewRate = $performance['response_rate'] ?? 0;
                        $interviewRate = $performance['interview_rate'] ?? 0;

                        $perfRows = [
                            ['label' => 'Shortlisted Hired',   'value' => $hiringSuccessRate, 'color' => 'emerald', 'hex' => '#10b981'],
                            ['label' => 'Invoices Settled',    'value' => $billingPaidPct,    'color' => 'blue',    'hex' => '#3b82f6'],
                            ['label' => 'Profiles Reviewed',   'value' => $reviewRate,        'color' => 'amber',   'hex' => '#f59e0b'],
                            ['label' => 'Interviews Scheduled', 'value' => $interviewRate,     'color' => 'purple',  'hex' => '#a855f7'],
                        ];
                    @endphp

                    <div class="space-y-4">
                        @foreach($perfRows as $row)
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-300">{{ $row['label'] }}</span>
                                <span class="text-xs font-bold text-{{ $row['color'] }}-400">{{ $row['value'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ min($row['value'], 100) }}%; background: {{ $row['hex'] }}"></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-3 bg-blue-600/10 border border-blue-500/20 rounded-xl text-center text-[10px] font-extrabold text-blue-400 uppercase tracking-wide">
                        {{ $funnelJoined }} of {{ $funnelShortlisted }} shortlisted candidates successfully joined
                    </div>
                </div>
            </div>

            {{-- Row 4: Quick Workspace --}}
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Workspace
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    {{-- Workspace 1: Successful Hires --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-blue flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Successful Hires</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ number_format($funnelJoined) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Candidates onboarded successfully</p>
                        </div>
                        <a href="{{ route('client.applications.index', ['joined_status' => 'Joined']) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Hired
                        </a>
                    </div>

                    {{-- Workspace 2: Approved Applications --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-orange flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Applications</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $totalApplicants ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Approved candidates</p>
                        </div>
                        <a href="{{ route('client.applications.index') }}" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Review Applications
                        </a>
                    </div>

                    {{-- Workspace 3: Interviews Today --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-purple flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Interviews</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $todayInterviews ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Scheduled today</p>
                        </div>
                        <a href="{{ route('client.interviews.calendar') }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Calendar
                        </a>
                    </div>

                    {{-- Workspace 4: Active Openings --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-indigo flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Openings</p>
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
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Invoices & Billing</p>
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
                            @forelse($jobs->take(5) as $job)
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
                @if(($totalJobs ?? 0) > 5)
                    <div class="p-4 border-t border-white/5 text-center">
                        <a href="{{ route('client.jobs.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-400 hover:text-blue-300 transition">
                            View all {{ $totalJobs }} requirements <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Row 6: Detailed Widgets Grid (Exact Copy of JPEG sections & data) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                {{-- Widget 1: Notifications (Replacing Recent Activities) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Notifications</h4>
                        <a href="#" onclick="alert('Notification page coming soon'); return false;" class="text-xs font-bold text-blue-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-4 pt-1">
                        @php
                            $notifications = auth()->user()->notifications()->take(5)->get();
                        @endphp

                        @forelse($notifications as $notification)
                            @php
                                $icon = $notification->data['icon'] ?? 'circle-info';
                                $color = 'bg-blue-500/20 text-blue-400';
                                if($icon == 'check-circle' || $icon == 'user-check') $color = 'bg-green-500/20 text-green-400';
                                if($icon == 'x-circle' || $icon == 'user-xmark') $color = 'bg-red-500/20 text-red-400';
                                if($icon == 'calendar-event') $color = 'bg-purple-500/20 text-purple-400';
                            @endphp
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $color }} flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-{{ str_replace('circle-check', 'check-circle', str_replace('circle-xmark', 'x-circle', $icon)) }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-300 font-medium leading-relaxed">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    <span class="text-[9px] text-slate-500 font-bold block mt-1 capitalize tracking-wide">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-500/20 text-slate-400 flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-bell-slash"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-300 font-medium leading-relaxed">No recent notifications.</p>
                                    <span class="text-[9px] text-slate-500 font-bold block mt-1 capitalize tracking-wide">System Idle</span>
                                </div>
                            </div>
                        @endforelse
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
                            <div class="flex items-center justify-between gap-3 p-3 bg-slate-950/50 border border-white/10 rounded-xl hover:bg-slate-950/70 hover:border-white/20 transition block">
                                <a href="{{ route('jobs.show', $job->id) }}" class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-blue-600/10 text-blue-400 flex items-center justify-center text-sm shrink-0"><i class="fa-solid fa-briefcase"></i></div>
                                    <div class="min-w-0">
                                        <h5 class="font-bold text-xs text-white truncate hover:text-cyan-300 transition">{{ $job->title }}</h5>
                                        <p class="text-[9px] text-slate-500 mt-0.5 truncate">{{ $job->location ?? '—' }} · {{ $job->job_type ?? '—' }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('client.jobs.applicants', $job) }}" class="text-[10px] font-extrabold bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 px-2 py-0.5 rounded border border-blue-500/20 shadow-md shrink-0 transition cursor-pointer">
                                    {{ $job->jobApplications->where('status', 'Approved')->count() }} Applicants
                                </a>
                            </div>
                        @endforeach

                        @if($topJobs->isEmpty())
                            <div class="p-6 bg-white/5 border border-white/5 rounded-xl text-center text-xs text-slate-400">
                                No job postings found. Post a job to see requirements here!
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Widget 3: Hiring & Billing Success (Replicating JPEG data & circular gauge exactly) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5 col-span-1 md:col-span-2 xl:col-span-1">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Hiring &amp; Billing Success</h4>
                        <span class="text-xs font-bold text-slate-400">This Month</span>
                    </div>

                    @php
                        $perf = $performance ?? [];
                        $totalBilled = $totalPaidInvoices + $totalOutstandingInvoices;
                        $billingPaidPct = $totalBilled > 0 ? (int) round($totalPaidInvoices / $totalBilled * 100) : 0;
                        $hiringSuccessRate = $funnelShortlisted > 0 ? (int) round($funnelJoined / $funnelShortlisted * 100) : 0;
                        $reviewRate = $perf['response_rate'] ?? 0;
                        $interviewRate = $perf['interview_rate'] ?? 0;

                        // Overall score = average of the 4 client-centric metrics
                        $overall = (int) round(collect([
                            $hiringSuccessRate,
                            $billingPaidPct,
                            $reviewRate,
                            $interviewRate,
                        ])->avg());
                        $circumference = 251.2;
                        $offset = $circumference - ($circumference * min($overall, 100) / 100);
                        $rating = $overall >= 75 ? 'Excellent' : ($overall >= 50 ? 'Good' : ($overall >= 25 ? 'Fair' : 'Getting Started'));
                        $perfBars = [
                            ['label' => 'Shortlisted Hired',   'value' => $hiringSuccessRate, 'hex' => '#10b981'],
                            ['label' => 'Invoices Settled',    'value' => $billingPaidPct,    'hex' => '#3b82f6'],
                            ['label' => 'Profiles Reviewed',   'value' => $reviewRate,        'hex' => '#f59e0b'],
                            ['label' => 'Interviews Scheduled', 'value' => $interviewRate,     'hex' => '#a855f7'],
                        ];
                    @endphp

                    {{-- Circular progress ring (real overall score) --}}
                    <div class="flex items-center justify-center gap-6 pt-2">
                        <div class="relative w-36 h-36 flex items-center justify-center shrink-0 shadow-lg shadow-cyan-500/10 rounded-full">
                            <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="42" fill="transparent" stroke="#111827" stroke-width="5"></circle>
                                <circle cx="48" cy="48" r="42" fill="transparent" stroke="#06b6d4" stroke-width="5"
                                        stroke-dasharray="{{ 2 * 3.1416 * 42 }}" stroke-dashoffset="{{ (2 * 3.1416 * 42) - ((2 * 3.1416 * 42) * min($overall,100) / 100) }}" stroke-linecap="round"></circle>
                            </svg>
                            <div class="text-center relative z-10 leading-tight" style="max-width: 88px;">
                                <span class="text-2xl font-black text-white block leading-none">{{ $overall }}%</span>
                                <span class="text-[9px] text-cyan-300 font-bold leading-tight block mt-1 whitespace-normal">{{ $rating }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-300">Hiring &amp; Payment health score</p>
                            <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">Average across candidate conversions, invoice clearings, and interview rates in your workspace.</p>
                        </div>
                    </div>

                    {{-- Progress Bars (real) --}}
                    <div class="space-y-2 pt-1">
                        @foreach($perfBars as $bar)
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] font-bold text-slate-300">
                                    <span>{{ $bar['label'] }}</span>
                                    <span class="text-white">{{ $bar['value'] }}%</span>
                                </div>
                                <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ min($bar['value'], 100) }}%; background: {{ $bar['hex'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="pt-4 flex flex-col md:flex-row items-center justify-between border-t border-white/5 gap-4 text-slate-500 text-xs">
                <div class="flex items-center gap-3">
                    <div class="font-extrabold text-white text-sm">SimplyHiree</div>
                    <span class="text-slate-600">|</span>
                    <span>Your Growth. Our Platform.</span>
                </div>
                <a href="{{ route('support') }}" class="hover:text-white transition">Help &amp; Support</a>
            </div>

        </main>
    </div>

</div>

<script>
    // Premium Chart Floating Tooltip Functions
    function showChartTooltip(event, label, count) {
        const tooltip = document.getElementById('chart-tooltip');
        if (!tooltip) return;
        
        document.getElementById('tooltip-date').innerText = label;
        document.getElementById('tooltip-stats').innerText = count + (count == 1 ? ' Interview' : ' Interviews');
        
        const container = tooltip.parentElement;
        const rect = container.getBoundingClientRect();
        
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top - 45; // slightly above point
        
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
        tooltip.style.transform = 'translateX(-50%)';
        tooltip.style.opacity = '1';
    }

    function hideChartTooltip() {
        const tooltip = document.getElementById('chart-tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
        }
    }
    
    // Bind to window context so inline handlers have instant access
    window.showChartTooltip = showChartTooltip;
    window.hideChartTooltip = hideChartTooltip;

    document.addEventListener('DOMContentLoaded', function () {
        // Smooth scroll for My Jobs link
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

        // 1. Live Digital Clock
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // hour 0 is 12
            const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;
            
            const timeEl = document.getElementById('widget-time');
            if (timeEl) {
                timeEl.innerText = formattedTime;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. Witty Greeting Templates & Consecutive-Uniqueness Logic
        const clientName = "{{ Auth::user()->name }}";
        const timeGreetings = {
            morning: [
                `Rise and shine, ${clientName}! Let's build a dream team today.`,
                `Fresh morning, fresh goals. Welcome back, ${clientName}!`,
                `Ready to discover some premium talent today, ${clientName}?`,
                `Top of the morning, ${clientName}! Let's check those stats.`,
                `Morning, ${clientName}! Fresh coffee, fresh candidates. Let's hire!`,
                `Rise, shine, and recruit! Welcome back, ${clientName}!`,
                `A stellar morning to you, ${clientName}! The pipeline is looking bright.`
            ],
            afternoon: [
                `Good afternoon, ${clientName}! Let's convert those interviews into offers.`,
                `Hope your coffee is strong and your pipeline is stronger, ${clientName}!`,
                `Welcome back, ${clientName}. Let's make this afternoon count!`,
                `Post-lunch productivity spike? Let's check the candidates, ${clientName}!`,
                `Harnessing that afternoon energy, ${clientName}? Let's close some roles!`,
                `Good afternoon, ${clientName}. Time to make some executive decisions.`
            ],
            evening: [
                `Good evening, ${clientName}. Wrapping up a great day, or just getting started?`,
                `Sun is setting but your hiring pipeline is still hot, ${clientName}!`,
                `A wonderful evening to you, ${clientName}! Let's review the pipeline before we log off.`,
                `Evening check-in, ${clientName}! Some stellar profiles just arrived.`,
                `The sun is setting, but the talent hunt never rests, ${clientName}!`
            ],
            night: [
                `Burning the midnight oil, ${clientName}? The dedication is stellar!`,
                `Late-night scouting? You're a true hiring champion, ${clientName}.`,
                `Hustling after hours, ${clientName}? Sleep can wait, talent can't!`,
                `Quiet night, busy pipeline. Welcome back, ${clientName}.`,
                `Night shift recruitment power-hour! Welcome back, ${clientName}!`,
                `Dedication has a name, and it is ${clientName}. Late-night hiring mode active!`
            ]
        };

        const weatherTwists = {
            rain: [
                "It is pouring outside, but we're making it rain job offers! 🌧️",
                "Perfect cozy weather to review some resumes indoors. ☔",
                "Stormy outside, but your hiring pipeline is smooth sailing! ⛈️",
                "Raining cats and dogs outside, but we are raining talent in here! 🌧️"
            ],
            hot: [
                "It is scorching outside, but your hiring activity is even hotter! 🥵🔥",
                "Stay cool indoors while we heat up the candidate pipeline! ☀️🥤",
                "Blazing sun outside, sizzling talent inside! ☀️",
                "Heat index is high, but your pipeline's success rate is higher! 🌡️"
            ],
            cold: [
                "Chilly breeze outside! Let's warm up the dashboard. ❄️☕",
                "Grab a hot beverage while reviewing these heartwarming profiles! 🧣",
                "Cold weather outside, warm candidates inside! ❄️"
            ],
            cloudy: [
                "Overcast today, but your hiring vision is crystal clear! ☁️",
                "Foggy weather? Let's cut through the haze and find that perfect match! 🌫️",
                "Cloudy skies can't dampen this sparkling pipeline! 🌥️"
            ],
            clear: [
                "Clear skies outside, and a clear path to your next hire inside! 🌤️",
                "What a gorgeous day! Let's match this beautiful weather with some beautiful offers. 🌸",
                "Sunny and beautiful! Time to bring some sunshine to a candidate's inbox. ☀️"
            ]
        };

        function getHourCategory() {
            const hour = new Date().getHours();
            if (hour >= 5 && hour < 12) return 'morning';
            if (hour >= 12 && hour < 17) return 'afternoon';
            if (hour >= 17 && hour < 21) return 'evening';
            return 'night';
        }

        function setWittyGreeting(weatherCategory = null) {
            const welcomeEl = document.getElementById('dynamic-welcome-greeting');
            if (!welcomeEl) return;
            
            const timeCat = getHourCategory();
            const timeList = timeGreetings[timeCat];
            
            // Get last selected indices from localStorage to guarantee non-repetition
            const lastTimeKey = `last_g_time_${timeCat}`;
            const lastTimeIdx = parseInt(localStorage.getItem(lastTimeKey) ?? -1, 10);
            
            let availableTimeIndices = Array.from({length: timeList.length}, (_, i) => i);
            if (timeList.length > 1 && lastTimeIdx >= 0 && lastTimeIdx < timeList.length) {
                availableTimeIndices = availableTimeIndices.filter(idx => idx !== lastTimeIdx);
            }
            const timeIdx = availableTimeIndices[Math.floor(Math.random() * availableTimeIndices.length)];
            localStorage.setItem(lastTimeKey, timeIdx);
            const baseGreeting = timeList[timeIdx];
            
            if (weatherCategory && weatherTwists[weatherCategory]) {
                const twistList = weatherTwists[weatherCategory];
                const lastWeatherKey = `last_g_weather_${weatherCategory}`;
                const lastWeatherIdx = parseInt(localStorage.getItem(lastWeatherKey) ?? -1, 10);
                
                let availableWeatherIndices = Array.from({length: twistList.length}, (_, i) => i);
                if (twistList.length > 1 && lastWeatherIdx >= 0 && lastWeatherIdx < twistList.length) {
                    availableWeatherIndices = availableWeatherIndices.filter(idx => idx !== lastWeatherIdx);
                }
                const weatherIdx = availableWeatherIndices[Math.floor(Math.random() * availableWeatherIndices.length)];
                localStorage.setItem(lastWeatherKey, weatherIdx);
                const weatherTwist = twistList[weatherIdx];
                
                welcomeEl.innerHTML = `${baseGreeting} <span class="text-blue-400 font-semibold block sm:inline sm:ml-1 text-sm mt-1 sm:mt-0">${weatherTwist}</span>`;
            } else {
                welcomeEl.innerText = baseGreeting;
            }
        }

        // Run baseline immediately
        setWittyGreeting();

        // 3. OpenMeteo Live Weather Fetch
        function fetchWeather(lat, lon) {
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,rain,showers,snowfall,weather_code,wind_speed_10m`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const current = data.current;
                    if (!current) return;
                    
                    const temp = Math.round(current.temperature_2m);
                    const code = current.weather_code;
                    const wind = current.wind_speed_10m;
                    const prec = current.precipitation;
                    
                    let desc = "Clear Sky";
                    let iconHtml = "";
                    let wCat = "clear";
                    let warningText = "";
                    let isWarning = false;
                    
                    // Decode weather code (WMO standard)
                    if (code === 0) {
                        desc = "Clear Sky";
                        iconHtml = `
                            <svg class="w-7 h-7 text-amber-400 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="5" fill="rgba(251, 191, 36, 0.2)"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707-.707"></path>
                            </svg>`;
                        wCat = "clear";
                    } else if (code >= 1 && code <= 3) {
                        desc = code === 1 ? "Mainly Clear" : (code === 2 ? "Partly Cloudy" : "Overcast");
                        iconHtml = `
                            <div class="relative w-8 h-8 animate-float">
                                <svg class="absolute top-0.5 left-0.5 w-5 h-5 text-amber-400 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"></circle>
                                    <path d="M12 3v1m0 16v1m9-9h-1M4 12H3"></path>
                                </svg>
                                <svg class="absolute bottom-0 right-0 w-6 h-6 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.36 10.04a6 6 0 00-11.44-1.74 4 4 0 00-6.28 4.78 4 4 0 005.18 4.88h11.24a4.5 4.5 0 001.3-8.92z"></path>
                                </svg>
                            </div>`;
                        wCat = "cloudy";
                    } else if (code >= 45 && code <= 48) {
                        desc = "Foggy";
                        iconHtml = `
                            <svg class="w-7 h-7 text-slate-400 animate-float" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M5 18h14M8 6h8"></path>
                            </svg>`;
                        wCat = "cloudy";
                    } else if ((code >= 51 && code <= 67) || (code >= 80 && code <= 82)) {
                        desc = code >= 80 ? "Rain Showers" : "Rainy";
                        iconHtml = `
                            <div class="relative w-8 h-8 animate-float">
                                <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.36 10.04a6 6 0 00-11.44-1.74 4 4 0 00-6.28 4.78 4 4 0 005.18 4.88h11.24a4.5 4.5 0 001.3-8.92z"></path>
                                </svg>
                                <div class="absolute bottom-0 left-2 w-0.5 h-1.5 bg-blue-300 rounded animate-rain-1"></div>
                                <div class="absolute bottom-0 left-3.5 w-0.5 h-1.5 bg-blue-300 rounded animate-rain-2"></div>
                                <div class="absolute bottom-0 left-5 w-0.5 h-1.5 bg-blue-300 rounded animate-rain-3"></div>
                            </div>`;
                        wCat = "rain";
                    } else if (code === 95) {
                        desc = "Thunderstorm";
                        iconHtml = `
                            <div class="relative w-8 h-8 animate-float">
                                <svg class="w-6 h-6 text-slate-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.36 10.04a6 6 0 00-11.44-1.74 4 4 0 00-6.28 4.78 4 4 0 005.18 4.88h11.24a4.5 4.5 0 001.3-8.92z"></path>
                                </svg>
                                <svg class="absolute bottom-[-2px] left-3 w-3 h-4 text-yellow-400 animate-flash" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                                </svg>
                            </div>`;
                        wCat = "rain";
                    } else {
                        desc = "Overcast";
                        iconHtml = `
                            <svg class="w-7 h-7 text-slate-300 animate-float" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.36 10.04a6 6 0 00-11.44-1.74 4 4 0 00-6.28 4.78 4 4 0 005.18 4.88h11.24a4.5 4.5 0 001.3-8.92z"></path>
                            </svg>`;
                        wCat = "cloudy";
                    }
                    
                    if (temp >= 32) {
                        wCat = "hot";
                    } else if (temp < 18) {
                        wCat = "cold";
                    }
                    
                    if (temp >= 38) {
                        warningText = "🔥 Heat Alert: Stay Cool";
                        isWarning = true;
                    } else if (code === 95) {
                        warningText = "⚡ Storm warning: Indoors";
                        isWarning = true;
                    } else if (prec >= 5) {
                        warningText = "🌧️ Heavy Rain: Umbrella";
                        isWarning = true;
                    } else if (wind >= 30) {
                        warningText = "💨 Gale Warning";
                        isWarning = true;
                    } else {
                        warningText = "🌤️ perfect weather";
                    }
                    
                    document.getElementById('weather-loading').classList.add('hidden');
                    const wInfo = document.getElementById('weather-info');
                    wInfo.classList.remove('hidden');
                    wInfo.classList.add('flex');
                    
                    document.getElementById('weather-icon-container').innerHTML = iconHtml;
                    document.getElementById('weather-temp').innerText = `${temp}°C`;
                    document.getElementById('weather-desc').innerText = desc;
                    
                    const warnEl = document.getElementById('weather-warning');
                    const warnTextEl = document.getElementById('warning-text');
                    warnTextEl.innerText = warningText;
                    warnEl.classList.remove('hidden');
                    warnEl.classList.add('flex');
                    if (isWarning) {
                        warnEl.style.backgroundColor = "rgba(239, 68, 68, 0.15)";
                        warnEl.style.color = "#f87171";
                        warnEl.style.borderColor = "rgba(239, 68, 68, 0.25)";
                        warnEl.classList.add('animate-pulse');
                        warnEl.classList.remove('animate-pulse-slow');
                    } else {
                        warnEl.style.backgroundColor = "rgba(59, 130, 246, 0.1)";
                        warnEl.style.color = "#93c5fd";
                        warnEl.style.borderColor = "rgba(59, 130, 246, 0.15)";
                        warnEl.classList.remove('animate-pulse');
                        warnEl.classList.add('animate-pulse-slow');
                    }
                    
                    // Inject witty greeting twist!
                    setWittyGreeting(wCat);
                })
                .catch(err => {
                    console.error("Weather fetch failed:", err);
                    document.getElementById('weather-loading').innerHTML = `<span class="text-slate-500">Weather unavailable</span>`;
                });
        }

        // HTML5 Geolocation triggering
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    fetchWeather(pos.coords.latitude, pos.coords.longitude);
                },
                err => {
                    console.warn("Geolocation denied/failed. Fallback to Delhi.", err);
                    fetchWeather(28.61, 77.20);
                },
                { timeout: 7000 }
            );
        } else {
            fetchWeather(28.61, 77.20);
        }
    });
</script>
@endsection

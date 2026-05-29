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

    /* Solid Box Sections matching the JPEG exactly */
    .glass-card {
        background-color: #231b80 !important;
        border: 1px solid rgba(59, 130, 246, 0.15) !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
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
        .custom-main-content {
            margin-left: 0;
        }
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
                    ['icon' => 'fa-solid fa-briefcase', 'label' => 'Requirements', 'route' => route('client.jobs.index'), 'active' => false],
                    ['icon' => 'fa-solid fa-user-group', 'label' => 'Candidates', 'route' => route('client.vendors.browse'), 'active' => false],
                    ['icon' => 'fa-solid fa-file-lines', 'label' => 'Applications', 'route' => route('client.applications.index'), 'active' => false],
                    ['icon' => 'fa-solid fa-video', 'label' => 'Interviews', 'route' => route('client.interviews.calendar'), 'active' => false],
                    ['icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Replacements', 'route' => route('client.applications.index', ['joined_status' => 'Left']), 'active' => false],
                    ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Invoices & Billing', 'route' => route('client.billing'), 'active' => false],
                    ['icon' => 'fa-solid fa-message', 'label' => 'Messages', 'route' => '#', 'active' => false, 'badge' => '12'],
                    ['icon' => 'fa-solid fa-gear', 'label' => 'Settings', 'route' => route('client.profile.company'), 'active' => false],
                    ['icon' => 'fa-solid fa-circle-question', 'label' => 'Help & Support', 'route' => '#', 'active' => false],
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
            <div class="flex items-center justify-between gap-3 p-1 rounded-xl hover:bg-white/5 transition duration-200">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold uppercase shrink-0">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-xs text-white truncate">{{ Auth::user()->name }}</h4>
                        <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Client</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0 shrink-0">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-red-400 p-1.5 transition">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>
                </form>
            </div>
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
                {{-- Date Display --}}
                <div class="text-right hidden md:block">
                    <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">System Calendar</p>
                    <p class="text-xs font-semibold text-white mt-0.5">{{ date('l, M j, Y') }}</p>
                </div>

                {{-- Notification bell --}}
                <div class="relative">
                    <button class="p-2.5 bg-slate-900/60 hover:bg-slate-900 text-slate-400 hover:text-white rounded-lg border border-white/5 transition">
                        <i class="fa-solid fa-bell"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                    </button>
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
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Welcome back, {{ Auth::user()->name }} 👋</h1>
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
                
                {{-- Metric Card 1: Open Requirements (Indigo) --}}
                <a href="{{ route('client.jobs.index') }}" class="metric-card-indigo p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-blue-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Open Requirements</p>
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
                    $pulseSolid = [
                        'blue'    => '#3b82f6',
                        'indigo'  => '#6366f1',
                        'emerald' => '#10b981',
                        'amber'   => '#f59e0b',
                        'rose'    => '#f43f5e',
                    ];
                @endphp
                <div class="grid grid-cols-5 gap-2.5">
                    @foreach($dailyPulse ?? [] as $pulse)
                        @php $sc = $pulseSolid[$pulse['color']] ?? '#3b82f6'; @endphp
                        <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-black/20 border border-white/5 hover:border-white/15 transition">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $sc }};">
                                <i class="fa-solid {{ $pulse['icon'] }} text-white text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-extrabold text-white leading-none">{{ $pulse['value'] }}</div>
                                <div class="text-[9px] text-slate-400 uppercase font-bold tracking-wide leading-tight mt-1 truncate">{{ $pulse['label'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Submission Trend — real SVG line chart --}}
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
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Submission Trend · Last 7 Days</p>
                        <span class="text-[10px] font-bold text-blue-400">{{ $trend7Total }} this week</span>
                    </div>
                    <div class="h-28 bg-black/20 rounded-xl border border-white/5 relative px-2 pt-2">
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
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#0b1020" stroke="#60a5fa" stroke-width="2"></circle>
                            @endforeach
                        </svg>
                        <div class="flex justify-between text-[9px] text-slate-500 font-bold uppercase px-1 mt-1">
                            @foreach($pts as $p)
                                <span class="{{ $p['l'] === 'Today' ? 'text-blue-300' : '' }}">{{ $p['l'] }}</span>
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
                        // Real funnel from controller; each band narrows toward the bottom
                        $funnelData = $funnel ?? [];
                        $funnelTop  = max(1, collect($funnelData)->max('count') ?? 0);
                        // Width steps so the cone always tapers even when counts are equal/zero
                        $funnelWidths = [100, 84, 68, 52, 36];
                        $funnelGrad = [
                            'linear-gradient(135deg,#3b82f6,#2563eb)', // Submitted  - blue
                            'linear-gradient(135deg,#06b6d4,#0891b2)', // Shortlisted- cyan
                            'linear-gradient(135deg,#10b981,#059669)', // Interview  - emerald
                            'linear-gradient(135deg,#14b8a6,#0d9488)', // Offered    - teal
                            'linear-gradient(135deg,#f59e0b,#ea580c)', // Joined     - amber/orange
                        ];
                    @endphp

                    <div class="flex flex-col items-center pt-1 pb-1">
                        @foreach($funnelData as $i => $stg)
                            @php
                                $w = $funnelWidths[$i] ?? 30;
                                $isLast = $i === count($funnelData) - 1;
                            @endphp
                            <div class="relative flex items-center justify-center text-white font-bold transition-all duration-300 hover:brightness-110 group"
                                 style="width: {{ $w }}%; min-width: 120px; height: 46px; background: {{ $funnelGrad[$i] ?? $funnelGrad[4] }};
                                        clip-path: polygon(6% 0, 94% 0, 86% 100%, 14% 100%);
                                        margin-top: {{ $i === 0 ? '0' : '-1px' }}; box-shadow: 0 4px 12px -4px rgba(0,0,0,.4);">
                                <span class="text-[11px] uppercase tracking-wide font-extrabold drop-shadow">{{ $stg['label'] }}</span>
                                {{-- count chip to the right of the band --}}
                                <span class="absolute -right-2 translate-x-full text-sm font-black text-white whitespace-nowrap"
                                      style="right: -0.5rem;">{{ $stg['count'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- conversion footnote --}}
                    @php
                        $subVal = $funnelData[0]['count'] ?? 0;
                        $joinVal = $funnelData[count($funnelData)-1]['count'] ?? 0;
                        $conv = $subVal > 0 ? round($joinVal / $subVal * 100, 1) : 0;
                    @endphp
                    <div class="mt-2 text-center text-[10px] text-slate-400 font-semibold">
                        Overall conversion: <span class="text-emerald-400 font-bold">{{ $conv }}%</span>
                        ({{ $joinVal }} joined of {{ $subVal }} submitted)
                    </div>
                </div>

                {{-- Right Widget: Recruitment Performance (real ratios) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Recruitment Performance</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Conversion ratios across your pipeline</p>
                    </div>

                    @php
                        $perf = $performance ?? [];
                        $perfRows = [
                            ['label' => 'Selection Ratio',  'value' => $perf['selection_ratio'] ?? 0, 'color' => 'emerald', 'hex' => '#10b981'],
                            ['label' => 'Interview Rate',   'value' => $perf['interview_rate'] ?? 0,  'color' => 'blue',    'hex' => '#3b82f6'],
                            ['label' => 'Client Response',  'value' => $perf['response_rate'] ?? 0,   'color' => 'amber',   'hex' => '#f59e0b'],
                            ['label' => 'Fill Rate',        'value' => $perf['fill_rate'] ?? 0,       'color' => 'purple',  'hex' => '#a855f7'],
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

                    @php $joinedCount = collect($funnel ?? [])->firstWhere('label', 'Joined')['count'] ?? 0; @endphp
                    <div class="p-3 bg-blue-600/10 border border-blue-500/20 rounded-xl text-center text-[10px] font-extrabold text-blue-400 uppercase tracking-wide">
                        {{ $joinedCount }} of {{ $totalApplicants ?? 0 }} approved candidates hired
                    </div>
                </div>
            </div>

            {{-- Row 4: Quick Workspace --}}
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Workspace
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    {{-- Workspace 1: Total Submissions --}}
                    @php $totalSubmitted = collect($funnel ?? [])->firstWhere('label', 'Submitted')['count'] ?? 0; @endphp
                    <div class="glass-card rounded-2xl p-5 workspace-card-blue flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Total Submissions</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ number_format($totalSubmitted) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Candidates sent to your jobs</p>
                        </div>
                        <a href="{{ route('client.vendors.browse') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Browse Vendors
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
                                        <p class="text-[9px] text-slate-500 mt-0.5">{{ $job->location ?? '—' }} · {{ $job->job_type ?? '—' }}</p>
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

                    @php
                        $perf = $performance ?? [];
                        // Overall score = average of the 4 ratios
                        $overall = (int) round(collect([
                            $perf['selection_ratio'] ?? 0,
                            $perf['interview_rate'] ?? 0,
                            $perf['response_rate'] ?? 0,
                            $perf['fill_rate'] ?? 0,
                        ])->avg());
                        $circumference = 251.2;
                        $offset = $circumference - ($circumference * min($overall, 100) / 100);
                        $rating = $overall >= 75 ? 'Excellent' : ($overall >= 50 ? 'Good' : ($overall >= 25 ? 'Fair' : 'Getting Started'));
                        $perfBars = [
                            ['label' => 'Selection Ratio', 'value' => $perf['selection_ratio'] ?? 0, 'hex' => '#10b981'],
                            ['label' => 'Client Response', 'value' => $perf['response_rate'] ?? 0,   'hex' => '#3b82f6'],
                            ['label' => 'Interview Rate',  'value' => $perf['interview_rate'] ?? 0,  'hex' => '#f59e0b'],
                            ['label' => 'Fill Rate',       'value' => $perf['fill_rate'] ?? 0,       'hex' => '#a855f7'],
                        ];
                    @endphp

                    {{-- Circular progress ring (real overall score) --}}
                    <div class="flex items-center justify-center gap-6 pt-2">
                        <div class="relative w-24 h-24 flex items-center justify-center shrink-0 shadow-lg shadow-cyan-500/10 rounded-full">
                            <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="40" fill="transparent" stroke="#111827" stroke-width="7"></circle>
                                <circle cx="48" cy="48" r="40" fill="transparent" stroke="#06b6d4" stroke-width="7"
                                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round"></circle>
                            </svg>
                            <div class="text-center relative z-10">
                                <span class="text-xl font-black text-white block">{{ $overall }}%</span>
                                <span class="text-[8px] text-cyan-400 uppercase font-bold tracking-wider">{{ $rating }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-300">Your hiring health score</p>
                            <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">Average across selection, response, interview &amp; fill rates from your live pipeline.</p>
                        </div>
                    </div>

                    {{-- Progress Bars (real) --}}
                    <div class="space-y-2 pt-1">
                        @foreach($perfBars as $bar)
                            <div class="space-y-1">
                                <div class="flex justify-between text-[9px] font-bold text-slate-400 uppercase">
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

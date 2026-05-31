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
        background-color: rgba(7, 13, 36, 0.95) !important;
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
        .custom-main-content {
            margin-left: 0;
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

    /* PREMIUM DESIGN SYSTEM CLASSES FOR UNIFIED CLIENT VIEWS */
    /* 1. Premium cards - deep gradient, neon accent top-border, glass hover scale */
    .premium-card {
        background: linear-gradient(135deg, rgba(16, 28, 79, 0.75) 0%, rgba(10, 18, 56, 0.85) 100%) !important;
        border: 1px solid rgba(59, 130, 246, 0.25) !important;
        border-radius: 1.5rem !important;
        backdrop-filter: blur(16px) !important;
        -webkit-backdrop-filter: blur(16px) !important;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.1) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
    }
    .premium-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #06b6d4, #3b82f6, #6366f1);
        opacity: 0.4;
        transition: opacity 0.3s;
    }
    .premium-card:hover::before {
        opacity: 1;
    }
    .premium-card:hover {
        border-color: rgba(6, 182, 212, 0.45) !important;
        box-shadow: 0 30px 50px -10px rgba(0, 0, 0, 0.6), 0 0 20px rgba(6, 182, 212, 0.15), inset 0 1px 1px rgba(255, 255, 255, 0.15) !important;
        transform: translateY(-2px);
    }

    .profile-card {
        background-color: #0b1437 !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 1.25rem !important;
        padding: 1.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .profile-card:hover {
        border-color: rgba(34, 211, 238, 0.3) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 0 15px rgba(6, 182, 212, 0.1) !important;
    }

    /* 2. Premium inputs and forms - inset shadow dark slate fields, cyan border focus */
    .premium-form input[type="text"],
    .premium-form input[type="email"],
    .premium-form input[type="url"],
    .premium-form input[type="number"],
    .premium-form input[type="date"],
    .premium-form input[type="tel"],
    .premium-form input[type="password"],
    .premium-form input[type="search"],
    .premium-form select,
    .premium-form textarea {
        background-color: rgba(3, 7, 26, 0.75) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 0.75rem !important;
        padding: 0.65rem 0.85rem !important;
        font-size: 0.875rem !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
    }
    .premium-form input[type="text"]:focus,
    .premium-form input[type="email"]:focus,
    .premium-form input[type="url"]:focus,
    .premium-form input[type="number"]:focus,
    .premium-form input[type="date"]:focus,
    .premium-form input[type="tel"]:focus,
    .premium-form input[type="password"]:focus,
    .premium-form input[type="search"]:focus,
    .premium-form select:focus,
    .premium-form textarea:focus {
        outline: none !important;
        border-color: #06b6d4 !important;
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        background-color: rgba(3, 7, 26, 0.9) !important;
    }
    .premium-form input::placeholder,
    .premium-form textarea::placeholder {
        color: rgba(148, 163, 184, 0.5) !important;
    }
    .premium-form input[type="date"] { color-scheme: dark; }
    .premium-form input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1) brightness(1.5); }
    .premium-form select option { background: #0b1437; color: #ffffff; }

    /* File select uploads */
    .premium-form input[type="file"] {
        background: transparent !important;
        border: none !important;
        color: #94a3b8 !important;
        padding: 0 !important;
        height: auto !important;
        box-shadow: none !important;
    }
    .premium-form input[type="file"]::file-selector-button {
        background: #0891b2 !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        color: #ffffff !important;
        border-radius: 0.5rem !important;
        padding: 0.45rem 0.9rem !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        margin-right: 0.75rem !important;
    }
    .premium-form input[type="file"]::file-selector-button:hover {
        background: #06b6d4 !important;
        box-shadow: 0 0 12px rgba(6,182,212,0.3) !important;
    }
    .premium-form input[type="file"]::-webkit-file-upload-button {
        background: #0891b2 !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        color: #ffffff !important;
        border-radius: 0.5rem !important;
        padding: 0.45rem 0.9rem !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
        margin-right: 0.75rem !important;
    }
    .premium-form input[type="file"]::-webkit-file-upload-button:hover {
        background: #06b6d4 !important;
        box-shadow: 0 0 12px rgba(6,182,212,0.3) !important;
    }

    /* Labels - bold soft-slate uppercase headers */
    .premium-form label:not(.btn-label) {
        color: #94a3b8 !important;
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        margin-bottom: 0.45rem !important;
        display: block !important;
    }

    /* 3. Premium tables - rounded border layout, uppercase header rows, hover states */
    .premium-table-container {
        background: rgba(7, 13, 36, 0.95) !important;
        border: 1px solid rgba(59, 130, 246, 0.15) !important;
        border-radius: 1.5rem !important;
        overflow: hidden !important;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.1) !important;
    }
    .premium-table {
        min-w-full: 100%;
        text-align: left;
        font-size: 0.875rem;
    }
    .premium-table thead {
        background: rgba(6, 18, 59, 0.85) !important;
        border-bottom: 1px solid rgba(59, 130, 246, 0.12) !important;
    }
    .premium-table thead th {
        color: #94a3b8 !important;
        font-size: 0.725rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        padding: 1.15rem 1.5rem !important;
    }
    .premium-table tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        transition: all 0.22s ease !important;
    }
    .premium-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
    .premium-table tbody td {
        padding: 1.15rem 1.5rem !important;
        vertical-align: middle !important;
        color: #f1f5f9 !important;
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
                    ['icon' => 'fa-solid fa-chart-line', 'label' => 'Dashboard', 'route' => route('client.dashboard'), 'active' => request()->routeIs('client.dashboard')],
                    ['icon' => 'fa-solid fa-briefcase', 'label' => 'My Jobs', 'route' => route('client.jobs.index'), 'active' => request()->is('client/jobs*')],
                    ['icon' => 'fa-solid fa-file-lines', 'label' => 'Applications', 'route' => route('client.applications.index'), 'active' => request()->is('client/applications*') && !request()->has('joined_status')],
                    ['icon' => 'fa-solid fa-video', 'label' => 'Interviews', 'route' => route('client.interviews.calendar'), 'active' => request()->is('client/interviews*')],
                    ['icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Replacements', 'route' => route('client.applications.index', ['joined_status' => 'Left']), 'active' => request()->is('client/applications*') && request('joined_status') === 'Left'],
                    ['icon' => 'fa-solid fa-handshake', 'label' => 'Sourcing Partners', 'route' => route('client.vendors.browse'), 'active' => request()->is('client/vendors*') || request()->is('client/vendor-performance*')],
                    ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Invoices & Billing', 'route' => route('client.billing'), 'active' => request()->is('client/billing*')],
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
            @yield('client_content')

            {{-- Footer --}}
            <div class="mt-8 pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-500 text-xs">
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

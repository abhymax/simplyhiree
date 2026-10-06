<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SimplyHiree') }}</title>

        <!-- FAVICON -->
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%' stop-color='%232563eb' /><stop offset='100%' stop-color='%234f46e5' /></linearGradient></defs><rect width='100' height='100' rx='20' fill='url(%23g)' /><text x='50' y='65' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='Roboto'>SH</text></svg>">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

        @vite(['resources/css/app.css'])
        @livewireStyles

        <style>
            body { font-family: 'Outfit', sans-serif; }
            [x-cloak] { display: none !important; }
            
            /* Glassmorphism Classes */
            .glass-panel {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            /* Rich-text job description rendering */
            .job-desc-html h2 { font-size: 1.25rem; font-weight: 700; margin: 1rem 0 0.5rem; }
            .job-desc-html h3 { font-size: 1.1rem; font-weight: 700; margin: 0.9rem 0 0.4rem; }
            .job-desc-html p { margin: 0 0 0.75rem; }
            .job-desc-html ul { list-style: disc; padding-left: 1.5rem; margin: 0.5rem 0 0.75rem; }
            .job-desc-html ol { list-style: decimal; padding-left: 1.5rem; margin: 0.5rem 0 0.75rem; }
            .job-desc-html li { margin-bottom: 0.25rem; }
            .job-desc-html strong, .job-desc-html b { font-weight: 700; }
            .job-desc-html em, .job-desc-html i { font-style: italic; }
            .job-desc-html u { text-decoration: underline; }
            .job-desc-html a { color: #67e8f9; text-decoration: underline; }
            .job-desc-html blockquote { border-left: 3px solid rgba(255,255,255,0.25); padding-left: 0.85rem; margin: 0.5rem 0; opacity: 0.9; }

            /* Admin sidebar: reserve 256px on desktop so every admin page
               (including ones that use -mx-* to breakout) stays clear of
               the fixed left sidebar. Body bg matches the sidebar so
               sub-pixel rounding never shows a white seam between them. */
            body.has-admin-sidebar {
                padding-top: 3.5rem; /* mobile topbar */
                background-color: #0f172a; /* slate-900 — matches sidebar */
            }
            @media (min-width: 1024px) {
                body.has-admin-sidebar { padding-top: 76px; padding-left: 16rem; }
                .admin-mobile-only { display: none !important; }
                .admin-sidebar-aside { transform: translateX(0) !important; }
            }
            @media (max-width: 1023px) {
                .admin-desktop-only { display: none !important; }
            }

            /* Shared Superadmin visual language. Keeps legacy and newer pages
               cohesive without changing their workflow-specific markup. */
            .has-admin-sidebar main {
                position: relative;
                isolation: isolate;
                overflow: hidden;
                background:
                    radial-gradient(circle at 7% 4%, rgba(245, 158, 11, .10), transparent 23rem),
                    linear-gradient(118deg, #111827 0%, #172554 48%, #1e3a8a 100%);
                background-attachment: fixed;
            }
            .admin-global-header { min-height: 76px; }
            .admin-global-weather { min-width: 300px; padding: .72rem 1rem; }
            .admin-global-weather-time { font-size: 1rem; letter-spacing: .04em; }
            /* Admin pages historically used negative horizontal margins to
               break out of the public layout. With the fixed sidebar those
               margins erase the content gutter, so normalize only the direct
               page wrapper and retain each page's own responsive padding. */
            .has-admin-sidebar main > div:not(.admin-ambient) {
                margin-left: 0 !important;
                margin-right: 0 !important;
                min-width: 0;
                min-height: 100vh;
                padding-top: 2.5rem !important;
                padding-bottom: 3rem !important;
            }
            .has-admin-sidebar main > div[class*="bg-slate-950"] {
                background-color: transparent !important;
            }
            @media (min-width: 1024px) {
                .has-admin-sidebar main > div:not(.admin-ambient) {
                    padding-left: max(2rem, env(safe-area-inset-left)) !important;
                    padding-right: 2rem !important;
                }
            }
            .admin-ambient {
                position: fixed;
                z-index: -1;
                pointer-events: none;
                border-radius: 9999px;
                filter: blur(100px);
            }
            .admin-ambient-amber {
                top: -5rem; left: 15rem; width: 25rem; height: 25rem;
                background: rgba(245, 158, 11, .25);
                animation: admin-breathe 4.2s ease-in-out infinite;
            }
            .admin-ambient-cyan {
                right: -10rem; bottom: 4rem; width: 26rem; height: 26rem;
                background: rgba(6, 182, 212, .09);
            }
            @keyframes admin-breathe {
                0%, 100% { opacity: .34; transform: scale(.92); }
                50% { opacity: .72; transform: scale(1.06); }
            }
            .has-admin-sidebar main section,
            .has-admin-sidebar main .glass-panel {
                transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
            }
            .has-admin-sidebar main section {
                border-radius: 1rem !important;
                border-color: rgba(148, 163, 184, .16) !important;
                background-color: rgba(15, 23, 42, .76) !important;
                backdrop-filter: blur(14px);
                box-shadow: 0 18px 50px rgba(2, 6, 23, .18);
            }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+1) { background: linear-gradient(135deg, rgba(8,145,178,.18), rgba(15,23,42,.88)) !important; }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+2) { background: linear-gradient(135deg, rgba(5,150,105,.17), rgba(15,23,42,.88)) !important; }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+3) { background: linear-gradient(135deg, rgba(217,119,6,.16), rgba(15,23,42,.88)) !important; }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+4) { background: linear-gradient(135deg, rgba(124,58,237,.17), rgba(15,23,42,.88)) !important; }
            .has-admin-sidebar main section:hover {
                border-color: rgba(103, 232, 249, .2);
                box-shadow: 0 14px 38px rgba(2, 6, 23, .24);
            }
            .has-admin-sidebar main .grid > [class*="border"] {
                position: relative;
                overflow: hidden;
            }
            .has-admin-sidebar main .grid > [class*="border"]::before {
                content: "";
                position: absolute;
                inset: 0 0 auto;
                height: 2px;
                background: #38bdf8;
                opacity: .72;
            }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+2)::before { background: #34d399; }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+3)::before { background: #fbbf24; }
            .has-admin-sidebar main .grid > [class*="border"]:nth-child(4n+4)::before { background: #a78bfa; }
            .has-admin-sidebar main .grid > [class*="border"]:hover {
                transform: translateY(-2px);
            }
            .has-admin-sidebar main table {
                border-collapse: separate;
                border-spacing: 0 .55rem;
            }
            .has-admin-sidebar main thead {
                background: rgba(8, 18, 40, .92);
                backdrop-filter: blur(10px);
            }
            .has-admin-sidebar main thead th {
                color: #67e8f9 !important;
                font-size: .72rem;
                font-weight: 800;
                letter-spacing: .035em;
                text-transform: uppercase;
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            .has-admin-sidebar main tbody tr {
                background: rgba(23, 37, 84, .82);
                transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
            }
            .has-admin-sidebar main tbody tr:hover {
                background-color: rgba(30, 58, 138, .66);
                box-shadow: inset 3px 0 0 rgba(34, 211, 238, .42);
                transform: translateY(-1px);
            }
            .has-admin-sidebar main tbody td {
                border-top: 1px solid rgba(148, 163, 184, .09);
                border-bottom: 1px solid rgba(148, 163, 184, .09);
                padding-top: 1.1rem !important;
                padding-bottom: 1.1rem !important;
            }
            .has-admin-sidebar main tbody td:first-child {
                border-left: 1px solid rgba(148, 163, 184, .09);
                border-radius: .75rem 0 0 .75rem;
            }
            .has-admin-sidebar main tbody td:last-child {
                border-right: 1px solid rgba(148, 163, 184, .09);
                border-radius: 0 .75rem .75rem 0;
            }
            .has-admin-sidebar main h1 {
                color: #fff;
                font-size: clamp(2rem, 3vw, 2.75rem);
                line-height: 1.05;
                font-weight: 800;
                letter-spacing: 0;
                text-shadow: 0 8px 28px rgba(2, 6, 23, .28);
            }
            .has-admin-sidebar main h2,
            .has-admin-sidebar main h3 { letter-spacing: 0; }
            .has-admin-sidebar main form,
            .has-admin-sidebar main details,
            .has-admin-sidebar main [class*="rounded"] { scroll-margin-top: 1rem; }
            .has-admin-sidebar main input:not([type="checkbox"]):not([type="radio"]),
            .has-admin-sidebar main select,
            .has-admin-sidebar main textarea {
                transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
            }
            .has-admin-sidebar main input:focus,
            .has-admin-sidebar main select:focus,
            .has-admin-sidebar main textarea:focus {
                border-color: rgba(34, 211, 238, .65) !important;
                box-shadow: 0 0 0 3px rgba(34, 211, 238, .10) !important;
            }
            .has-admin-sidebar main button,
            .has-admin-sidebar main a[class*="bg-"] {
                transition: color .18s ease, background-color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
            }
            .has-admin-sidebar main button:hover,
            .has-admin-sidebar main a[class*="bg-"]:hover {
                transform: translateY(-1px);
            }
            .has-admin-sidebar main button:active,
            .has-admin-sidebar main a[class*="bg-"]:active {
                transform: translateY(0);
            }
            @media (prefers-reduced-motion: reduce) {
                .admin-ambient-amber { animation: none; }
                .has-admin-sidebar main *, .has-admin-sidebar main *::before, .has-admin-sidebar main *::after {
                    scroll-behavior: auto !important;
                    transition-duration: .01ms !important;
                    animation-duration: .01ms !important;
                    animation-iteration-count: 1 !important;
                }
            }
            .has-partner-workspace, .has-candidate-workspace { background: #0b1b45; color: #e5efff; }
            .has-partner-workspace .glass-nav, .has-candidate-workspace .glass-nav { background: rgba(10, 26, 63, .9) !important; border-color: rgba(125,211,252,.16) !important; }
            .has-partner-workspace .glass-nav *, .has-candidate-workspace .glass-nav * { color: #e5efff !important; }
            .has-partner-workspace .glass-nav a:hover, .has-candidate-workspace .glass-nav a:hover { background: rgba(59,130,246,.18); color: #fff !important; border-radius: .65rem; }
            .has-partner-workspace main, .has-candidate-workspace main { background: linear-gradient(135deg,#0c1f4c,#173a7b 55%,#152c65); min-height: 100vh; }
            .has-partner-workspace main [class*="rounded"], .has-candidate-workspace main [class*="rounded"] { transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
            .has-partner-workspace main [class*="rounded"]:hover, .has-candidate-workspace main [class*="rounded"]:hover { box-shadow: 0 16px 34px rgba(2,8,29,.28); }

            /* Partner workspace: use the same stable, vertical workspace shell
               as the client portal. Individual partner screens only own their
               workflow content; navigation and the surrounding visual system
               live here, preventing page-by-page drift. */
            body.has-partner-workspace { background: #0b1b45; padding-top: 76px; }
            .partner-sidebar {
                position: fixed; inset: 0 auto 0 0; z-index: 120; width: 272px;
                display: flex; flex-direction: column; background: #10275a;
                border-right: 1px solid rgba(125, 211, 252, .14);
                box-shadow: 18px 0 42px rgba(2, 8, 29, .18);
                transition: transform .28s cubic-bezier(.4,0,.2,1);
            }
            .partner-sidebar-logo { height: 76px; display: flex; align-items: center; gap: 11px; padding: 0 22px; border-bottom: 1px solid rgba(125,211,252,.12); color: #fff; font-size: 1.25rem; font-weight: 800; }
            .partner-sidebar-nav { flex: 1; overflow-y: auto; padding: 18px 11px; display: flex; flex-direction: column; gap: 4px; }
            .partner-sidebar-label { margin: 12px 12px 5px; color: #93c5fd; font-size: 10px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
            .partner-sidebar-link { position: relative; display: flex; align-items: center; gap: 12px; min-height: 43px; overflow: hidden; border: 1px solid transparent; border-radius: 10px; padding: 10px 12px; color: #f8fafc; font-size: 13px; font-weight: 650; text-decoration: none; transition: transform .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease; }
            .partner-sidebar-link i { width: 18px; text-align: center; color: #dbeafe; font-size: 15px; transition: color .2s ease, transform .2s ease; }
            .partner-sidebar-link:hover { transform: translateX(3px); color: #fff; background: rgba(96,165,250,.18); border-color: rgba(125,211,252,.28); box-shadow: 0 8px 18px rgba(2,6,23,.2); }
            .partner-sidebar-link:hover i { color: #67e8f9; transform: scale(1.08); }
            .partner-sidebar-link.active { color: #fff; font-weight: 800; background: linear-gradient(105deg,#2563eb,#5b3fee); border-color: rgba(196,181,253,.34); box-shadow: 0 12px 24px rgba(37,99,235,.28); }
            .partner-sidebar-link.active i { color: #fff; }
            .partner-sidebar-link::after { content: ''; position: absolute; top: 0; bottom: 0; left: -42%; width: 28%; opacity: 0; transform: skewX(-20deg); background: linear-gradient(90deg,transparent,rgba(255,255,255,.38),transparent); }
            .partner-sidebar-link:hover::after, .partner-sidebar-link.active::after { animation: partner-nav-sweep 2.8s ease-in-out infinite; }
            @keyframes partner-nav-sweep { 0%,64%{left:-42%;opacity:0} 73%{opacity:1} 100%{left:120%;opacity:0} }
            .partner-sidebar-footer { border-top: 1px solid rgba(125,211,252,.12); padding: 14px 12px; background: rgba(3,10,33,.28); }
            .partner-user-card { display:flex; align-items:center; gap:10px; padding:9px 8px; color:#fff; text-decoration:none; border-radius:10px; transition:background-color .2s ease; }
            .partner-user-card:hover { background: rgba(255,255,255,.07); }
            .partner-avatar { display:inline-flex; width:34px; height:34px; align-items:center; justify-content:center; flex:none; border-radius:999px; background:linear-gradient(135deg,#60a5fa,#7c3aed); color:#fff; font-size:13px; font-weight:800; box-shadow:0 0 0 2px rgba(255,255,255,.12); }
            .partner-global-header { position: fixed; inset: 0 0 auto 272px; z-index: 110; display: flex; height: 76px; align-items: center; gap: 16px; padding: 0 28px; border-bottom: 1px solid rgba(125,211,252,.12); background: rgba(11,27,69,.76); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
            .partner-global-search { min-width: 260px; width: min(44vw, 620px); }
            .partner-global-search input { width: 100%; border: 1px solid rgba(147,197,253,.28); border-radius: 12px; background: rgba(4,18,53,.66); padding: 11px 14px 11px 39px; color: #fff; font-size: 13px; outline: none; transition: border-color .2s ease, box-shadow .2s ease; }
            .partner-global-search input::placeholder { color: #bfdbfe; opacity: .75; }
            .partner-global-search input:focus { border-color: rgba(103,232,249,.72); box-shadow: 0 0 0 3px rgba(34,211,238,.1); }
            .partner-workspace-weather { margin-left: auto; display:flex; align-items:center; gap:12px; min-width:260px; border:1px solid rgba(148,163,184,.18); border-radius:15px; background:rgba(4,18,53,.56); padding:9px 13px; color:#fff; }
            .partner-workspace-weather .time { font-size:14px; font-weight:800; letter-spacing:.04em; }
            .partner-workspace-weather .date { color:#94a3b8; font-size:9px; font-weight:700; text-transform:uppercase; }
            .has-partner-workspace .glass-nav { display: none !important; }
            .has-partner-workspace main { position:relative; isolation:isolate; min-height:calc(100vh - 76px); margin-left:272px; overflow:hidden; background:radial-gradient(circle at 8% 4%,rgba(251,191,36,.15),transparent 24rem),radial-gradient(circle at 94% 88%,rgba(14,165,233,.12),transparent 26rem),linear-gradient(135deg,#10224d 0%,#183a7a 54%,#162d66 100%) !important; }
            .has-partner-workspace main::before { content:''; position:fixed; z-index:-1; top:8rem; left:17rem; width:23rem; height:23rem; border-radius:999px; filter:blur(92px); background:rgba(245,158,11,.17); animation:partner-ambient-breathe 4.8s ease-in-out infinite; pointer-events:none; }
            @keyframes partner-ambient-breathe { 0%,100%{opacity:.28;transform:scale(.92)} 50%{opacity:.64;transform:scale(1.05)} }
            .has-partner-workspace main > div.min-h-screen { min-height:calc(100vh - 76px) !important; margin:0 !important; padding:2.5rem max(1.5rem,env(safe-area-inset-right)) 3.25rem max(1.5rem,env(safe-area-inset-left)) !important; background:transparent !important; }
            .has-partner-workspace main > div.min-h-screen > .absolute { display:none; }
            .has-partner-workspace main .bg-white { background-color:rgba(7,18,48,.78) !important; color:#e5efff !important; border-color:rgba(147,197,253,.2) !important; }
            .has-partner-workspace main input:not([type="checkbox"]):not([type="radio"]), .has-partner-workspace main select, .has-partner-workspace main textarea { background-color:rgba(4,18,53,.82) !important; color:#f8fafc !important; border-color:rgba(147,197,253,.28) !important; }
            .has-partner-workspace main input::placeholder, .has-partner-workspace main textarea::placeholder { color:rgba(191,219,254,.7) !important; }
            .has-partner-workspace main option { background:#0b1b45; color:#fff; }
            .has-partner-workspace main table { border-collapse:separate; border-spacing:0 .42rem; }
            .has-partner-workspace main table thead { background:rgba(5,18,51,.85) !important; }
            .has-partner-workspace main table thead th { color:#67e8f9 !important; font-size:.7rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
            .has-partner-workspace main table tbody tr { background:rgba(30,58,123,.46); transition:transform .2s ease,background-color .2s ease,box-shadow .2s ease; }
            .has-partner-workspace main table tbody tr:hover { transform:translateY(-1px); background:rgba(37,99,235,.22); box-shadow:inset 3px 0 0 rgba(34,211,238,.5); }
            .has-partner-workspace main table tbody td { border-top:1px solid rgba(148,163,184,.1); border-bottom:1px solid rgba(148,163,184,.1); }
            .has-partner-workspace main table tbody td:first-child { border-left:1px solid rgba(148,163,184,.1); border-radius:.7rem 0 0 .7rem; }
            .has-partner-workspace main table tbody td:last-child { border-right:1px solid rgba(148,163,184,.1); border-radius:0 .7rem .7rem 0; }
            .has-partner-workspace main [class*="rounded-2xl"], .has-partner-workspace main [class*="rounded-3xl"] { transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease,filter .22s ease; }
            .has-partner-workspace main [class*="rounded-2xl"]:hover, .has-partner-workspace main [class*="rounded-3xl"]:hover { box-shadow:0 18px 38px rgba(2,6,23,.28); }
            .has-partner-workspace main .bg-slate-900\/60, .has-partner-workspace main .bg-slate-900\/50, .has-partner-workspace main .bg-white\/10 { border-color:rgba(147,197,253,.18) !important; }
            .has-partner-workspace footer { margin-left:272px; border-color:rgba(147,197,253,.12); background:#0b1b45; color:#94a3b8; }
            .partner-mobile-toggle { display:none; }
            @media (max-width: 1023px) { body.has-partner-workspace{padding-top:64px}.partner-sidebar{width:272px;transform:translateX(-100%)}.partner-sidebar.is-open{transform:translateX(0)}.partner-global-header{left:0;height:64px;padding:0 15px}.partner-global-search{width:auto;min-width:0;flex:1}.partner-workspace-weather{display:none}.partner-mobile-toggle{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid rgba(147,197,253,.25);border-radius:10px;background:rgba(4,18,53,.7);color:#fff}.has-partner-workspace main{margin-left:0;min-height:calc(100vh - 64px)}.has-partner-workspace main>div.min-h-screen{min-height:calc(100vh - 64px)!important;padding:1.35rem 1rem 2rem!important}.has-partner-workspace footer{margin-left:0}.partner-sidebar-scrim{display:none;position:fixed;inset:64px 0 0;z-index:115;background:rgba(2,6,23,.54);backdrop-filter:blur(2px)}.partner-sidebar-scrim:not(.hidden){display:block}}
            @media (min-width:1024px){.partner-sidebar-scrim{display:none!important}}
            @media (prefers-reduced-motion: reduce){.partner-sidebar-link::after,.has-partner-workspace main::before{animation:none!important}.has-partner-workspace *{transition-duration:.01ms!important}}
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 @if(auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'))) has-admin-sidebar @endif @if(auth()->check() && auth()->user()->hasRole('partner')) has-partner-workspace @endif @if(auth()->check() && auth()->user()->hasRole('candidate')) has-candidate-workspace @endif">
        
        @php
            $usesSidebar = auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'));
            $usesPartnerSidebar = auth()->check() && auth()->user()->hasRole('partner');
        @endphp

        <div class="flex flex-col min-h-screen">

            @if($usesSidebar)
                @include('layouts.admin-sidebar')
                <header class="admin-global-header fixed top-0 right-0 left-0 z-40 flex items-center justify-end gap-3 border-b border-white/10 px-5 lg:left-64" style="background: linear-gradient(118deg, #111827 0%, #172554 48%, #1e3a8a 100%); background-attachment: fixed;">
                    <div class="admin-global-weather hidden sm:flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 text-xs shadow-lg">
                        <div class="border-r border-white/10 pr-3 text-right"><b id="admin-global-time" class="admin-global-weather-time text-white">--:--:--</b><div class="text-[9px] font-bold uppercase text-slate-400">{{ now()->format('D, M j, Y') }}</div></div>
                        <i id="admin-global-weather-icon" class="fa-solid fa-spinner animate-spin text-cyan-300 text-xl"></i>
                        <div><span id="admin-global-weather-temp" class="font-extrabold text-white">Loading</span><div id="admin-global-weather-desc" class="mt-0.5 text-[9px] font-bold uppercase tracking-wide text-blue-200">Fetching weather</div></div>
                    </div>
                    <livewire:notifications-bell />
                </header>
            @elseif($usesPartnerSidebar)
                @include('layouts.partner-sidebar')
                <header class="partner-global-header" x-data="{ menuOpen: false }">
                    <button type="button" class="partner-mobile-toggle" @click="$dispatch('partner-menu-toggle')" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
                    <form action="{{ route('partner.jobs') }}" method="GET" class="partner-global-search relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-blue-200/80 text-sm"></i>
                        <input name="search" value="{{ request()->routeIs('partner.jobs') ? request('search') : '' }}" placeholder="Search approved jobs..." aria-label="Search approved jobs">
                    </form>
                    <div class="partner-workspace-weather hidden lg:flex">
                        <div class="border-r border-white/10 pr-3 text-right"><div id="partner-global-time" class="time">--:--</div><div class="date">{{ now()->format('D, M j, Y') }}</div></div>
                        <i id="partner-global-weather-icon" class="fa-solid fa-spinner animate-spin text-cyan-300 text-lg"></i>
                        <div><strong id="partner-global-weather-temp">Loading</strong><div id="partner-global-weather-desc" class="date mt-0.5">Fetching weather</div></div>
                    </div>
                    <livewire:notifications-bell />
                </header>
            @else
                @include('layouts.navigation')
            @endif

            @if (isset($header))
                <header class="bg-white shadow-sm border-b border-slate-100 z-10 relative">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-grow">
                @if($usesSidebar)
                @endif
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>

            <footer class="{{ $usesSidebar ? 'bg-slate-900 border-t border-white/10 text-slate-400' : 'bg-white border-t border-slate-200 text-slate-500' }} mt-auto py-2 z-10 relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-2 text-xs">
                    <div>
                        &copy; {{ date('Y') }} <span class="font-semibold {{ $usesSidebar ? 'text-slate-200' : 'text-slate-700' }}">SimplyHiree</span>. All rights reserved.
                    </div>
                    @include('partials.google-play-link')
                    <div class="flex gap-5 font-medium">
                        <a href="{{ route('privacy') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Terms of Service</a>
                        @unless($usesPartnerSidebar)
                            <a href="{{ auth()->check() ? route('support') : route('contact') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Support</a>
                        @endunless
                    </div>
                </div>
            </footer>

        </div>

        @livewireScripts
        @if($usesSidebar || $usesPartnerSidebar)<script>
            (() => {
                const isPartner = {{ $usesPartnerSidebar ? 'true' : 'false' }};
                const time = document.getElementById(isPartner ? 'partner-global-time' : 'admin-global-time');
                const icon = document.getElementById(isPartner ? 'partner-global-weather-icon' : 'admin-global-weather-icon');
                const temp = document.getElementById(isPartner ? 'partner-global-weather-temp' : 'admin-global-weather-temp');
                const description = document.getElementById(isPartner ? 'partner-global-weather-desc' : 'admin-global-weather-desc');
                const tick = () => { if (time) time.textContent = new Date().toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true }); };
                const updateWeather = (lat, lon) => fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code`)
                    .then(response => response.json())
                    .then(data => {
                        const current = data.current;
                        if (!current) throw new Error('No weather data');
                        const code = current.weather_code;
                        const rainy = (code >= 51 && code <= 67) || (code >= 80 && code <= 82) || code === 95;
                        const cloudy = (code >= 1 && code <= 3) || (code >= 45 && code <= 48);
                        const condition = code === 0 ? 'Clear sky' : rainy ? 'Rain showers' : cloudy ? 'Cloudy' : 'Partly cloudy';
                        if (temp) temp.textContent = `${Math.round(current.temperature_2m)}°C`;
                        if (description) description.textContent = condition;
                        if (icon) icon.className = `fa-solid ${code === 0 ? 'fa-sun text-amber-300' : rainy ? 'fa-cloud-rain text-blue-300' : cloudy ? 'fa-cloud text-slate-200' : 'fa-cloud-sun text-amber-200'} text-xl`;
                    })
                    .catch(() => { if (temp) temp.textContent = 'Weather unavailable'; if (description) description.textContent = 'Try again shortly'; });
                tick(); setInterval(tick, 1000);
                let coordinates = { lat: 28.6139, lon: 77.2090 };
                const refresh = () => updateWeather(coordinates.lat, coordinates.lon);
                refresh();
                if (navigator.geolocation) navigator.geolocation.getCurrentPosition(position => { coordinates = { lat: position.coords.latitude, lon: position.coords.longitude }; refresh(); }, () => {}, { timeout: 5000, maximumAge: 600000 });
                setInterval(refresh, 600000);
            })();
        </script>@endif
        @if($usesPartnerSidebar)<script>
            (() => {
                const sidebar = document.querySelector('.partner-sidebar');
                const scrim = document.querySelector('.partner-sidebar-scrim');
                const toggle = () => { sidebar?.classList.toggle('is-open'); scrim?.classList.toggle('hidden'); };
                window.addEventListener('partner-menu-toggle', toggle);
                scrim?.addEventListener('click', toggle);
            })();
        </script>@endif
    </body>
</html>

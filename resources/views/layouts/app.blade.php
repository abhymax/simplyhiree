<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SimplyHiree') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                body.has-admin-sidebar { padding-top: 0; padding-left: 16rem; }
                .admin-mobile-only { display: none !important; }
                .admin-sidebar-aside { transform: translateX(0) !important; }
            }
            @media (max-width: 1023px) {
                .admin-desktop-only { display: none !important; }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 @if(auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'))) has-admin-sidebar @endif">
        
        @php
            $usesSidebar = auth()->check() && (auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Manager'));
        @endphp

        <div class="flex flex-col min-h-screen">

            @if($usesSidebar)
                @include('layouts.admin-sidebar')
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
                    <div class="flex gap-5 font-medium">
                        <a href="{{ route('privacy') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Privacy Policy</a>
                        <a href="{{ route('terms') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Terms of Service</a>
                        <a href="{{ auth()->check() ? route('support') : route('contact') }}" class="hover:{{ $usesSidebar ? 'text-white' : 'text-indigo-600' }} transition-colors">Support</a>
                    </div>
                </div>
            </footer>

        </div>

        @livewireScripts
    </body>
</html>
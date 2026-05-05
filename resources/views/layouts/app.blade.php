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
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900">
        
        <div class="flex flex-col min-h-screen">
            
            @include('layouts.navigation')

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

            <footer class="bg-white border-t border-slate-200 mt-auto py-8 z-10 relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-slate-500">
                        &copy; {{ date('Y') }} <span class="font-bold text-slate-700">SimplyHiree</span>. All rights reserved.
                    </div>
                    
                    <div class="flex gap-6 text-sm font-medium text-slate-500">
                        <a href="#" class="hover:text-indigo-600 transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-indigo-600 transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-indigo-600 transition-colors">Support</a>
                    </div>
                </div>
            </footer>

        </div>

        @livewireScripts
    </body>
</html>
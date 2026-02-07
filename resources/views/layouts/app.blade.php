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
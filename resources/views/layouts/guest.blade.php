<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" style="background-color: #020512 !important;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SimplyHiree') }} — Connect Sourcing Partners & Employers</title>
        
        <!-- FAVICON -->
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'><stop offset='0%' stop-color='%232563eb' /><stop offset='100%' stop-color='%234f46e5' /></linearGradient></defs><rect width='100' height='100' rx='20' fill='url(%23g)' /><text x='50' y='65' font-size='50' font-weight='bold' text-anchor='middle' fill='white' font-family='Roboto'>SH</text></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html, body {
                background-color: #020512 !important;
                color: #e2e8f0 !important;
            }
            body {
                font-family: 'Outfit', sans-serif;
            }
            /* Custom Form Styling Overrides for Breathtaking Premium UI */
            label {
                color: #cbd5e1 !important; /* text-slate-300 */
                font-size: 0.75rem !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.08em !important;
                margin-bottom: 0.45rem !important;
                display: inline-block !important;
            }
            input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select {
                background-color: rgba(3, 7, 26, 0.85) !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                border-radius: 0.75rem !important;
                color: #ffffff !important;
                padding: 0.75rem 1rem !important;
                box-shadow: inset 0 2px 4px 0 rgba(3, 7, 26, 0.75) !important;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                font-size: 0.875rem !important;
                width: 100% !important;
            }
            input:focus, select:focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), inset 0 2px 4px 0 rgba(3, 7, 26, 0.75) !important;
                outline: none !important;
            }
            select option {
                background-color: #0b0f19 !important;
                color: #ffffff !important;
            }
            x-primary-button, button[type="submit"] {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
                border: 1px solid rgba(255,255,255,0.1) !important;
                border-radius: 0.75rem !important;
                color: #ffffff !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                padding: 0.85rem 1.5rem !important;
                letter-spacing: 0.05em !important;
                box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4) !important;
                transition: all 0.25s ease !important;
                cursor: pointer !important;
            }
            button[type="submit"]:hover {
                transform: translateY(-1px) !important;
                box-shadow: 0 12px 30px -5px rgba(37, 99, 235, 0.5) !important;
                filter: brightness(1.05) !important;
            }
            button[type="submit"]:active {
                transform: translateY(1px) !important;
            }
            button[type="button"] {
                transition: all 0.2s ease !important;
            }
            button[type="button"]:hover {
                transform: translateY(-1px) !important;
                filter: brightness(1.05) !important;
            }
            button[type="button"]:active {
                transform: translateY(1px) !important;
            }
            
            /* Helper classes to guarantee styling */
            .glass-card-form {
                background-color: #0b0f19 !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            }
        </style>
    </head>
    <body class="font-sans text-slate-200 antialiased bg-slate-950 min-h-screen" style="background-color: #020512 !important;">
        @php
            $title = 'Welcome back!';
            $slogan = 'Secure Gateway';
            $quote = '"Connecting elite talent with outstanding opportunities across verified global employers."';
            $iconHtml = '<i class="fa-solid fa-shield-halved text-8xl text-blue-500 animate-pulse"></i>';
            $leftBg = 'from-slate-950 via-slate-900 to-indigo-950';

            if (request()->is('login*')) {
                $title = 'Welcome to SimplyHiree';
                $slogan = 'Accelerating Executive Recruitment';
                $quote = '"Hiring was, is, and always will be the most critical operational key to scaling great organizations."';
                $iconHtml = '<i class="fa-solid fa-users-viewfinder text-8xl text-blue-500 animate-pulse"></i>';
                $leftBg = 'from-slate-950 via-slate-900 to-blue-950';
            } elseif (request()->is('register/client*')) {
                $title = 'Hire Pre-Vetted Talent';
                $slogan = 'SimplyHiree for Corporate Employers';
                $quote = '"Acquiring the right people is the single most important action in building lasting enterprise value."';
                $iconHtml = '<i class="fa-solid fa-building-circle-check text-8xl text-emerald-500 animate-pulse"></i>';
                $leftBg = 'from-slate-950 via-slate-900 to-emerald-950';
            } elseif (request()->is('register/candidate*')) {
                $title = 'Land Your Dream Role';
                $slogan = 'SimplyHiree for Elite Professionals';
                $quote = '"The only way to do truly outstanding work is to align your talents with the perfect opportunity."';
                $iconHtml = '<i class="fa-solid fa-bolt-lightning text-8xl text-purple-500 animate-pulse"></i>';
                $leftBg = 'from-slate-950 via-slate-900 to-purple-950';
            } elseif (request()->is('register/partner*') || request()->is('register/vendor*')) {
                $title = 'Scale Your Agency Payouts';
                $slogan = 'SimplyHiree for Expert Sourcing Partners';
                $quote = '"Colossal outcomes in executive staffing are always driven by collaborative, elite recruiter alliances."';
                $iconHtml = '<i class="fa-solid fa-handshake-simple text-8xl text-cyan-500 animate-pulse"></i>';
                $leftBg = 'from-slate-950 via-slate-900 to-cyan-950';
            }
        @endphp

        <div class="min-h-screen lg:grid lg:grid-cols-12 bg-[#020512]" style="background-color: #020512 !important;">
            
            {{-- LEFT SIDE PANEL - Premium Slogans & Graphics --}}
            <aside class="lg:col-span-5 hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br {{ $leftBg }} border-r border-white/5 relative overflow-hidden">
                {{-- Decorative Glowing Blur Blobs --}}
                <div class="absolute -top-12 -left-12 w-80 h-80 bg-blue-500/10 rounded-full filter blur-[80px]"></div>
                <div class="absolute -bottom-12 -right-12 w-80 h-80 bg-indigo-500/10 rounded-full filter blur-[80px]"></div>
                
                {{-- Brand Logo --}}
                <div class="relative z-10 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-extrabold text-lg shadow-lg shadow-blue-500/30">
                        SH
                    </div>
                    <div class="font-black text-2xl text-white tracking-tight">Simply<span class="text-blue-500">Hiree</span></div>
                </div>

                {{-- Graphics and Slogans Center --}}
                <div class="relative z-10 my-auto py-12 flex flex-col items-center text-center">
                    <div class="mb-8 p-6 bg-slate-900/30 border border-white/5 rounded-full shadow-2xl backdrop-blur-md">
                        {!! $iconHtml !!}
                    </div>
                    <h2 class="text-3xl font-black text-white tracking-tight leading-tight mb-2">{{ $title }}</h2>
                    <p class="text-blue-400 font-bold uppercase tracking-wider text-xs mb-6">{{ $slogan }}</p>
                    <div class="max-w-sm">
                        <p class="text-slate-300 text-sm italic leading-relaxed">
                            {{ $quote }}
                        </p>
                    </div>
                </div>

                {{-- Footer Branding --}}
                <div class="relative z-10 text-xs text-slate-500 font-semibold tracking-wide flex items-center justify-between">
                    <span>© {{ date('Y') }} SimplyHiree</span>
                    <span>Your Growth. Our Platform.</span>
                </div>
            </aside>

            {{-- RIGHT SIDE PANEL - Forms --}}
            <main class="lg:col-span-7 flex items-center justify-center p-6 sm:p-12 relative overflow-y-auto bg-[#020512]" style="background-color: #020512 !important;">
                {{-- Mobile Brand Logo --}}
                <div class="lg:hidden absolute top-6 left-6 flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-extrabold text-sm shadow-md">
                        SH
                    </div>
                    <span class="font-bold text-lg text-white">SimplyHiree</span>
                </div>

                <div class="w-full {{ (request()->is('register*') || request()->is('signup*')) ? 'sm:max-w-2xl' : 'sm:max-w-md' }} my-8">
                    {{-- Standard Guest Form Wrap --}}
                    <div class="glass-card-form rounded-3xl p-8 relative overflow-hidden">
                        {{-- Inset decorative highlight --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                        
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>

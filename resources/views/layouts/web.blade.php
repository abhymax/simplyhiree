<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SimplyHiree') - The Future of Recruitment</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#0EA5E9', // Sky 500
                        dark: '#0F172A', // Slate 900
                    },
                    animation: { 'blob': 'blob 7s infinite' },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        .text-gradient {
            background: linear-gradient(135deg, #4F46E5 0%, #0EA5E9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">

    <header x-data="{ mobileMenuOpen: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="{ 'glass-nav shadow-sm': scrolled, 'bg-transparent': !scrolled }"
            class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg transform group-hover:rotate-12 transition-transform">
                    SH
                </div>
                <span class="text-2xl font-bold text-slate-900 tracking-tight">SimplyHiree</span>
            </a>

            <nav class="hidden md:flex items-center space-x-8 font-medium">
                <a href="{{ route('about') }}" class="text-slate-600 hover:text-primary transition-colors">About</a>
                <a href="{{ route('contact') }}" class="text-slate-600 hover:text-primary transition-colors">Contact</a>
                <a href="/login" class="text-slate-600 hover:text-primary transition-colors">Login</a>
                <a href="/register/candidate" class="px-6 py-2.5 bg-slate-900 text-white rounded-full hover:bg-primary hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    Get Started
                </a>
            </nav>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-700">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-100 shadow-xl p-4 flex flex-col space-y-4">
            <a href="{{ route('about') }}" class="text-slate-600 font-medium">About</a>
            <a href="{{ route('contact') }}" class="text-slate-600 font-medium">Contact</a>
            <a href="/login" class="text-slate-600 font-medium">Login</a>
            <a href="/register/candidate" class="bg-primary text-white text-center py-3 rounded-lg font-bold">Sign Up Free</a>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-300 py-16 mt-auto">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">SH</div>
                        <span class="text-2xl font-bold text-white">SimplyHiree</span>
                    </div>
                    <p class="text-slate-400 max-w-sm leading-relaxed">
                        We are bridging the gap between talent and opportunity. Our AI-driven platform ensures the perfect match for employers, partners, and candidates.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('about') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">About Us</a></li>
                        <li><a href="{{ route('home') }}#features" class="hover:text-white hover:translate-x-1 transition-all inline-block">Features</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">Contact Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Legal</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('privacy') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} SimplyHiree. All Rights Reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors"><i class="fab fa-linkedin"></i> LinkedIn</a>
                    <a href="#" class="hover:text-white transition-colors"><i class="fab fa-twitter"></i> Twitter</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50, duration: 800, easing: 'ease-out-cubic' });
    </script>
</body>
</html>
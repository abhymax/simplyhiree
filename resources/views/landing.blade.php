<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimplyHiree - The Future of Recruitment</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#0EA5E9', // Sky 500
                        dark: '#0F172A', // Slate 900
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                    },
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .text-gradient {
            background: linear-gradient(135deg, #4F46E5 0%, #0EA5E9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Hide scrollbar for clean look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a0a0a0; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 overflow-x-hidden selection:bg-primary selection:text-white">

    <header x-data="{ mobileMenuOpen: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="{ 'glass-nav shadow-sm': scrolled, 'bg-transparent': !scrolled }"
            class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg transform group-hover:rotate-12 transition-transform">
                    SH
                </div>
                <span class="text-2xl font-bold text-slate-900 tracking-tight">SimplyHiree</span>
            </a>

            <nav class="hidden md:flex items-center space-x-8 font-medium">
                <a href="#features" class="text-slate-600 hover:text-primary transition-colors">Features</a>
                <a href="#roles" class="text-slate-600 hover:text-primary transition-colors">How it Works</a>
                <a href="/login" class="text-slate-600 hover:text-primary transition-colors">Login</a>
                <a href="/register/candidate" class="px-6 py-2.5 bg-slate-900 text-white rounded-full hover:bg-primary hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    Get Started
                </a>
            </nav>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-700 focus:outline-none">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-5"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-100 shadow-xl p-4 flex flex-col space-y-4">
            <a href="#features" class="text-slate-600 font-medium">Features</a>
            <a href="#roles" class="text-slate-600 font-medium">How it Works</a>
            <a href="/login" class="text-slate-600 font-medium">Login</a>
            <a href="/register/candidate" class="bg-primary text-white text-center py-3 rounded-lg font-bold">Sign Up Free</a>
        </div>
    </header>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-0 left-1/4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-1/3 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <div class="container mx-auto px-6 text-center">
            <div data-aos="fade-up" data-aos-duration="1000">
                <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 text-primary text-sm font-semibold mb-6 border border-indigo-100">
                    🚀 Revolutionizing Recruitment
                </span>
                <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 leading-tight mb-6">
                    Hiring Made <br>
                    <span class="text-gradient">Simple & Intelligent</span>
                </h1>
                <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Connect employers, recruitment partners, and qualified candidates on one seamless platform. No more chaos, just results.
                </p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="#roles" class="px-8 py-4 bg-primary text-white rounded-full font-bold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-1 transition-all">
                        Start Hiring / Job Search
                    </a>
                    <a href="/jobs" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-full font-bold hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Browse Open Jobs
                    </a>
                </div>
            </div>

            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto border-t border-slate-200 pt-10" data-aos="fade-up" data-aos-delay="200">
                <div>
                    <div class="text-3xl font-bold text-slate-900">500+</div>
                    <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Companies</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-slate-900">12k+</div>
                    <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Candidates</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-slate-900">98%</div>
                    <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Success Rate</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-slate-900">24/7</div>
                    <div class="text-sm text-slate-500 font-medium uppercase tracking-wide">Support</div>
                </div>
            </div>
        </div>
    </section>

    <section id="roles" class="py-24 bg-white relative">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">Choose Your Path</h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto">We've built dedicated tools for every player in the recruitment ecosystem.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="group relative bg-slate-50 rounded-3xl p-8 border border-slate-100 hover:border-blue-500 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2" data-aos="fade-up" data-aos-delay="0">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-6 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Employer / Client</h3>
                    <p class="text-slate-600 mb-8 leading-relaxed">
                        Post unlimited jobs, manage applicants with a drag-and-drop pipeline, and hire 3x faster.
                    </p>
                    <a href="/register/client" class="inline-flex items-center text-blue-600 font-bold group-hover:translate-x-2 transition-transform">
                        Post a Job <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>

                <div class="group relative bg-slate-50 rounded-3xl p-8 border border-slate-100 hover:border-emerald-500 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-6 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Recruitment Partner</h3>
                    <p class="text-slate-600 mb-8 leading-relaxed">
                        Access exclusive job mandates, submit your candidates, and earn commissions transparently.
                    </p>
                    <a href="/register/partner" class="inline-flex items-center text-emerald-600 font-bold group-hover:translate-x-2 transition-transform">
                        Join Network <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>

                <div class="group relative bg-slate-50 rounded-3xl p-8 border border-slate-100 hover:border-purple-500 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-6 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Job Seeker</h3>
                    <p class="text-slate-600 mb-8 leading-relaxed">
                        Create a stunning profile, apply with one click, and get tracked by top companies.
                    </p>
                    <a href="/register/candidate" class="inline-flex items-center text-purple-600 font-bold group-hover:translate-x-2 transition-transform">
                        Find a Job <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <section id="features" class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-20" data-aos="zoom-in">
                <span class="text-primary font-bold tracking-wider uppercase text-sm">Why Choose Us</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">Built for Efficiency</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <div class="flex gap-4" data-aos="fade-right">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Lightning Fast Matching</h4>
                        <p class="text-slate-600">Our algorithms instantly match job requirements with candidate profiles.</p>
                    </div>
                </div>
                <div class="flex gap-4" data-aos="fade-right" data-aos-delay="100">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Verified Profiles</h4>
                        <p class="text-slate-600">Every candidate and company is vetted to ensure quality and trust.</p>
                    </div>
                </div>
                <div class="flex gap-4" data-aos="fade-right" data-aos-delay="200">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Real-time Updates</h4>
                        <p class="text-slate-600">Never wonder about your application status again. Get notified instantly.</p>
                    </div>
                </div>
                <div class="flex gap-4" data-aos="fade-left">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Advanced Analytics</h4>
                        <p class="text-slate-600">Clients get deep insights into their hiring funnel and recruitment metrics.</p>
                    </div>
                </div>
                 <div class="flex gap-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Secure & Private</h4>
                        <p class="text-slate-600">Your data is encrypted and safe. We prioritize user privacy above all.</p>
                    </div>
                </div>
                <div class="flex gap-4" data-aos="fade-left" data-aos-delay="200">
                    <div class="w-12 h-12 shrink-0 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-900 mb-2">Global Reach</h4>
                        <p class="text-slate-600">Connect with talent or opportunities from anywhere in the country.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 transform -skew-y-3 origin-left scale-110"></div>
        <div class="container mx-auto px-6 relative text-center text-white" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Ready to Get Started?</h2>
            <p class="text-xl text-indigo-100 mb-10 max-w-2xl mx-auto">Join thousands of professionals and companies who have streamlined their recruitment journey.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/register/candidate" class="px-8 py-4 bg-white text-primary rounded-full font-bold shadow-lg hover:shadow-xl hover:bg-gray-50 transition-all">
                    Sign Up Now
                </a>
                <a href="/login" class="px-8 py-4 bg-indigo-700 text-white rounded-full font-bold border border-indigo-500 hover:bg-indigo-800 transition-all">
                    Login to Account
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-slate-300 py-16">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-2xl font-bold text-white mb-4 block">SimplyHiree</span>
                    <p class="text-slate-400 max-w-sm">
                        Simplifying the hiring process with smart technology and human-centric design.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} SimplyHiree. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100,
            duration: 800,
            easing: 'ease-out-cubic',
        });
    </script>
</body>
</html>
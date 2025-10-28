<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimplyHiree - Unlock the Power of Recruitment</title>
    <!-- Using latest Tailwind CSS for modern utilities and design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Using Google Fonts for better typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .hero-gradient {
            background: linear-gradient(90deg, #0d324d 0%, #7f5a83 100%);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800">

    <!-- Navigation Bar -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto flex justify-between items-center p-4">
            <a href="/" class="text-3xl font-bold text-slate-900">SimplyHiree</a>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="#features" class="text-slate-600 hover:text-slate-900 transition-colors">Features</a>
                <a href="#about" class="text-slate-600 hover:text-slate-900 transition-colors">About Us</a>
                <a href="/login" class="text-slate-600 hover:text-slate-900 transition-colors">Login</a>
                <a href="/register/candidate" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-4 rounded-lg transition-transform hover:scale-105">
                    Sign Up
                </a>
            </nav>
            <!-- Mobile Menu Button (optional) -->
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient text-white text-center py-24 md:py-32">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">Unlock the Power of Recruitment</h1>
            <p class="mt-4 text-lg md:text-xl max-w-3xl mx-auto text-slate-200">The seamless platform connecting employers, recruitment partners, and qualified candidates instantly.</p>
            <div class="mt-10 flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#getting-started" class="bg-yellow-400 text-slate-900 font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-yellow-500 transition-transform hover:scale-105 w-full sm:w-auto">Get Started</a>
                <a href="/jobs" class="border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white/10 transition-colors w-full sm:w-auto">Explore Open Jobs</a>
            </div>
        </div>
    </section>

    <!-- "Getting Started" Section for Roles -->
    <section id="getting-started" class="py-20 bg-white">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl md:text-4xl font-bold">Who Are You?</h2>
            <p class="mt-3 text-lg text-slate-600 max-w-2xl mx-auto">Choose your path and let's find the perfect fit together.</p>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- For Clients/Employers -->
                <div class="bg-slate-50 p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow border border-slate-200">
                    <h3 class="text-2xl font-bold text-slate-900">I'm a Client/Employer</h3>
                    <p class="mt-4 text-slate-600">Need to fill a position? Post jobs, manage applicants, and hire top-tier talent directly.</p>
                    <a href="/register/client" class="mt-6 inline-block bg-sky-500 text-white font-semibold py-2 px-5 rounded-lg hover:bg-sky-600 transition-colors">Post a Job</a>
                </div>
                <!-- For Partners/Agencies -->
                <div class="bg-slate-50 p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow border border-slate-200">
                    <h3 class="text-2xl font-bold text-slate-900">I'm a Partner/Agency</h3>
                    <p class="mt-4 text-slate-600">Have a pool of great candidates? Connect them to exclusive job openings from our clients.</p>
                    <a href="/register/partner" class="mt-6 inline-block bg-teal-500 text-white font-semibold py-2 px-5 rounded-lg hover:bg-teal-600 transition-colors">Become a Partner</a>
                </div>
                <!-- For Candidates -->
                <div class="bg-slate-50 p-8 rounded-xl shadow-md hover:shadow-xl transition-shadow border border-slate-200">
                    <h3 class="text-2xl font-bold text-slate-900">I'm a Candidate</h3>
                    <p class="mt-4 text-slate-600">Looking for your next career move? Browse jobs, apply with one click, and track your status.</p>
                    <a href="/register/candidate" class="mt-6 inline-block bg-amber-500 text-white font-semibold py-2 px-5 rounded-lg hover:bg-amber-600 transition-colors">Find a Job</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="features" class="bg-slate-100 py-20">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl md:text-4xl font-bold">Why Choose SimplyHiree?</h2>
            <p class="mt-3 text-lg text-slate-600 max-w-2xl mx-auto">We offer unmatched ease, security, and efficiency in the recruitment process.</p>
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold">Fast Matching</h3>
                    <p class="mt-2 text-slate-600">Find the best candidates in minutes using our advanced matching algorithm.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold">Unified Platform</h3>
                    <p class="mt-2 text-slate-600">Manage jobs, candidates, and agency partners all in one place.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-xl font-bold">Instant Notifications</h3>
                    <p class="mt-2 text-slate-600">Get notified instantly about applications, interviews, and status updates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-8 text-center">
        <div class="container mx-auto">
            <p>&copy; {{ date('Y') }} SimplyHiree. All Rights Reserved.</p>
            <div class="mt-4 space-x-4">
                <a href="#" class="text-slate-400 hover:text-white">Contact Us</a>
                <a href="#" class="text-slate-400 hover:text-white">Privacy Policy</a>
                <a href="#" class="text-slate-400 hover:text-white">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>

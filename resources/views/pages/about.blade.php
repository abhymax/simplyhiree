@extends('layouts.web')

@section('title', 'About Us')

@section('content')
<style>
    .about-hero {
        background:
            radial-gradient(60% 60% at 15% 10%, rgba(79,70,229,0.12), transparent 60%),
            radial-gradient(50% 50% at 90% 0%, rgba(14,165,233,0.12), transparent 55%),
            #f8fafc;
    }
    .blob { position:absolute; border-radius:9999px; filter:blur(90px); opacity:.5; z-index:0; }
    .value-card { transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease; }
    .value-card:hover { transform: translateY(-6px); box-shadow: 0 24px 50px -24px rgba(79,70,229,.45); border-color: rgba(79,70,229,.35); }
    .stat-num { background: linear-gradient(135deg,#4F46E5,#0EA5E9); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
</style>

{{-- HERO --}}
<section class="about-hero relative overflow-hidden pt-48 pb-24 px-6">
    <div class="blob w-80 h-80 bg-indigo-300 -top-10 -left-10"></div>
    <div class="blob w-72 h-72 bg-sky-300 top-20 right-0"></div>
    <div class="relative z-10 max-w-4xl mx-auto text-center">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white shadow-sm border border-slate-200 text-primary font-bold tracking-wider uppercase text-xs" data-aos="fade-down">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Our Story
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mt-6 leading-[1.1]" data-aos="fade-up">
            We're rebuilding <span class="text-gradient">recruitment</span><br class="hidden md:block"> around people, not paperwork
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto mt-6" data-aos="fade-up" data-aos-delay="100">
            SimplyHiree connects companies, recruitment agencies, and candidates on one transparent platform — so the right people meet the right opportunities, faster.
        </p>
        <div class="flex flex-wrap justify-center gap-4 mt-10" data-aos="fade-up" data-aos-delay="200">
            <a href="/register/candidate"
               class="inline-flex items-center gap-2.5 px-8 py-4 rounded-full font-bold text-white shadow-lg hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300"
               style="background: linear-gradient(135deg,#4F46E5,#0EA5E9); box-shadow:0 12px 28px -10px rgba(79,70,229,.6);">
                <i class="fa-solid fa-rocket"></i> Get Started Free
            </a>
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2.5 px-8 py-4 rounded-full font-bold transition-all duration-300"
               style="background:#e0e7ff; color:#4338ca;"
               onmouseover="this.style.background='#c7d2fe'" onmouseout="this.style.background='#e0e7ff'">
                <i class="fa-solid fa-comments"></i> Talk to Us
            </a>
        </div>
    </div>
</section>

{{-- STATS BAND --}}
<section class="bg-white py-14 border-y border-slate-100">
    <div class="container mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        @foreach([
            ['num' => '1000+', 'label' => 'Active Clients',     'icon' => 'fa-building'],
            ['num' => '10K+',  'label' => 'Trusted Vendors',    'icon' => 'fa-handshake'],
            ['num' => '50K+',  'label' => 'Successful Hires',   'icon' => 'fa-user-check'],
            ['num' => '4.8/5', 'label' => 'Avg. Partner Rating','icon' => 'fa-star'],
        ] as $i => $s)
            <div data-aos="zoom-in" data-aos-delay="{{ $i * 80 }}">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-50 text-primary flex items-center justify-center mb-3">
                    <i class="fa-solid {{ $s['icon'] }} text-lg"></i>
                </div>
                <div class="text-3xl md:text-4xl font-extrabold stat-num">{{ $s['num'] }}</div>
                <div class="text-slate-500 text-sm font-medium mt-1">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>
</section>

{{-- MISSION --}}
<section class="py-24 bg-slate-50">
    <div class="container mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
        <div data-aos="fade-right">
            <div class="relative">
                <div class="absolute -inset-3 bg-gradient-to-br from-indigo-500 to-sky-500 rounded-[2rem] rotate-3 opacity-90"></div>
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80"
                     alt="The SimplyHiree team" class="relative rounded-[1.6rem] shadow-2xl">
            </div>
        </div>
        <div data-aos="fade-left">
            <span class="text-primary font-bold uppercase tracking-wider text-xs">Our Mission</span>
            <h2 class="text-3xl md:text-4xl font-extrabold mt-3 mb-6 text-slate-900 leading-tight">
                Every resume is a dream.<br>Every opening is a future.
            </h2>
            <p class="text-slate-600 mb-8 text-lg leading-relaxed">
                Hiring is still slow, opaque, and full of friction. We're changing that by giving companies, agencies, and candidates a single place to collaborate — with clear status at every step, fair commercials, and tools that do the heavy lifting.
            </p>
            <div class="space-y-5">
                @foreach([
                    ['t' => 'Transparency first', 'd' => 'Clear pipeline status, honest commercials, no ghosting.'],
                    ['t' => 'Speed that matters', 'd' => 'Smart matching and streamlined rounds shorten time-to-hire.'],
                    ['t' => 'Built on trust',     'd' => 'Verified vendors, replacement guarantees, and accountable payouts.'],
                ] as $n => $m)
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 shrink-0 rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white flex items-center justify-center font-bold text-sm shadow-lg">{{ $n + 1 }}</div>
                        <div>
                            <h4 class="font-bold text-slate-900">{{ $m['t'] }}</h4>
                            <p class="text-slate-600 text-sm">{{ $m['d'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- VALUES / WHAT WE DO --}}
<section class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
            <span class="text-primary font-bold uppercase tracking-wider text-xs">What drives us</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-3">One platform, every side of hiring</h2>
            <p class="text-slate-600 mt-4">From the first job post to the final joining date, SimplyHiree keeps everyone in sync.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['icon' => 'fa-briefcase', 'bg' => '#eef2ff', 'fg' => '#4f46e5', 't' => 'For Companies',  'd' => 'Post roles, manage applicants, schedule multi-round interviews, and track billing — all in one workspace.'],
                ['icon' => 'fa-handshake', 'bg' => '#e0f2fe', 'fg' => '#0284c7', 't' => 'For Agencies',   'd' => 'Submit candidates to open requirements, track your pipeline, and get paid on time with replacement protection.'],
                ['icon' => 'fa-user-tie',  'bg' => '#d1fae5', 'fg' => '#059669', 't' => 'For Candidates', 'd' => 'Apply once, get matched to the right roles, and receive interview reminders over WhatsApp & email.'],
            ] as $i => $c)
                <div class="value-card bg-slate-50 border border-slate-100 rounded-3xl p-8" data-aos="fade-up" data-aos-delay="{{ $i * 100 }}">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-5" style="background: {{ $c['bg'] }}; color: {{ $c['fg'] }};">
                        <i class="fa-solid {{ $c['icon'] }}"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $c['t'] }}</h3>
                    <p class="text-slate-600 leading-relaxed">{{ $c['d'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- LEADERSHIP --}}
<section class="py-24 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="text-primary font-bold uppercase tracking-wider text-xs">The Team</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-3">Meet the leadership</h2>
            <p class="text-slate-600 mt-2">The people building SimplyHiree.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            @foreach([
                ['name' => 'Aman Yadav',  'role' => 'CEO & Founder',    'img' => 'men/32'],
                ['name' => 'Surbhi S',    'role' => 'Head of Product',  'img' => 'women/44'],
                ['name' => 'Abhinab Roy', 'role' => 'Chief Technology Officer', 'img' => 'men/85'],
            ] as $i => $p)
                <div class="group bg-white rounded-3xl p-8 text-center shadow-sm hover:shadow-2xl transition-all duration-300" data-aos="fade-up" data-aos-delay="{{ $i * 100 }}">
                    <div class="w-28 h-28 mx-auto mb-5 rounded-full p-1 bg-gradient-to-br from-indigo-500 to-sky-500">
                        <img src="https://randomuser.me/api/portraits/{{ $p['img'] }}.jpg"
                             class="w-full h-full object-cover rounded-full grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                    <h3 class="font-bold text-xl text-slate-900">{{ $p['name'] }}</h3>
                    <p class="text-primary text-sm font-semibold mt-1">{{ $p['role'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 px-6 bg-white">
    <div class="container mx-auto">
        <div class="relative overflow-hidden rounded-[2.5rem] px-8 py-16 md:py-20 text-center"
             style="background: linear-gradient(135deg,#4F46E5,#0EA5E9);" data-aos="zoom-in">
            <div class="blob w-72 h-72 bg-white/20 -top-16 -right-10"></div>
            <div class="relative z-10 max-w-2xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white">Ready to hire smarter?</h2>
                <p class="text-indigo-100 mt-4 text-lg">Join the companies and agencies already growing with SimplyHiree.</p>
                <div class="flex flex-wrap justify-center gap-4 mt-9">
                    <a href="/register/candidate" class="px-8 py-3.5 bg-white text-slate-900 rounded-full font-bold hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300">
                        Create Free Account
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-3.5 bg-white/10 text-white border border-white/40 rounded-full font-bold hover:bg-white/20 transition-all duration-300">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

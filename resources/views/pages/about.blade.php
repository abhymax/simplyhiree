@extends('layouts.web')

@section('title', 'About Us')

@section('content')
    <section class="bg-slate-50 pt-32 pb-20 px-6 text-center">
        <span class="text-primary font-bold tracking-wider uppercase text-sm">Our Story</span>
        <h1 class="text-4xl md:text-6xl font-bold text-slate-900 mt-4 mb-6" data-aos="fade-up">
            We Are Revolutionizing <br> <span class="text-primary">Recruitment</span>
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            SimplyHiree was born from a simple idea: Hiring shouldn't be hard. We bridge the gap between talent and opportunity using technology and trust.
        </p>
    </section>

    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <div class="bg-gradient-to-br from-indigo-500 to-blue-500 rounded-3xl p-2 rotate-2">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Team" class="rounded-2xl shadow-2xl -rotate-2 transform transition hover:rotate-0 duration-500">
                </div>
            </div>
            <div data-aos="fade-left">
                <h2 class="text-3xl font-bold mb-6 text-slate-900">Our Mission</h2>
                <p class="text-slate-600 mb-6 text-lg leading-relaxed">
                    At SimplyHiree, we believe that every resume represents a dream and every job opening is a future waiting to be built. Our mission is to remove the friction from hiring by providing a unified platform where Companies, Agencies, and Candidates collaborate seamlessly.
                </p>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-primary font-bold">1</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Transparency First</h4>
                            <p class="text-slate-600 text-sm">No hidden fees, no ghosting. We prioritize clear communication.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-primary font-bold">2</div>
                        <div>
                            <h4 class="font-bold text-slate-900">AI-Powered Matching</h4>
                            <p class="text-slate-600 text-sm">We use technology to find the perfect fit, not just keywords.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Meet the Leadership</h2>
                <p class="text-slate-600 mt-2">The minds behind the platform.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm text-center group hover:shadow-xl transition-all">
                    <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                    </div>
                    <h3 class="font-bold text-xl">Amit Sharma</h3>
                    <p class="text-primary text-sm font-medium">CEO & Founder</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm text-center group hover:shadow-xl transition-all">
                    <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                    </div>
                    <h3 class="font-bold text-xl">Sarah Jenkins</h3>
                    <p class="text-primary text-sm font-medium">Head of Product</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm text-center group hover:shadow-xl transition-all">
                    <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 overflow-hidden">
                        <img src="https://randomuser.me/api/portraits/men/85.jpg" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                    </div>
                    <h3 class="font-bold text-xl">Rajiv Mehta</h3>
                    <p class="text-primary text-sm font-medium">CTO</p>
                </div>
            </div>
        </div>
    </section>
@endsection
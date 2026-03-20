@extends('layouts.web')

@section('title', 'Contact Us')

@section('content')
    <section class="pt-32 pb-20 px-6">
        <div class="container mx-auto max-w-6xl">
            <div class="text-center mb-16">
                <h1 class="text-4xl font-bold text-slate-900">Get in Touch</h1>
                <p class="text-slate-600 mt-4 text-lg">We'd love to hear from you. Here is how you can reach us.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 flex items-start gap-4">
                        <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Email Support</h3>
                            <p class="text-slate-500 text-sm mt-1">Our team typically responds within 2 hours.</p>
                            <a href="mailto:support@simplyhiree.com" class="text-primary font-medium mt-2 block">support@simplyhiree.com</a>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 flex items-start gap-4">
                        <div class="p-3 bg-emerald-100 text-emerald-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Phone</h3>
                            <p class="text-slate-500 text-sm mt-1">Mon-Fri from 9am to 6pm IST.</p>
                            <a href="tel:+918888353984" class="text-primary font-medium mt-2 block">8888353984</a>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 flex items-start gap-4">
                        <div class="p-3 bg-purple-100 text-purple-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Office</h3>
                            <p class="text-slate-500 text-sm mt-1">B-23, Sector 62, Industrial Area,<br>Noida, Uttar Pradesh 201309</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-slate-50 p-8 rounded-3xl border border-slate-200">
                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->has('contact'))
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800">
                            {{ $errors->first('contact') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full px-4 py-3 rounded-xl bg-white border {{ $errors->has('first_name') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200' }} focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="John">
                                @error('first_name')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full px-4 py-3 rounded-xl bg-white border {{ $errors->has('last_name') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200' }} focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="Doe">
                                @error('last_name')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 rounded-xl bg-white border {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200' }} focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="john@example.com">
                            @error('email')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Message</label>
                            <textarea rows="5" name="message" class="w-full px-4 py-3 rounded-xl bg-white border {{ $errors->has('message') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200' }} focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="How can we help you?">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl hover:bg-indigo-700 transition-colors shadow-lg hover:shadow-xl">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

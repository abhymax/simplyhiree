@extends('layouts.app')

@section('content')
<style>
    .fx-card { transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .fx-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(14,165,233,.22); border-color: rgba(255,255,255,.30); }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[22rem] h-[22rem] bg-indigo-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-3xl mx-auto">
        <div class="fx-card bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/10">
                <span class="inline-flex items-center text-cyan-300 text-xs font-bold uppercase tracking-wider">Candidate Onboarding</span>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mt-2">Create Candidate Account</h1>
                <p class="text-blue-100 mt-1">Fill your details to start applying for jobs.</p>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-5 bg-rose-500/20 border border-rose-400/40 text-rose-100 px-4 py-3 rounded-xl">
                        <ul class="list-disc ml-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.candidate') }}" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>

                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>

                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Phone Number (India)</label>
                            <input
                                type="tel"
                                name="phone_number"
                                value="{{ old('phone_number') }}"
                                required
                                maxlength="10"
                                minlength="10"
                                pattern="[6-9][0-9]{9}"
                                placeholder="10-digit mobile number"
                                class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>

                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Location</label>
                            <input type="text" name="location" value="{{ old('location') }}" class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>

                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Password</label>
                            <input type="password" name="password" required class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>

                        <div>
                            <label class="block text-blue-100 text-sm font-bold mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="w-full rounded-xl border border-white/20 bg-slate-800 text-white">
                        </div>
                    </div>

                    <div class="pt-2 flex flex-wrap gap-3">
                        <button type="submit" class="fx-btn bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black px-6 py-2.5 rounded-xl">
                            Register Candidate
                        </button>
                        <a href="{{ route('login') }}" class="fx-btn bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold px-6 py-2.5 rounded-xl">
                            Already have an account?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

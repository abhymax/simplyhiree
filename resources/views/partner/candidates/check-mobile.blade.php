@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden flex items-center">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 w-full max-w-md mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Partner Workspace
                </span>
                <h1 class="text-3xl font-extrabold text-white mt-4">Add New Candidate</h1>
                <p class="text-blue-200 mt-1 text-sm">Check mobile number before creating profile.</p>
            </div>

            <div class="p-6">
                <form action="{{ route('partner.candidates.verify') }}" method="POST">
                    @csrf

                    <label for="phone_number" class="block text-sm font-semibold text-blue-100 mb-2">
                        Mobile Number <span class="text-rose-300">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm">+91</span>
                        <input
                            type="text"
                            name="phone_number"
                            id="phone_number"
                            class="w-full pl-14 pr-4 py-3 rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            placeholder="9876543210"
                            required
                            autofocus
                        >
                    </div>

                    @error('phone_number')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="mt-5 w-full inline-flex justify-center items-center gap-2 py-3 px-4 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 transition shadow-lg">
                        Check & Proceed <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="mt-5 pt-5 border-t border-white/10 text-center">
                    <a href="{{ route('partner.candidates.index') }}" class="text-blue-200 hover:text-white font-semibold">
                        Cancel and go back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
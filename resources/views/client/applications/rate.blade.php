@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-2xl mx-auto">
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
            <div class="text-center mb-6">
                <i class="fa-solid fa-star text-5xl text-amber-300 mb-3"></i>
                <h1 class="text-3xl font-extrabold tracking-tight">Rate the Sourcing Partner</h1>
                <p class="text-blue-200 mt-1">Your feedback helps us reward quality vendors.</p>
            </div>

            <div class="bg-slate-800/60 border border-white/10 rounded-2xl px-4 py-3 mb-6 flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase text-blue-300 font-bold">Partner</div>
                    <div class="text-white font-extrabold">{{ $partner->name }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-blue-300">Hire</div>
                    <div class="text-white font-bold">{{ $application->candidate_name }}</div>
                    <div class="text-blue-200 text-xs">{{ $application->job?->title }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('client.applications.rate.store', $application->id) }}" class="space-y-5">
                @csrf

                {{-- Star picker --}}
                <div x-data="{ score: 5 }">
                    <label class="block text-sm font-bold text-amber-200 uppercase tracking-wider mb-2">Overall Rating *</label>
                    <div class="flex items-center gap-2 text-4xl">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" @click="score = {{ $i }}"
                                :class="score >= {{ $i }} ? 'text-amber-300' : 'text-slate-500'"
                                class="transition hover:scale-110">★</button>
                        @endfor
                        <span class="ml-3 text-white text-base font-bold" x-text="score + ' / 5'"></span>
                    </div>
                    <input type="hidden" name="score" x-bind:value="score">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-blue-200 mb-1">⚡ Speed</label>
                        <select name="speed_score" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2">
                            <option value="">—</option>
                            @for($i=1;$i<=5;$i++)<option value="{{$i}}">{{$i}} / 5</option>@endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-200 mb-1">📈 Quality</label>
                        <select name="quality_score" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2">
                            <option value="">—</option>
                            @for($i=1;$i<=5;$i++)<option value="{{$i}}">{{$i}} / 5</option>@endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-200 mb-1">💬 Communication</label>
                        <select name="communication_score" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2">
                            <option value="">—</option>
                            @for($i=1;$i<=5;$i++)<option value="{{$i}}">{{$i}} / 5</option>@endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase mb-1">Feedback (optional)</label>
                    <textarea name="feedback" rows="4" maxlength="1500" placeholder="Share specifics — what went well, what could be better"
                        class="w-full bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2"></textarea>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-sm text-slate-300 hover:text-white">Skip for now</a>
                    <button type="submit" class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold rounded-xl shadow-lg shadow-amber-500/20">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Submit Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

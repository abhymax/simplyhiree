@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-slate-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 py-10 text-white">
<div class="mx-auto max-w-3xl">
    <a href="{{ route('partner.replacements') }}" class="text-sm font-bold uppercase text-cyan-300"><i class="fa-solid fa-arrow-left mr-2"></i>Replacement requests</a>
    <div class="mt-5 rounded-3xl border border-amber-400/25 bg-gradient-to-br from-slate-900 to-amber-950/30 p-7 shadow-2xl">
        <h1 class="text-3xl font-black">Send a replacement candidate</h1>
        <p class="mt-2 text-slate-300">For {{ $application->job->title }}. This nomination is linked directly to replacement case #{{ $application->id }}.</p>
        @if((isset($errors) && $errors->any()) || session('error'))<div class="mt-5 rounded-xl border border-rose-400/40 bg-rose-500/10 p-3 text-rose-100">{{ session('error') ?: $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('partner.replacements.candidate.store',$application) }}" class="mt-7 space-y-5">@csrf
            <div><label class="mb-2 block text-xs font-bold uppercase text-amber-200">Replacement candidate</label><select name="candidate_id" required class="w-full rounded-xl border-white/15 bg-slate-900 text-white"><option value="">Choose from your candidate pool</option>@foreach($candidates as $candidate)<option value="{{ $candidate->id }}">{{ trim($candidate->first_name.' '.$candidate->last_name) }} · {{ $candidate->email ?: $candidate->phone_number }}</option>@endforeach</select></div>
            @if(!$application->job->screening_required)<div><label class="mb-2 block text-xs font-bold uppercase text-amber-200">Interview date & time</label><input type="datetime-local" name="interview_at" required min="{{ now()->format('Y-m-d\\TH:i') }}" class="w-full rounded-xl border-white/15 bg-slate-900 text-white"><p class="mt-2 text-xs text-slate-400">This is a direct-lineup job, so interview time is mandatory.</p></div>@endif
            <button class="w-full rounded-xl bg-amber-400 py-3 font-black text-slate-950 transition hover:-translate-y-0.5 hover:bg-amber-300"><i class="fa-solid fa-paper-plane mr-2"></i>Submit and link replacement</button>
        </form>
    </div>
</div></div>
@endsection

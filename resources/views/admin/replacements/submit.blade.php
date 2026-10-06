<x-app-layout>
<div class="min-h-screen bg-slate-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 py-10 text-white">
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.replacements.index') }}" class="text-sm font-bold uppercase text-cyan-300"><i class="fa-solid fa-arrow-left mr-2"></i>Replacement monitor</a>
    <div class="mt-5 rounded-3xl border border-amber-400/25 bg-gradient-to-br from-slate-900 to-amber-950/30 p-7 shadow-2xl">
        <h1 class="text-3xl font-black">Nominate replacement</h1>
        <p class="mt-2 text-slate-300">{{ $application->job->title }} · case #{{ $application->id }}. Only candidates belonging to the original sourcing partner are available.</p>
        @if((isset($errors) && $errors->any()) || session('error'))<div class="mt-5 rounded-xl border border-rose-400/40 bg-rose-500/10 p-3 text-rose-100">{{ session('error') ?: $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('admin.replacements.candidate.store',$application) }}" class="mt-7 space-y-5">@csrf
            <div><label class="mb-2 block text-xs font-bold uppercase text-amber-200">Replacement candidate</label><select name="candidate_id" required class="w-full rounded-xl border-white/15 bg-slate-900 text-white"><option value="">Choose candidate</option>@foreach($candidates as $candidate)<option value="{{ $candidate->id }}">{{ trim($candidate->first_name.' '.$candidate->last_name) }} · {{ $candidate->email ?: $candidate->phone_number }}</option>@endforeach</select></div>
            @if(!$application->job->screening_required)<div><label class="mb-2 block text-xs font-bold uppercase text-amber-200">Interview date & time</label><input type="datetime-local" name="interview_at" required min="{{ now()->format('Y-m-d\\TH:i') }}" class="w-full rounded-xl border-white/15 bg-slate-900 text-white"></div>@endif
            <button class="w-full rounded-xl bg-amber-400 py-3 font-black text-slate-950 hover:bg-amber-300"><i class="fa-solid fa-link mr-2"></i>Nominate and link</button>
        </form>
    </div>
</div></div>
</x-app-layout>

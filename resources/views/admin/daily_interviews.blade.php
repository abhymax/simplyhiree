@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mb-8 border-b border-white/10 pb-6">
            <h1 class="text-2xl font-extrabold text-white sm:text-3xl">Interviews</h1>
            <p class="mt-1 text-blue-200">Everything scheduled, and anything whose outcome is still unrecorded.</p>
        </div>

        @php
            $groups = [
                ['key' => 'today',    'label' => "Today",    'rows' => $todayRounds,    'tone' => 'cyan',
                 'empty' => 'Nothing scheduled for today.'],
                ['key' => 'upcoming', 'label' => 'Upcoming', 'rows' => $upcomingRounds, 'tone' => 'indigo',
                 'empty' => 'No interviews scheduled ahead.'],
                ['key' => 'pastdue',  'label' => 'Past due — outcome not marked', 'rows' => $pastDueRounds, 'tone' => 'amber',
                 'empty' => 'Nothing outstanding. Every past interview has an outcome.'],
            ];
            $tones = [
                'cyan'   => ['border-cyan-400/30 bg-cyan-500/15 text-cyan-200', 'text-cyan-300'],
                'indigo' => ['border-indigo-400/30 bg-indigo-500/15 text-indigo-200', 'text-indigo-300'],
                'amber'  => ['border-amber-400/30 bg-amber-500/15 text-amber-200', 'text-amber-300'],
            ];
        @endphp

        @foreach($groups as $g)
            @php [$badge, $accent] = $tones[$g['tone']]; @endphp
            <div class="mb-8 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/60" id="{{ $g['key'] }}">
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                    <h2 class="font-bold text-white">{{ $g['label'] }}</h2>
                    <span class="rounded-full border px-3 py-1 text-xs font-extrabold {{ $badge }}">{{ $g['rows']->count() }}</span>
                </div>

                @if($g['rows']->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-slate-400">{{ $g['empty'] }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-white/5 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">
                                <tr>
                                    <th class="px-5 py-4">When</th>
                                    <th class="px-5 py-4">Candidate</th>
                                    <th class="px-5 py-4">Round</th>
                                    <th class="px-5 py-4">Job Role</th>
                                    <th class="px-5 py-4">Client</th>
                                    <th class="px-5 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @foreach($g['rows'] as $r)
                                    @php
                                        $app = $r->application;
                                        $job = $app?->job;
                                        $phone = $app?->candidate?->phone_number ?? $app?->candidateUser?->profile?->phone_number;
                                    @endphp
                                    <tr class="transition-colors hover:bg-cyan-400/10">
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <div class="text-base font-extrabold {{ $accent }}">{{ $r->scheduled_at?->format('g:i A') }}</div>
                                            <div class="mt-0.5 text-xs text-slate-400">{{ $r->scheduled_at?->format('d M Y') }}</div>
                                            @if($g['key'] === 'pastdue')
                                                <div class="mt-0.5 text-[10px] font-bold text-amber-300">{{ $r->scheduled_at?->diffForHumans() }}</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="font-bold text-white">{{ $app?->candidate_name ?: 'Candidate' }}</div>
                                            @if($phone)<div class="mt-0.5 text-xs text-slate-400">{{ $phone }}</div>@endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-md border border-white/15 bg-white/5 px-2 py-1 text-xs font-bold text-slate-200">Round {{ $r->round_number }}</span>
                                            <div class="mt-1 text-xs text-slate-400">{{ $r->mode }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-200">{{ $job?->title ?? '—' }}</td>
                                        <td class="px-5 py-4 text-sm font-medium text-slate-200">{{ $job?->company_name ?: 'Internal' }}</td>
                                        <td class="px-5 py-4">
                                            @if($app)
                                                <a href="{{ route('admin.applications.show', $app->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-500 px-4 py-2 text-xs font-extrabold text-white transition hover:bg-indigo-400">
                                                    <i class="fa-regular fa-eye"></i> View Details
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-500">Application removed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach

    </div>
</div>
@endsection

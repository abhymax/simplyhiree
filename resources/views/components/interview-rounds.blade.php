@props(['rounds' => []])

@php
    $ivRounds = collect($rounds ?? [])->sortBy('round_number')->values();
@endphp

@if($ivRounds->isNotEmpty())
    <div class="mt-3 flex flex-wrap items-center gap-1.5">
        <span class="mr-1 text-[10px] font-bold uppercase tracking-wide text-slate-400">Interviews</span>
        @foreach($ivRounds as $r)
            @php
                $rating = (int) ($r->rating ?? 0);
                $rec = strtolower((string) ($r->recommendation ?? ''));
                $status = strtolower((string) ($r->status ?? ''));
                $done = filled($r->feedback) || $rating > 0
                    || in_array($status, ['completed', 'feedback_submitted', 'done', 'passed', 'cleared', 'rejected']);
                if (str_contains($rec, 'reject') || str_contains($rec, 'no')) {
                    $tint = 'border-rose-400/40 bg-rose-500/15 text-rose-100';
                } elseif (str_contains($rec, 'hold') || str_contains($rec, 'maybe')) {
                    $tint = 'border-amber-400/40 bg-amber-500/15 text-amber-100';
                } elseif (str_contains($rec, 'recommend') || str_contains($rec, 'select') || str_contains($rec, 'proceed') || str_contains($rec, 'yes') || str_contains($rec, 'strong')) {
                    $tint = 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100';
                } else {
                    $tint = 'border-white/15 bg-white/5 text-slate-200';
                }
                $payload = [
                    'round' => (int) $r->round_number,
                    'rating' => $rating,
                    'recommendation' => $r->recommendation,
                    'feedback' => $r->feedback,
                    'interviewer' => $r->interviewer_name ?? null,
                    'mode' => $r->mode ?? null,
                    'status' => $r->status ?? null,
                    'scheduled_at' => optional($r->scheduled_at)->format('d M Y, h:i A'),
                    'submitted_at' => optional($r->feedback_submitted_at)->format('d M Y'),
                ];
            @endphp
            <button type="button"
                @if($done) @click.stop="$dispatch('show-interview-round', @js($payload))" @else disabled @endif
                title="Round {{ $r->round_number }}{{ $done ? ' — click for HR feedback' : ' — awaiting feedback' }}"
                class="inline-flex items-center gap-1 rounded-md border px-1.5 py-0.5 text-[10px] font-bold leading-none {{ $tint }} {{ $done ? 'cursor-pointer transition hover:brightness-125' : 'cursor-default opacity-50' }}">
                <span>R{{ $r->round_number }}</span>
                <span class="tracking-tight text-amber-300" aria-hidden="true">@for($i = 1; $i <= 5; $i++){{ $i <= $rating ? '★' : '☆' }}@endfor</span>
                @if($done && filled($r->feedback))<i class="fa-regular fa-comment-dots text-[9px] opacity-80"></i>@endif
            </button>

            @if(strtolower((string) ($r->status ?? '')) === 'scheduled' && auth()->check() && auth()->user()->hasRole('client'))
                <form method="POST" action="{{ route('client.rounds.resend', $r->id) }}" class="inline"
                      onsubmit="event.stopPropagation(); return confirm('Re-send the Round {{ $r->round_number }} interview invite to this candidate?');">
                    @csrf
                    <button type="submit" @click.stop
                            title="Re-send the Round {{ $r->round_number }} invite by email and WhatsApp"
                            class="inline-flex items-center gap-1 rounded-md border border-cyan-300/30 bg-cyan-400/10 px-1.5 py-0.5 text-[10px] font-bold leading-none text-cyan-100 transition hover:bg-cyan-400/25">
                        <i class="fa-regular fa-paper-plane text-[9px]"></i><span class="sr-only">Re-send Round {{ $r->round_number }} invite</span>
                    </button>
                </form>
            @endif
        @endforeach
    </div>
@endif

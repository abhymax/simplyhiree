{{-- Compact assessment status + score + optional details link for list views.
     Expects: $app (JobApplication), $showRoute (nullable route name for detail). --}}
@php $b = $app->assessmentBadge(); @endphp
@if($b)
    @php
        $cmap = [
            'emerald' => 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30',
            'rose'    => 'bg-rose-500/15 text-rose-300 border-rose-400/30',
            'amber'   => 'bg-amber-500/15 text-amber-200 border-amber-400/30',
            'slate'   => 'bg-white/10 text-slate-300 border-white/20',
        ];
        $cls = $cmap[$b['color']] ?? $cmap['slate'];
    @endphp
    <div class="flex flex-col items-start gap-1">
        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $cls }}">
            {{ $b['label'] }}@if(!is_null($b['percentage'])) · {{ rtrim(rtrim(number_format($b['percentage'], 2), '0'), '.') }}%@endif
        </span>
        @if(!empty($showRoute) && $b['session_id'])
            <a href="{{ route($showRoute, $b['session_id']) }}" class="text-cyan-300 hover:text-cyan-200 text-[11px] font-bold">
                <i class="fa-solid fa-arrow-up-right-from-square mr-0.5"></i>View details
            </a>
        @endif
    </div>
@else
    <span class="text-slate-500 text-xs">—</span>
@endif

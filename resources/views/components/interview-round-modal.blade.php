{{--
    Shared interview-round lightbox. Include ONCE per page. Any
    <x-interview-rounds> chip dispatches 'show-interview-round' with the round
    payload; this listens on window, fills, and opens. Teleported to <body> so
    position:fixed anchors to the viewport (escapes blurred/transformed cards).
--}}
<div
    x-data="{ open: false, r: {} }"
    x-on:show-interview-round.window="r = $event.detail; open = true"
    x-on:keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div
            x-show="open"
            style="display: none;"
            class="fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto px-4 py-10"
        >
            <div
                x-show="open"
                x-transition.opacity
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"
                @click="open = false"
            ></div>

            <div
                x-show="open"
                x-transition
                class="relative z-10 w-full max-w-lg rounded-2xl border border-white/15 bg-slate-900 p-6 text-left text-white shadow-2xl"
            >
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black">Round <span x-text="r.round"></span> — HR feedback</h3>
                        <p class="mt-0.5 text-xs text-slate-400" x-text="r.scheduled_at ? ('Interviewed ' + r.scheduled_at) : ''"></p>
                    </div>
                    <button type="button" @click="open = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-white/10 hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <span class="text-xl leading-none text-amber-300" x-text="'★'.repeat(r.rating || 0) + '☆'.repeat(5 - (r.rating || 0))"></span>
                    <span class="text-sm font-bold text-slate-300"><span x-text="r.rating || 0"></span>/5</span>
                    <template x-if="r.recommendation">
                        <span class="rounded-full border border-white/20 bg-white/10 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide" x-text="r.recommendation"></span>
                    </template>
                </div>

                <dl class="mb-4 grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs text-slate-300">
                    <div><span class="text-slate-500">Interviewer:</span> <span x-text="r.interviewer || '—'"></span></div>
                    <div><span class="text-slate-500">Mode:</span> <span x-text="r.mode || '—'"></span></div>
                    <div><span class="text-slate-500">Status:</span> <span x-text="r.status || '—'"></span></div>
                    <div><span class="text-slate-500">Feedback on:</span> <span x-text="r.submitted_at || '—'"></span></div>
                </dl>

                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Remarks</div>
                <div class="mt-1.5 max-h-64 overflow-y-auto whitespace-pre-line rounded-xl border border-white/10 bg-slate-950/60 p-3 text-sm leading-relaxed text-slate-100"
                     x-text="r.feedback || 'No written remarks for this round.'"></div>
            </div>
        </div>
    </template>
</div>

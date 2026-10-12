@php $base = str_replace('.index', '', $homeRoute); @endphp

<div class="relative z-10 max-w-4xl mx-auto">
    <a href="{{ route($base . '.index') }}" class="mb-3 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-cyan-300 hover:text-white">
        <i class="fa-solid fa-arrow-left"></i> All messages
    </a>

    <div class="mb-4 rounded-2xl border border-white/10 bg-slate-900/60 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-xl font-extrabold text-white">
                    {{ $conversation->subject ?: 'Conversation' }}
                </h1>
                <p class="mt-0.5 text-sm text-blue-200">
                    {{ $isInternal ? ($conversation->party->name ?? 'Unknown') . ' · ' . (\App\Models\ChatConversation::PARTY_TYPES[$conversation->party_type] ?? $conversation->party_type) : 'SimplyHiree team' }}
                    @if($conversation->status === 'closed') · <span class="font-bold text-slate-400">Closed</span>@endif
                </p>
            </div>

            @if($isInternal)
                <div class="flex flex-wrap items-center gap-2">
                    @if(auth()->user()->hasRole('Superadmin'))
                        <form method="POST" action="{{ route($base . '.assign', $conversation) }}" class="flex items-center gap-2">
                            @csrf
                            <select name="assigned_to_user_id" onchange="this.form.submit()"
                                    class="rounded-lg border border-white/20 bg-slate-800 px-2 py-1.5 text-xs text-white">
                                <option value="">Unassigned</option>
                                @foreach($teamMembers as $m)
                                    <option value="{{ $m->id }}" @selected($conversation->assigned_to_user_id == $m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @elseif($conversation->assignee)
                        <span class="rounded-lg border border-indigo-400/30 bg-indigo-500/15 px-2 py-1.5 text-xs font-bold text-indigo-200">{{ $conversation->assignee->name }}</span>
                    @endif
                    <form method="POST" action="{{ route($base . '.status', $conversation) }}">
                        @csrf
                        <button class="rounded-lg border border-white/20 bg-white/5 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-white/10">
                            {{ $conversation->status === 'open' ? 'Close' : 'Reopen' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="mb-3 rounded-xl border border-rose-400/40 bg-rose-500/15 p-3 text-sm text-rose-100">{{ session('error') }}</div>
    @endif

    {{-- Thread --}}
    <div id="thread" class="mb-4 max-h-[55vh] space-y-3 overflow-y-auto rounded-2xl border border-white/10 bg-slate-900/40 p-4">
        @foreach($messages as $m)
            @php $mine = $m->sender_user_id === auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" data-mid="{{ $m->id }}">
                <div class="max-w-[78%] rounded-2xl px-4 py-2.5 {{ $mine ? 'bg-cyan-500/20 border border-cyan-300/25' : 'bg-white/5 border border-white/10' }}">
                    <div class="mb-0.5 text-[10px] font-bold uppercase tracking-wide {{ $mine ? 'text-cyan-200' : 'text-slate-400' }}">
                        {{ $m->sender?->name ?? 'Unknown' }} · {{ $m->created_at?->format('d M, h:i A') }}
                    </div>
                    @if(filled($m->body))
                        <div class="whitespace-pre-wrap text-sm text-white">{{ $m->body }}</div>
                    @endif
                    @foreach($m->attachments as $a)
                        <a href="{{ $a->url() }}" target="_blank" rel="noopener"
                           class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg border border-white/15 bg-white/5 px-2 py-1 text-xs font-bold text-cyan-100 hover:bg-white/10">
                            <i class="fa-regular fa-file"></i> {{ $a->original_name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Reply --}}
    <form method="POST" action="{{ route($base . '.reply', $conversation) }}" enctype="multipart/form-data"
          class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
        @csrf
        <textarea name="body" rows="3" maxlength="5000" placeholder="Write a reply…"
                  class="w-full rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white"></textarea>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-4 py-2 text-xs font-bold text-cyan-100 transition hover:bg-cyan-400/20">
                <i class="fa-solid fa-paperclip"></i>
                <span>Attach files</span>
                <input type="file" name="attachments[]" multiple class="hidden"
                       onchange="var n=this.files.length; document.getElementById('att-count').textContent = n ? (n + ' file' + (n>1?'s':'') + ' ready') : '';">
            </label>
            <span id="att-count" class="text-xs font-bold text-emerald-300"></span>
            <button class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-extrabold text-slate-950 hover:bg-cyan-400">
                <i class="fa-solid fa-paper-plane mr-1"></i> Send
            </button>
        </div>
        <p class="mt-1 text-[11px] text-slate-400">Up to 5 files, 10 MB each.</p>
    </form>
</div>

<script>
(function () {
    var thread = document.getElementById('thread');
    if (!thread) return;
    thread.scrollTop = thread.scrollHeight;

    var pollUrl = @json(route($base . '.poll', $conversation));
    var lastId  = {{ $messages->last()->id ?? 0 }};
    var me      = {{ auth()->id() }};

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    // Polling rather than websockets: the host has no socket server, and a few
    // seconds' delay is unnoticeable for messaging.
    setInterval(function () {
        fetch(pollUrl + '?after=' + lastId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                (d.messages || []).forEach(function (m) {
                    lastId = m.id;
                    var mine = m.side === @json(auth()->user() && \App\Models\ChatConversation::isInternal(auth()->user()) ? 'simplyhiree' : 'party');
                    var files = (m.files || []).map(function (f) {
                        return '<a href="' + f.url + '" target="_blank" class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg border border-white/15 bg-white/5 px-2 py-1 text-xs font-bold text-cyan-100">' + esc(f.name) + '</a>';
                    }).join('');
                    var html = '<div class="flex ' + (mine ? 'justify-end' : 'justify-start') + '" data-mid="' + m.id + '">' +
                        '<div class="max-w-[78%] rounded-2xl px-4 py-2.5 ' + (mine ? 'bg-cyan-500/20 border border-cyan-300/25' : 'bg-white/5 border border-white/10') + '">' +
                        '<div class="mb-0.5 text-[10px] font-bold uppercase tracking-wide ' + (mine ? 'text-cyan-200' : 'text-slate-400') + '">' + esc(m.sender) + ' · ' + esc(m.at) + '</div>' +
                        (m.body ? '<div class="whitespace-pre-wrap text-sm text-white">' + esc(m.body) + '</div>' : '') +
                        files + '</div></div>';
                    thread.insertAdjacentHTML('beforeend', html);
                });
                if ((d.messages || []).length) thread.scrollTop = thread.scrollHeight;
            })
            .catch(function () {});
    }, 7000);
})();
</script>

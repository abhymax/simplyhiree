@php $base = str_replace('.index', '', $homeRoute); @endphp

<div class="relative z-10 max-w-6xl mx-auto">
    <div class="mb-5 flex flex-col gap-3 border-b border-white/10 pb-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">Messages</h1>
            <p class="mt-1 text-sm text-blue-200">
                {{ $isInternal ? 'Conversations with clients, vendors and candidates.' : 'Talk directly to the SimplyHiree team.' }}
            </p>
        </div>
        <button type="button" onclick="document.getElementById('new-chat').classList.toggle('hidden')"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-extrabold text-slate-950 transition hover:bg-cyan-400 md:self-auto">
            <i class="fa-solid fa-plus"></i> New conversation
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-400/40 bg-emerald-500/15 p-3 text-sm text-emerald-100">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-rose-400/40 bg-rose-500/15 p-3 text-sm text-rose-100">{{ session('error') }}</div>
    @endif

    {{-- Start a conversation --}}
    <div id="new-chat" class="hidden mb-5 rounded-2xl border border-white/10 bg-slate-900/60 p-4">
        <form method="POST" action="{{ route($base . '.store') }}" class="space-y-3">
            @csrf
            @if($isInternal)
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <select name="party_type" required class="rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white">
                        <option value="">Who is this with?</option>
                        <option value="client">A client</option>
                        <option value="vendor">A vendor</option>
                        <option value="candidate">A candidate</option>
                    </select>
                    <input list="chat-party-list" name="party_user_id" required placeholder="Type a name, then pick"
                           class="rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white">
                    <datalist id="chat-party-list">
                        @foreach($partyOptions ?? [] as $opt)
                            <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </datalist>
                    <input type="text" name="subject" maxlength="200" placeholder="Subject (optional)"
                           class="rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white">
                </div>
            @else
                <input type="text" name="subject" maxlength="200" placeholder="Subject (optional)"
                       class="w-full rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white">
            @endif
            <textarea name="body" rows="3" required maxlength="5000" placeholder="Write your message…"
                      class="w-full rounded-xl border border-white/20 bg-slate-800 px-3 py-2.5 text-sm text-white"></textarea>
            <div class="flex justify-end">
                <button class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-extrabold text-slate-950 hover:bg-cyan-400">Send</button>
            </div>
        </form>
    </div>

    {{-- Filters (SimplyHiree side only) --}}
    @if($isInternal)
        <form method="GET" class="mb-4 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email or subject…"
                   class="min-w-[200px] flex-1 rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 text-sm text-white placeholder-slate-400">
            <select name="party_type" class="rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 text-sm text-white">
                <option value="">All parties</option>
                @foreach(\App\Models\ChatConversation::PARTY_TYPES as $k => $label)
                    <option value="{{ $k }}" @selected(request('party_type') === $k)>{{ $label }}s</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 text-sm text-white">
                <option value="">Any status</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
            </select>
            <label class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-slate-900/40 px-3 py-2 text-sm text-white">
                <input type="checkbox" name="mine" value="1" @checked(request('mine') === '1') class="rounded bg-slate-800">
                Assigned to me
            </label>
            <button class="rounded-xl bg-cyan-500 px-4 py-2 text-sm font-extrabold text-slate-950">Filter</button>
        </form>
    @endif

    {{-- Conversations --}}
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/50">
        @forelse($conversations as $c)
            @php $n = $unread[$c->id] ?? 0; @endphp
            <a href="{{ route($base . '.show', $c) }}"
               class="flex items-center gap-4 border-b border-white/5 px-5 py-4 transition hover:bg-white/5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500/25 font-black text-indigo-100">
                    {{ strtoupper(mb_substr($c->party->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-white">{{ $c->party->name ?? 'Unknown' }}</span>
                        @if($isInternal)
                            <span class="rounded-md border border-white/15 bg-white/5 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-300">
                                {{ \App\Models\ChatConversation::PARTY_TYPES[$c->party_type] ?? $c->party_type }}
                            </span>
                        @endif
                        @if($c->status === 'closed')
                            <span class="rounded-md border border-slate-400/30 bg-slate-500/15 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-300">Closed</span>
                        @endif
                        @if($n > 0)
                            <span class="rounded-full bg-cyan-500 px-2 py-0.5 text-[10px] font-black text-slate-950">{{ $n }} new</span>
                        @endif
                    </div>
                    <div class="mt-0.5 truncate text-sm text-slate-300">
                        {{ $c->subject ? $c->subject . ' — ' : '' }}{{ \Illuminate\Support\Str::limit($c->latestMessage?->body ?? 'No messages yet', 90) }}
                    </div>
                    @if($isInternal && $c->assignee)
                        <div class="mt-0.5 text-[11px] font-bold text-indigo-300"><i class="fa-solid fa-user-check mr-1"></i>{{ $c->assignee->name }}</div>
                    @endif
                </div>
                <div class="shrink-0 text-right text-[11px] text-slate-400">
                    {{ $c->last_message_at?->diffForHumans() }}
                </div>
            </a>
        @empty
            <p class="px-6 py-12 text-center text-sm text-slate-400">
                No conversations yet. Start one with the button above.
            </p>
        @endforelse
    </div>

    <div class="mt-4">{{ $conversations->links() }}</div>
</div>

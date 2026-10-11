{{--
    Floating chat launcher, bottom-right on every page.

    Kept deliberately light: one unread count and the five most recent
    threads, since this renders on every page load.
--}}
@auth
@php
    $cwUser = auth()->user();
    $cwInternal = \App\Models\ChatConversation::isInternal($cwUser);
    $cwBase = $cwInternal ? 'admin.chat'
        : ($cwUser->hasRole('client') ? 'client.chat'
        : ($cwUser->hasRole('partner') ? 'partner.chat'
        : ($cwUser->hasRole('candidate') ? 'candidate.chat' : null)));

    $cwThreads = collect();
    $cwUnread = 0;
    if ($cwBase) {
        $cwThreads = \App\Models\ChatConversation::query()
            ->visibleTo($cwUser)
            ->with(['party:id,name', 'latestMessage'])
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->limit(5)->get();
        foreach ($cwThreads as $t) {
            $cwUnread += $t->unreadCountFor($cwUser);
        }
    }
@endphp

@if($cwBase)
<div id="sh-chat" style="position:fixed;right:20px;bottom:20px;z-index:9999;font-family:'Inter','Segoe UI',system-ui,sans-serif;">

    {{-- Panel --}}
    <div id="sh-chat-panel"
         style="display:none;width:340px;max-width:calc(100vw - 40px);margin-bottom:12px;border-radius:18px;overflow:hidden;
                background:#0b1220;border:1px solid rgba(148,163,184,.22);box-shadow:0 24px 60px -12px rgba(0,0,0,.65);">

        <div style="padding:14px 16px;background:linear-gradient(135deg,#0ea5e9,#4f46e5);">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <div style="color:#fff;font-weight:800;font-size:15px;">Messages</div>
                <button type="button" onclick="shChatToggle()" aria-label="Close messages"
                        style="background:rgba(255,255,255,.18);border:0;color:#fff;border-radius:8px;width:26px;height:26px;cursor:pointer;line-height:1;">&times;</button>
            </div>
            <div style="color:rgba(255,255,255,.85);font-size:11px;margin-top:2px;">
                {{ $cwInternal ? 'Clients, vendors and candidates' : 'Talk to the SimplyHiree team' }}
            </div>
        </div>

        <div style="max-height:320px;overflow-y:auto;">
            @forelse($cwThreads as $t)
                @php $n = $t->unreadCountFor($cwUser); @endphp
                <a href="{{ route($cwBase . '.show', $t) }}"
                   style="display:flex;gap:10px;align-items:center;padding:12px 14px;border-bottom:1px solid rgba(148,163,184,.12);text-decoration:none;">
                    <span style="flex:0 0 auto;width:34px;height:34px;border-radius:50%;background:rgba(99,102,241,.25);color:#c7d2fe;
                                 display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;">
                        {{ strtoupper(mb_substr($cwInternal ? ($t->party->name ?? '?') : 'SH', 0, 1)) }}
                    </span>
                    <span style="min-width:0;flex:1;">
                        <span style="display:block;color:#fff;font-weight:700;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $cwInternal ? ($t->party->name ?? 'Unknown') : ($t->subject ?: 'SimplyHiree') }}
                        </span>
                        <span style="display:block;color:#94a3b8;font-size:11.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ \Illuminate\Support\Str::limit($t->latestMessage?->body ?? 'No messages yet', 42) }}
                        </span>
                    </span>
                    @if($n > 0)
                        <span style="flex:0 0 auto;background:#06b6d4;color:#042f2e;border-radius:999px;padding:1px 7px;font-size:10px;font-weight:900;">{{ $n }}</span>
                    @endif
                </a>
            @empty
                <div style="padding:26px 16px;text-align:center;color:#94a3b8;font-size:12.5px;">
                    No conversations yet.<br>Start one below.
                </div>
            @endforelse
        </div>

        <div style="padding:12px 14px;border-top:1px solid rgba(148,163,184,.14);display:flex;gap:8px;">
            @unless($cwInternal)
                <form method="POST" action="{{ route($cwBase . '.store') }}" style="display:flex;gap:8px;width:100%;">
                    @csrf
                    <input type="text" name="body" required maxlength="5000" placeholder="Write a message…"
                           style="flex:1;min-width:0;background:#111c31;border:1px solid rgba(148,163,184,.25);border-radius:10px;
                                  padding:9px 11px;color:#fff;font-size:12.5px;outline:none;">
                    <button type="submit" aria-label="Send"
                            style="background:#06b6d4;border:0;border-radius:10px;padding:0 13px;color:#042f2e;font-weight:900;cursor:pointer;font-size:13px;">&#10148;</button>
                </form>
            @else
                <a href="{{ route($cwBase . '.index') }}"
                   style="flex:1;text-align:center;background:#06b6d4;color:#042f2e;font-weight:800;font-size:12.5px;
                          padding:9px;border-radius:10px;text-decoration:none;">Open inbox</a>
            @endunless
        </div>

        @unless($cwInternal)
            <div style="padding:0 14px 12px;">
                <a href="{{ route($cwBase . '.index') }}" style="color:#67e8f9;font-size:11.5px;font-weight:700;text-decoration:none;">View all messages →</a>
            </div>
        @endunless
    </div>

    {{-- Launcher --}}
    <button type="button" onclick="shChatToggle()" aria-label="Open messages"
            style="position:relative;width:58px;height:58px;border-radius:50%;border:0;cursor:pointer;margin-left:auto;display:block;
                   background:linear-gradient(135deg,#0ea5e9,#4f46e5);box-shadow:0 12px 30px -6px rgba(14,165,233,.65);
                   transition:transform .18s ease;"
            onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.1"
             stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto;display:block;">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
        @if($cwUnread > 0)
            <span style="position:absolute;top:-3px;right:-3px;min-width:22px;height:22px;border-radius:999px;background:#f43f5e;
                         color:#fff;font-size:11px;font-weight:900;display:flex;align-items:center;justify-content:center;
                         padding:0 5px;border:2px solid #0b1220;">{{ $cwUnread > 9 ? '9+' : $cwUnread }}</span>
        @endif
    </button>
</div>

<script>
    function shChatToggle() {
        var p = document.getElementById('sh-chat-panel');
        if (p) p.style.display = (p.style.display === 'none' || !p.style.display) ? 'block' : 'none';
    }
</script>
@endif
@endauth

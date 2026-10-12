{{--
    Floating chat popup. The whole conversation happens in here — opening a
    thread, sending and receiving — so starting a chat never navigates away.
--}}
@auth
@php
    $cwUser = auth()->user();
    $cwInternal = \App\Models\ChatConversation::isInternal($cwUser);
    $cwBase = $cwInternal ? 'admin.chat'
        : ($cwUser->hasRole('client') ? 'client.chat'
        : ($cwUser->hasRole('partner') ? 'partner.chat'
        : ($cwUser->hasRole('candidate') ? 'candidate.chat' : null)));
@endphp

@if($cwBase)
<div id="sh-chat" style="position:fixed;right:20px;bottom:20px;z-index:9999;font-family:'Inter','Segoe UI',system-ui,sans-serif;">

  <div id="sh-panel" style="display:none;width:360px;max-width:calc(100vw - 32px);margin-bottom:12px;border-radius:18px;overflow:hidden;
       background:#0b1220;border:1px solid rgba(148,163,184,.22);box-shadow:0 24px 60px -12px rgba(0,0,0,.7);">

    <div style="padding:13px 15px;background:linear-gradient(135deg,#0ea5e9,#4f46e5);display:flex;align-items:center;gap:9px;">
      <button id="sh-back" type="button" onclick="shShowList()" title="Back"
              style="display:none;background:rgba(255,255,255,.2);border:0;color:#fff;border-radius:8px;width:26px;height:26px;cursor:pointer;">&#8592;</button>
      <div style="flex:1;min-width:0;">
        <div id="sh-title" style="color:#fff;font-weight:800;font-size:14.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Chats</div>
        <div id="sh-sub" style="color:rgba(255,255,255,.85);font-size:10.5px;">{{ $cwInternal ? 'Vendors, clients and candidates' : 'Talk to the SimplyHiree team' }}</div>
      </div>
      <button type="button" onclick="shToggle()" aria-label="Close"
              style="background:rgba(255,255,255,.18);border:0;color:#fff;border-radius:8px;width:26px;height:26px;cursor:pointer;line-height:1;">&times;</button>
    </div>

    {{-- LIST --}}
    <div id="sh-list" style="max-height:330px;overflow-y:auto;"></div>

    {{-- THREAD --}}
    <div id="sh-thread" style="display:none;">
      <div id="sh-msgs" style="height:280px;overflow-y:auto;padding:12px;background:#070e1b;"></div>
      <div style="padding:10px 12px;border-top:1px solid rgba(148,163,184,.14);display:flex;gap:8px;">
        <input id="sh-input" type="text" placeholder="Write a message…" onkeydown="if(event.key==='Enter'){event.preventDefault();shSend();}"
               style="flex:1;min-width:0;background:#111c31;border:1px solid rgba(148,163,184,.25);border-radius:10px;padding:9px 11px;color:#fff;font-size:12.5px;outline:none;">
        <button type="button" onclick="shSend()" aria-label="Send"
                style="background:#06b6d4;border:0;border-radius:10px;padding:0 13px;color:#042f2e;font-weight:900;cursor:pointer;">&#10148;</button>
      </div>
    </div>

    {{-- NEW (outside parties only) --}}
    @unless($cwInternal)
      <div id="sh-new" style="padding:10px 12px;border-top:1px solid rgba(148,163,184,.14);display:flex;gap:8px;">
        <input id="sh-new-input" type="text" placeholder="Start a new chat…" onkeydown="if(event.key==='Enter'){event.preventDefault();shStart();}"
               style="flex:1;min-width:0;background:#111c31;border:1px solid rgba(148,163,184,.25);border-radius:10px;padding:9px 11px;color:#fff;font-size:12.5px;outline:none;">
        <button type="button" onclick="shStart()" style="background:#06b6d4;border:0;border-radius:10px;padding:0 13px;color:#042f2e;font-weight:900;cursor:pointer;">&#10148;</button>
      </div>
    @endunless

    <div style="padding:9px 13px;border-top:1px solid rgba(148,163,184,.12);">
      <a href="{{ route($cwBase . '.index') }}" style="color:#67e8f9;font-size:11px;font-weight:700;text-decoration:none;">Open full chats page →</a>
    </div>
  </div>

  <button type="button" onclick="shToggle()" aria-label="Open chats"
          style="position:relative;width:58px;height:58px;border-radius:50%;border:0;cursor:pointer;margin-left:auto;display:block;
                 background:linear-gradient(135deg,#0ea5e9,#4f46e5);box-shadow:0 12px 30px -6px rgba(14,165,233,.65);transition:transform .18s;"
          onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto;display:block;">
      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
    </svg>
    <span id="sh-badge" style="display:none;position:absolute;top:-3px;right:-3px;min-width:22px;height:22px;border-radius:999px;background:#f43f5e;
                 color:#fff;font-size:11px;font-weight:900;align-items:center;justify-content:center;padding:0 5px;border:2px solid #0b1220;"></span>
  </button>
</div>

<script>
(function () {
  var BASE   = @json(url(request()->is('admin*') ? '/admin' : (request()->is('client*') ? '/client' : (request()->is('partner*') ? '/partner' : '/candidate')))),
      ROOT    = @json(rtrim(url('/'), '/')),
      PREFIX  = @json($cwInternal ? '/admin' : ($cwUser->hasRole('client') ? '/client' : ($cwUser->hasRole('partner') ? '/partner' : '/candidate'))),
      CSRF    = @json(csrf_token()),
      INTERNAL= @json($cwInternal),
      MYSIDE  = @json($cwInternal ? 'simplyhiree' : 'party');

  var open = false, current = null, lastId = 0, lastTotal = 0, firstRun = true;

  function url(p) { return ROOT + PREFIX + p; }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

  // A short two-tone chime, generated so no audio file is needed.
  function ping() {
    try {
      var Ctx = window.AudioContext || window.webkitAudioContext;
      if (!Ctx) return;
      var ctx = new Ctx();
      [880, 1180].forEach(function (f, i) {
        var o = ctx.createOscillator(), g = ctx.createGain();
        o.type = 'sine'; o.frequency.value = f;
        o.connect(g); g.connect(ctx.destination);
        var t = ctx.currentTime + i * 0.13;
        g.gain.setValueAtTime(0.0001, t);
        g.gain.exponentialRampToValueAtTime(0.16, t + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
        o.start(t); o.stop(t + 0.24);
      });
    } catch (e) {}
  }

  window.shToggle = function () {
    open = !open;
    document.getElementById('sh-panel').style.display = open ? 'block' : 'none';
    if (open) loadThreads();
  };

  window.shShowList = function () {
    current = null;
    document.getElementById('sh-thread').style.display = 'none';
    document.getElementById('sh-list').style.display = 'block';
    document.getElementById('sh-back').style.display = 'none';
    var n = document.getElementById('sh-new'); if (n) n.style.display = 'flex';
    document.getElementById('sh-title').textContent = 'Chats';
    loadThreads();
  };

  function badge(total) {
    var b = document.getElementById('sh-badge');
    if (!b) return;
    if (total > 0) { b.textContent = total > 9 ? '9+' : total; b.style.display = 'flex'; }
    else b.style.display = 'none';
  }

  function loadThreads() {
    fetch(url('/chat-threads'), { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        badge(d.unread_total);
        if (!firstRun && d.unread_total > lastTotal) ping();
        lastTotal = d.unread_total; firstRun = false;

        if (current) return;
        var el = document.getElementById('sh-list');
        if (!d.threads.length) {
          el.innerHTML = '<div style="padding:26px 16px;text-align:center;color:#94a3b8;font-size:12.5px;">No chats yet.</div>';
          return;
        }
        el.innerHTML = d.threads.map(function (t) {
          return '<div onclick="shOpen(' + t.id + ',\'' + esc(t.title).replace(/'/g, "\\'") + '\')" ' +
            'style="display:flex;gap:10px;align-items:center;padding:11px 13px;border-bottom:1px solid rgba(148,163,184,.12);cursor:pointer;">' +
            '<span style="flex:0 0 auto;width:32px;height:32px;border-radius:50%;background:rgba(99,102,241,.25);color:#c7d2fe;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">' +
              esc(t.title.charAt(0).toUpperCase()) + '</span>' +
            '<span style="min-width:0;flex:1;">' +
              '<span style="display:block;color:#fff;font-weight:700;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(t.title) +
                (INTERNAL ? ' <span style="color:#94a3b8;font-weight:600;font-size:10px;">· ' + esc(t.type) + '</span>' : '') + '</span>' +
              '<span style="display:block;color:#94a3b8;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(t.preview) + '</span>' +
            '</span>' +
            (t.unread > 0 ? '<span style="background:#06b6d4;color:#042f2e;border-radius:999px;padding:1px 7px;font-size:10px;font-weight:900;">' + t.unread + '</span>' : '') +
          '</div>';
        }).join('');
      }).catch(function () {});
  }

  window.shOpen = function (id, title) {
    current = id; lastId = 0;
    document.getElementById('sh-list').style.display = 'none';
    document.getElementById('sh-thread').style.display = 'block';
    document.getElementById('sh-back').style.display = 'block';
    var n = document.getElementById('sh-new'); if (n) n.style.display = 'none';
    document.getElementById('sh-title').textContent = title;
    document.getElementById('sh-msgs').innerHTML = '';
    pollThread(true);
  };

  function bubble(m) {
    var mine = m.side === MYSIDE;
    var files = (m.files || []).map(function (f) {
      return '<a href="' + f.url + '" target="_blank" style="display:inline-block;margin-top:4px;color:#67e8f9;font-size:11px;font-weight:700;">' + esc(f.name) + '</a>';
    }).join('');
    return '<div style="display:flex;justify-content:' + (mine ? 'flex-end' : 'flex-start') + ';margin-bottom:8px;">' +
      '<div style="max-width:78%;padding:7px 11px;border-radius:13px;background:' + (mine ? 'rgba(6,182,212,.22)' : 'rgba(255,255,255,.07)') +
      ';border:1px solid ' + (mine ? 'rgba(103,232,249,.3)' : 'rgba(148,163,184,.2)') + ';">' +
      '<div style="font-size:9.5px;font-weight:800;text-transform:uppercase;color:' + (mine ? '#a5f3fc' : '#94a3b8') + ';margin-bottom:2px;">' +
        esc(m.sender) + ' · ' + esc(m.at) + '</div>' +
      (m.body ? '<div style="color:#fff;font-size:12.5px;white-space:pre-wrap;">' + esc(m.body) + '</div>' : '') + files +
    '</div></div>';
  }

  function pollThread(initial) {
    if (!current) return;
    fetch(url('/chat/' + current + '/poll?after=' + lastId), { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var box = document.getElementById('sh-msgs'), incoming = 0;
        (d.messages || []).forEach(function (m) {
          lastId = m.id;
          if (m.side !== MYSIDE) incoming++;
          box.insertAdjacentHTML('beforeend', bubble(m));
        });
        if ((d.messages || []).length) box.scrollTop = box.scrollHeight;
        if (!initial && incoming > 0) ping();
      }).catch(function () {});
  }

  window.shSend = function () {
    var i = document.getElementById('sh-input'), body = i.value.trim();
    if (!body || !current) return;
    i.value = '';
    var fd = new FormData(); fd.append('body', body); fd.append('_token', CSRF);
    fetch(url('/chat/' + current + '/reply'), { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.message) {
          lastId = d.message.id;
          var box = document.getElementById('sh-msgs');
          box.insertAdjacentHTML('beforeend', bubble(d.message));
          box.scrollTop = box.scrollHeight;
        }
      }).catch(function () {});
  };

  window.shStart = function () {
    var i = document.getElementById('sh-new-input'), body = i.value.trim();
    if (!body) return;
    i.value = '';
    var fd = new FormData(); fd.append('body', body); fd.append('_token', CSRF);
    fetch(url('/chat'), { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.conversation_id) shOpen(d.conversation_id, 'SimplyHiree'); })
      .catch(function () {});
  };

  // Background: unread badge and chime even with the panel closed.
  loadThreads();
  setInterval(function () { current ? pollThread(false) : loadThreads(); }, 7000);
})();
</script>
@endif
@endauth

{{--
    Chat dock.

    Several conversations can be open at once, side by side along the bottom,
    each its own small window. The SimplyHiree side also gets a waiting queue
    and a cap on how many chats it will take at a time.
--}}
@auth
@php
    $cwUser = auth()->user();
    $cwInternal = \App\Models\ChatConversation::isInternal($cwUser);
    $cwPrefix = $cwInternal ? '/admin'
        : ($cwUser->hasRole('client') ? '/client'
        : ($cwUser->hasRole('partner') ? '/partner'
        : ($cwUser->hasRole('candidate') ? '/candidate' : null)));
    $cwIndex = $cwInternal ? 'admin.chat.index'
        : ($cwUser->hasRole('client') ? 'client.chat.index'
        : ($cwUser->hasRole('partner') ? 'partner.chat.index'
        : ($cwUser->hasRole('candidate') ? 'candidate.chat.index' : null)));
@endphp

@if($cwPrefix)
<div id="shd" style="position:fixed;right:18px;bottom:18px;z-index:9999;display:flex;align-items:flex-end;gap:10px;
                     font-family:'Inter','Segoe UI',system-ui,sans-serif;">
  <div id="shd-windows" style="display:flex;align-items:flex-end;gap:10px;"></div>

  <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px;">
    <div id="shd-list" style="display:none;width:340px;max-width:calc(100vw - 36px);border-radius:16px;overflow:hidden;
         background:#0b1220;border:1px solid rgba(148,163,184,.22);box-shadow:0 24px 60px -12px rgba(0,0,0,.7);"></div>

    <button type="button" onclick="shdToggle()" aria-label="Open chats"
            style="position:relative;width:56px;height:56px;border-radius:50%;border:0;cursor:pointer;
                   background:linear-gradient(135deg,#0ea5e9,#4f46e5);box-shadow:0 12px 30px -6px rgba(14,165,233,.6);">
      <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto;display:block;">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
      </svg>
      <span id="shd-badge" style="display:none;position:absolute;top:-3px;right:-3px;min-width:21px;height:21px;border-radius:999px;
            background:#f43f5e;color:#fff;font-size:10.5px;font-weight:900;align-items:center;justify-content:center;padding:0 5px;border:2px solid #0b1220;"></span>
    </button>
  </div>
</div>

<script>
(function () {
  var ROOT = @json(rtrim(url('/'), '/')),
      PREFIX = @json($cwPrefix),
      CSRF = @json(csrf_token()),
      INTERNAL = @json($cwInternal),
      MYSIDE = @json($cwInternal ? 'simplyhiree' : 'party'),
      SUPER = @json($cwUser->hasRole('Superadmin')),
      FULL = @json(route($cwIndex));

  var listOpen = false,
      windows = {},          // id -> { lastId, seen, minimised, title }
      capacity = 5, active = 0, canClaim = true,
      lastTotal = 0, firstRun = true;

  function u(p) { return ROOT + PREFIX + p; }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

  // ---- sound -------------------------------------------------------------
  var ctx = null;
  function getCtx() {
    var C = window.AudioContext || window.webkitAudioContext;
    if (!C) return null;
    if (!ctx) { try { ctx = new C(); } catch (e) { return null; } }
    return ctx;
  }
  function unlock() { var c = getCtx(); if (c && c.state === 'suspended') c.resume().catch(function(){}); }
  ['click','keydown','touchstart'].forEach(function (e) { document.addEventListener(e, unlock, { passive: true }); });

  function ping() {
    var c = getCtx(); if (!c) return;
    if (c.state === 'suspended') c.resume().catch(function(){});
    try {
      [880, 1180].forEach(function (f, i) {
        var o = c.createOscillator(), g = c.createGain();
        o.type = 'sine'; o.frequency.value = f; o.connect(g); g.connect(c.destination);
        var t = c.currentTime + i * 0.13;
        g.gain.setValueAtTime(0.0001, t);
        g.gain.exponentialRampToValueAtTime(0.18, t + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
        o.start(t); o.stop(t + 0.24);
      });
    } catch (e) {}
    flash();
  }
  var tTimer = null, tReal = document.title;
  function flash() {
    if (tTimer) return;
    var on = false;
    tTimer = setInterval(function () { document.title = on ? tReal : '● New message'; on = !on; }, 900);
    setTimeout(function () { clearInterval(tTimer); tTimer = null; document.title = tReal; }, 9000);
  }
  window.addEventListener('focus', function () { if (tTimer) { clearInterval(tTimer); tTimer = null; document.title = tReal; } });
  window.shdTestSound = function (e) { if (e) e.preventDefault(); unlock(); ping(); };

  // ---- list panel --------------------------------------------------------
  window.shdToggle = function () {
    listOpen = !listOpen;
    document.getElementById('shd-list').style.display = listOpen ? 'block' : 'none';
    if (listOpen) refresh();
  };

  function row(t, claimable) {
    return '<div style="display:flex;gap:9px;align-items:center;padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.12);">' +
      '<span onclick="' + (claimable ? 'shdClaim' : 'shdOpen') + '(' + t.id + ',\'' + esc(t.title).replace(/'/g,"\\'") + '\')" style="flex:1;min-width:0;cursor:pointer;">' +
        '<span style="display:block;color:#fff;font-weight:700;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
          esc(t.title) + (INTERNAL ? ' <span style="color:#94a3b8;font-size:10px;font-weight:600;">· ' + esc(t.type) + '</span>' : '') + '</span>' +
        '<span style="display:block;color:#94a3b8;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(t.preview) + '</span>' +
        (claimable && t.waiting ? '<span style="display:block;color:#fbbf24;font-size:10px;font-weight:700;margin-top:1px;">waiting ' + esc(t.waiting) + '</span>' : '') +
      '</span>' +
      (t.unread > 0 ? '<span style="background:#06b6d4;color:#042f2e;border-radius:999px;padding:1px 7px;font-size:10px;font-weight:900;">' + t.unread + '</span>' : '') +
      (claimable ? '<button onclick="shdClaim(' + t.id + ',\'' + esc(t.title).replace(/'/g,"\\'") + '\')" ' +
          'style="background:' + (canClaim ? '#06b6d4' : '#334155') + ';color:' + (canClaim ? '#042f2e' : '#94a3b8') +
          ';border:0;border-radius:8px;padding:4px 9px;font-size:10.5px;font-weight:900;cursor:pointer;">Take</button>' : '') +
    '</div>';
  }

  function refresh() {
    fetch(u('/chat-threads'), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        capacity = d.capacity; active = d.active; canClaim = d.can_claim;

        var b = document.getElementById('shd-badge');
        if (d.unread_total > 0) { b.textContent = d.unread_total > 9 ? '9+' : d.unread_total; b.style.display = 'flex'; }
        else b.style.display = 'none';
        if (!firstRun && d.unread_total > lastTotal) ping();
        lastTotal = d.unread_total; firstRun = false;

        if (!listOpen) return;
        var h = '<div style="padding:12px 14px;background:linear-gradient(135deg,#0ea5e9,#4f46e5);display:flex;align-items:center;justify-content:space-between;">' +
            '<span style="color:#fff;font-weight:800;font-size:14px;">Chats</span>' +
            '<button onclick="shdToggle()" style="background:rgba(255,255,255,.18);border:0;color:#fff;border-radius:8px;width:24px;height:24px;cursor:pointer;">&times;</button></div>';

        if (INTERNAL) {
          h += '<div style="padding:9px 13px;background:rgba(255,255,255,.04);border-bottom:1px solid rgba(148,163,184,.14);' +
               'display:flex;align-items:center;justify-content:space-between;gap:8px;">' +
               '<span style="color:' + (canClaim ? '#a5f3fc' : '#fbbf24') + ';font-size:11px;font-weight:800;">Handling ' + active + ' of ' + capacity + '</span>' +
               '<span style="display:flex;align-items:center;gap:5px;">' +
                 '<span style="color:#94a3b8;font-size:10.5px;">Max</span>' +
                 '<input id="shd-cap" type="number" min="1" max="20" value="' + capacity + '" onchange="shdCap(this.value)" ' +
                   'style="width:46px;background:#111c31;border:1px solid rgba(148,163,184,.25);border-radius:7px;padding:3px 5px;color:#fff;font-size:11px;">' +
               '</span></div>';
        }

        h += '<div style="max-height:300px;overflow-y:auto;">';
        h += (d.threads.length ? d.threads.map(function (t) { return row(t, false); }).join('')
                               : '<div style="padding:18px;text-align:center;color:#94a3b8;font-size:12px;">No chats assigned to you.</div>');

        if (INTERNAL && d.queue.length) {
          h += '<div style="padding:8px 13px;background:rgba(251,191,36,.08);border-top:1px solid rgba(251,191,36,.2);' +
               'color:#fbbf24;font-size:10.5px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;">Waiting · ' + d.queue.length + '</div>';
          h += d.queue.map(function (t) { return row(t, true); }).join('');
        }
        h += '</div>';

        h += '<div style="padding:9px 13px;border-top:1px solid rgba(148,163,184,.12);display:flex;justify-content:space-between;">' +
             '<a href="' + FULL + '" style="color:#67e8f9;font-size:11px;font-weight:700;text-decoration:none;">Full chats page →</a>' +
             '<a href="#" onclick="shdTestSound(event)" style="color:#94a3b8;font-size:11px;font-weight:700;text-decoration:none;">🔊 Test sound</a></div>';

        document.getElementById('shd-list').innerHTML = h;
      }).catch(function () {});
  }

  window.shdCap = function (v) {
    var fd = new FormData(); fd.append('capacity', v); fd.append('_token', CSRF);
    fetch(u('/chat-capacity'), { method: 'POST', body: fd, headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); }).then(refresh).catch(function () {});
  };

  window.shdClaim = function (id, title) {
    var fd = new FormData(); fd.append('_token', CSRF);
    fetch(u('/chat/' + id + '/claim'), { method: 'POST', body: fd, headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (!res.ok) { alert(res.d.message || 'Could not take this chat.'); refresh(); return; }
        shdOpen(id, title); refresh();
      }).catch(function () {});
  };

  // ---- chat windows ------------------------------------------------------
  window.shdOpen = function (id, title) {
    if (windows[id]) { windows[id].minimised = false; paint(id); return; }
    windows[id] = { lastId: 0, seen: Object.create(null), minimised: false, title: title };

    var el = document.createElement('div');
    el.id = 'shw-' + id;
    el.style.cssText = 'width:290px;border-radius:14px 14px 0 0;overflow:hidden;background:#0b1220;' +
      'border:1px solid rgba(148,163,184,.22);box-shadow:0 18px 44px -10px rgba(0,0,0,.7);';
    el.innerHTML =
      '<div style="padding:9px 11px;background:linear-gradient(135deg,#0ea5e9,#4f46e5);display:flex;align-items:center;gap:7px;cursor:pointer;" onclick="shdMin(' + id + ')">' +
        '<span style="flex:1;min-width:0;color:#fff;font-weight:800;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + esc(title) + '</span>' +
        (INTERNAL ? '<button onclick="event.stopPropagation();shdMenu(' + id + ')" title="Chat actions" style="background:rgba(255,255,255,.18);border:0;color:#fff;border-radius:6px;padding:2px 7px;font-size:12px;font-weight:800;cursor:pointer;line-height:1;">&#8942;</button>' : '') +
        '<button onclick="event.stopPropagation();shdClose(' + id + ')" aria-label="Hide this window" title="Hide window (the chat stays open)" style="background:rgba(255,255,255,.18);border:0;color:#fff;border-radius:6px;width:21px;height:21px;cursor:pointer;line-height:1;">&times;</button>' +
      '</div>' +
      '<div id="shmenu-' + id + '" style="display:none;background:#111c31;border-bottom:1px solid rgba(148,163,184,.18);padding:7px;">' +
        '<button onclick="shdResolve(' + id + ')" style="display:block;width:100%;text-align:left;background:none;border:0;color:#cbd5e1;font-size:11.5px;font-weight:700;padding:6px 8px;cursor:pointer;border-radius:6px;">&#10003; Mark resolved</button>' +
        '<button onclick="shdRelease(' + id + ')" style="display:block;width:100%;text-align:left;background:none;border:0;color:#cbd5e1;font-size:11.5px;font-weight:700;padding:6px 8px;cursor:pointer;border-radius:6px;">&#8631; Return to queue</button>' +
        '<button onclick="shdArchive(' + id + ')" style="display:block;width:100%;text-align:left;background:none;border:0;color:#fcd34d;font-size:11.5px;font-weight:700;padding:6px 8px;cursor:pointer;border-radius:6px;">&#128230; Archive</button>' +
        (SUPER ? '<button onclick="shdDelete(' + id + ')" style="display:block;width:100%;text-align:left;background:none;border:0;color:#fda4af;font-size:11.5px;font-weight:700;padding:6px 8px;cursor:pointer;border-radius:6px;">&#128465; Delete permanently</button>' : '') +
      '</div>' +
      '<div class="shw-body">' +
        '<div id="shm-' + id + '" style="height:240px;overflow-y:auto;padding:10px;background:#070e1b;"></div>' +
        '<div style="padding:8px 10px;border-top:1px solid rgba(148,163,184,.14);">' +
          '<div style="display:flex;gap:6px;align-items:center;">' +
            '<label title="Attach files" style="flex:0 0 auto;cursor:pointer;background:rgba(148,163,184,.15);border-radius:8px;padding:7px 9px;color:#cbd5e1;font-size:12px;">' +
              '&#128206;<input type="file" multiple style="display:none" onchange="shdFiles(' + id + ',this)"></label>' +
            '<input id="shi-' + id + '" type="text" placeholder="Message…" onkeydown="if(event.key===\'Enter\'){event.preventDefault();shdSend(' + id + ');}" ' +
              'style="flex:1;min-width:0;background:#111c31;border:1px solid rgba(148,163,184,.25);border-radius:9px;padding:8px 10px;color:#fff;font-size:12px;outline:none;">' +
            '<button onclick="shdSend(' + id + ')" style="background:#06b6d4;border:0;border-radius:9px;padding:0 11px;color:#042f2e;font-weight:900;cursor:pointer;">&#10148;</button>' +
          '</div>' +
          '<div id="shf-' + id + '" style="color:#6ee7b7;font-size:10px;font-weight:700;margin-top:4px;"></div>' +
        '</div>' +
      '</div>';
    document.getElementById('shd-windows').appendChild(el);
    pollWin(id, true);
  };

  window.shdMin = function (id) {
    var w = windows[id]; if (!w) return;
    w.minimised = !w.minimised; paint(id);
  };
  function paint(id) {
    var el = document.getElementById('shw-' + id); if (!el) return;
    var body = el.querySelector('.shw-body');
    if (body) body.style.display = windows[id].minimised ? 'none' : 'block';
  }
  window.shdClose = function (id) {
    var el = document.getElementById('shw-' + id);
    if (el) el.remove();
    delete windows[id];
  };
  window.shdRelease = function (id) {
    var fd = new FormData(); fd.append('_token', CSRF);
    fetch(u('/chat/' + id + '/release'), { method: 'POST', body: fd, headers: { Accept: 'application/json' } })
      .then(function () { shdClose(id); refresh(); }).catch(function () {});
  };

  window.shdMenu = function (id) {
    var m = document.getElementById('shmenu-' + id);
    if (m) m.style.display = m.style.display === 'none' ? 'block' : 'none';
  };

  function act(id, path, method, after) {
    var fd = new FormData(); fd.append('_token', CSRF);
    if (method) fd.append('_method', method);
    fetch(u('/chat/' + id + path), { method: 'POST', body: fd, headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json().catch(function () { return {}; }); })
      .then(function (d) { if (after) after(d); refresh(); })
      .catch(function () {});
  }

  window.shdResolve = function (id) { act(id, '/status', null, function () { shdMenu(id); }); };
  window.shdArchive = function (id) {
    if (!confirm('Archive this chat? It leaves the active list but every message is kept.')) return;
    act(id, '/archive', null, function () { shdClose(id); });
  };
  window.shdDelete = function (id) {
    if (!confirm('Delete this chat and every message in it permanently? Archiving keeps the history; this does not.')) return;
    act(id, '', 'DELETE', function () { shdClose(id); });
  };

  var pending = {};   // id -> FileList
  window.shdFiles = function (id, input) {
    pending[id] = input.files;
    var n = input.files.length;
    document.getElementById('shf-' + id).textContent = n ? (n + ' file' + (n > 1 ? 's' : '') + ' ready') : '';
  };

  function bubble(m) {
    var mine = m.side === MYSIDE;
    var files = (m.files || []).map(function (f) {
      return '<a href="' + f.url + '" target="_blank" style="display:block;margin-top:3px;color:#67e8f9;font-size:10.5px;font-weight:700;">&#128206; ' + esc(f.name) + '</a>';
    }).join('');
    return '<div style="display:flex;justify-content:' + (mine ? 'flex-end' : 'flex-start') + ';margin-bottom:7px;">' +
      '<div style="max-width:80%;padding:6px 10px;border-radius:12px;background:' + (mine ? 'rgba(6,182,212,.22)' : 'rgba(255,255,255,.07)') +
      ';border:1px solid ' + (mine ? 'rgba(103,232,249,.3)' : 'rgba(148,163,184,.2)') + ';">' +
      '<div style="font-size:9px;font-weight:800;text-transform:uppercase;color:' + (mine ? '#a5f3fc' : '#94a3b8') + ';margin-bottom:2px;">' + esc(m.sender) + ' · ' + esc(m.at) + '</div>' +
      (m.body ? '<div style="color:#fff;font-size:12px;white-space:pre-wrap;">' + esc(m.body) + '</div>' : '') + files + '</div></div>';
  }

  function render(id, m) {
    var w = windows[id]; if (!w || w.seen[m.id]) return false;
    w.seen[m.id] = true;
    if (m.id > w.lastId) w.lastId = m.id;
    var box = document.getElementById('shm-' + id);
    if (box) box.insertAdjacentHTML('beforeend', bubble(m));
    return true;
  }

  function pollWin(id, initial) {
    var w = windows[id]; if (!w) return;
    fetch(u('/chat/' + id + '/poll?after=' + w.lastId), { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var added = 0, incoming = 0;
        (d.messages || []).forEach(function (m) {
          if (!render(id, m)) return;
          added++; if (m.side !== MYSIDE) incoming++;
        });
        var box = document.getElementById('shm-' + id);
        if (added && box) box.scrollTop = box.scrollHeight;
        if (!initial && incoming) ping();
      }).catch(function () {});
  }

  window.shdSend = function (id) {
    var i = document.getElementById('shi-' + id);
    var body = i ? i.value.trim() : '';
    var files = pending[id];
    if (!body && (!files || !files.length)) return;

    var fd = new FormData();
    fd.append('_token', CSRF);
    if (body) fd.append('body', body);
    if (files) for (var k = 0; k < files.length; k++) fd.append('attachments[]', files[k]);

    if (i) i.value = '';
    pending[id] = null;
    var fl = document.getElementById('shf-' + id); if (fl) fl.textContent = '';

    fetch(u('/chat/' + id + '/reply'), { method: 'POST', body: fd, headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.message && render(id, d.message)) {
          var box = document.getElementById('shm-' + id);
          if (box) box.scrollTop = box.scrollHeight;
        }
      }).catch(function () {});
  };

  refresh();
  setInterval(function () {
    refresh();
    Object.keys(windows).forEach(function (id) { pollWin(id, false); });
  }, 7000);
})();
</script>
@endif
@endauth

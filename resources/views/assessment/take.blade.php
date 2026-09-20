<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stage {{ $stage->stage_order }} · {{ $assessment->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; background:linear-gradient(135deg,#0f172a,#1e1b4b 60%,#312e81);
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif; color:#e2e8f0; }
        .wrap { max-width:760px; margin:0 auto; padding:20px 16px 120px; }
        .topbar { position:sticky; top:0; z-index:20; background:rgba(15,23,42,.92); backdrop-filter:blur(8px);
            border:1px solid rgba(148,163,184,.18); border-radius:14px; padding:12px 16px; margin-bottom:18px;
            display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .topbar .info { min-width:0; }
        .topbar .stage { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:#a5b4fc; font-weight:800; }
        .topbar .name { color:#fff; font-weight:700; font-size:.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .timer { flex:0 0 auto; font-variant-numeric:tabular-nums; font-weight:800; font-size:1.15rem; color:#fff;
            background:rgba(99,102,241,.25); border:1px solid rgba(129,140,248,.4); border-radius:10px; padding:8px 14px; }
        .timer.warn { background:rgba(244,63,94,.22); border-color:rgba(244,63,94,.5); color:#fecaca; }
        .q { background:rgba(15,23,42,.6); border:1px solid rgba(148,163,184,.18); border-radius:16px; padding:18px 18px 8px; margin-bottom:16px; }
        .q .qnum { font-size:.72rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
        .q .qtext { color:#fff; font-weight:600; font-size:1.02rem; margin:6px 0 14px; line-height:1.5; }
        .opt { display:flex; align-items:center; gap:12px; padding:12px 14px; border:1px solid rgba(148,163,184,.25);
            border-radius:12px; margin-bottom:10px; cursor:pointer; transition:border-color .12s, background .12s; }
        .opt:hover { border-color:#818cf8; }
        .opt.sel { border-color:#818cf8; background:rgba(99,102,241,.16); }
        .opt input { accent-color:#6366f1; width:18px; height:18px; flex:0 0 auto; }
        .opt span { color:#e2e8f0; font-size:.95rem; }
        .savebar { position:fixed; left:0; right:0; bottom:0; background:rgba(2,6,23,.92); backdrop-filter:blur(10px);
            border-top:1px solid rgba(148,163,184,.18); padding:14px 16px; }
        .savebar .inner { max-width:760px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .progress { font-size:.82rem; color:#94a3b8; }
        .btn { border:none; border-radius:12px; font-weight:800; font-size:1rem; padding:13px 26px; color:#fff; cursor:pointer;
            background:linear-gradient(135deg,#4f46e5,#06b6d4); }
        .btn:hover { filter:brightness(1.08); }
        .warnnote { color:#fca5a5; font-size:.78rem; margin-top:8px; display:none; }
        .brand { display:flex; align-items:center; gap:8px; margin-bottom:14px; color:#fff; font-weight:800; }
        .brand .logo { width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#4f46e5,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:.8rem;}
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand"><div class="logo">SH</div> SimplyHiree Assessment</div>

    <div class="topbar">
        <div class="info">
            <div class="stage">Stage {{ $stage->stage_order }} of {{ $totalStages }}</div>
            <div class="name">{{ $assessment->name }} · pass {{ $assessment->passing_percentage }}%</div>
        </div>
        @if($remaining !== null)
            <div id="timer" class="timer" data-remaining="{{ $remaining }}">--:--</div>
        @else
            <div class="timer">No limit</div>
        @endif
    </div>

    <form id="test-form" method="POST" action="{{ route('assessment.stage.submit', $session->token) }}">
        @csrf
        @foreach($questions as $i => $q)
            <div class="q" data-qid="{{ $q->id }}">
                <div class="qnum">Question {{ $i + 1 }} of {{ $questions->count() }} · {{ $q->marks }} mark{{ $q->marks == 1 ? '' : 's' }}</div>
                <div class="qtext">{{ $q->question_text }}</div>
                @foreach($q->options as $opt)
                    @php $checked = (int) ($answers[$q->id] ?? 0) === $opt->id; @endphp
                    <label class="opt {{ $checked ? 'sel' : '' }}">
                        <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}" {{ $checked ? 'checked' : '' }}>
                        <span>{{ $opt->option_text }}</span>
                    </label>
                @endforeach
            </div>
        @endforeach
    </form>

    <div class="warnnote" id="focuswarn">Leaving the test window is recorded. Please stay on this page.</div>
</div>

<div class="savebar">
    <div class="inner">
        <div class="progress"><span id="answered">0</span> / {{ $questions->count() }} answered <span id="savestate" style="color:#67e8f9;"></span></div>
        <button type="submit" form="test-form" class="btn" id="submitbtn">Submit assessment</button>
    </div>
</div>

<script>
(function () {
    const token = @json($session->token);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const answerUrl = @json(route('assessment.stage.answer', $session->token));
    const focusUrl  = @json(route('assessment.stage.focus', $session->token));
    const overviewUrl = @json(route('assessment.overview', $session->token));
    const total = {{ $questions->count() }};
    const form = document.getElementById('test-form');

    function updateAnswered() {
        const set = new Set();
        form.querySelectorAll('input[type=radio]:checked').forEach(r => set.add(r.name));
        document.getElementById('answered').textContent = set.size;
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body || {})
        });
    }

    // Autosave on selection.
    form.addEventListener('change', function (e) {
        if (e.target.type !== 'radio') return;
        const q = e.target.closest('.q');
        q.querySelectorAll('.opt').forEach(o => o.classList.remove('sel'));
        e.target.closest('.opt').classList.add('sel');
        updateAnswered();

        const state = document.getElementById('savestate');
        state.textContent = '· saving…';
        post(answerUrl, { question_id: parseInt(q.dataset.qid, 10), option_id: parseInt(e.target.value, 10) })
            .then(r => r.json().catch(() => ({})).then(j => ({ status: r.status, j })))
            .then(({ status, j }) => {
                if (status === 409 && j && j.redirect) { window.location = j.redirect; return; }
                state.textContent = '· saved';
                setTimeout(() => { if (state.textContent === '· saved') state.textContent = ''; }, 1200);
            })
            .catch(() => { state.textContent = '· offline'; });
    });

    // Prevent accidental double submit; allow auto-submit.
    let submitting = false;
    form.addEventListener('submit', function () { submitting = true; document.getElementById('submitbtn').disabled = true; });

    // Countdown + auto-submit.
    const timerEl = document.getElementById('timer');
    if (timerEl) {
        let remaining = parseInt(timerEl.dataset.remaining, 10) || 0;
        const render = () => {
            const m = Math.floor(remaining / 60), s = remaining % 60;
            timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            timerEl.classList.toggle('warn', remaining <= 60);
        };
        render();
        const iv = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(iv);
                timerEl.textContent = '00:00';
                if (!submitting) form.requestSubmit();
                return;
            }
            render();
        }, 1000);
    }

    // Basic anti-cheat: record when the candidate leaves the tab/window.
    let lastFocusPing = 0;
    function reportFocusLoss() {
        const now = Date.now();
        if (now - lastFocusPing < 1500) return; // debounce
        lastFocusPing = now;
        document.getElementById('focuswarn').style.display = 'block';
        post(focusUrl, {}).catch(() => {});
    }
    document.addEventListener('visibilitychange', function () { if (document.hidden) reportFocusLoss(); });
    window.addEventListener('blur', reportFocusLoss);

    updateAnswered();
})();
</script>
</body>
</html>

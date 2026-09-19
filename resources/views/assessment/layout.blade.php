<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Assessment') · SimplyHiree</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
            color: #e2e8f0; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
        }
        .card {
            width: 100%; max-width: 460px; background: rgba(15,23,42,.72);
            border: 1px solid rgba(148,163,184,.18); border-radius: 20px;
            padding: 32px 28px; box-shadow: 0 24px 60px rgba(0,0,0,.45); backdrop-filter: blur(8px);
        }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; }
        .brand .logo {
            width: 40px; height: 40px; border-radius: 11px; font-weight: 800; color: #fff;
            background: linear-gradient(135deg,#4f46e5,#06b6d4); display: flex; align-items: center; justify-content: center;
        }
        .brand span { font-weight: 800; color: #fff; font-size: 1.05rem; letter-spacing: -.01em; }
        h1 { font-size: 1.35rem; margin: 0 0 6px; color: #fff; font-weight: 800; }
        p.lead { margin: 0 0 20px; color: #cbd5e1; font-size: .93rem; line-height: 1.55; }
        .job-chip {
            display: inline-block; font-size: .78rem; font-weight: 700; color: #a5b4fc;
            background: rgba(99,102,241,.16); border: 1px solid rgba(99,102,241,.3);
            padding: 5px 12px; border-radius: 999px; margin-bottom: 18px;
        }
        label { display: block; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: 7px; }
        input[type=text], input[type=tel] {
            width: 100%; padding: 13px 15px; border-radius: 12px; border: 1px solid rgba(148,163,184,.3);
            background: rgba(2,6,23,.6); color: #fff; font-size: 1.4rem; letter-spacing: .5em; text-align: center; font-weight: 700;
        }
        input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(129,140,248,.25); }
        .btn {
            display: block; width: 100%; margin-top: 18px; padding: 13px 16px; border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 800; cursor: pointer; color: #fff;
            background: linear-gradient(135deg,#4f46e5,#06b6d4); transition: filter .15s;
        }
        .btn:hover { filter: brightness(1.08); }
        .btn-link { background: none; border: none; color: #67e8f9; font-weight: 700; cursor: pointer; font-size: .88rem; padding: 8px; margin-top: 12px; width: 100%; }
        .alert { border-radius: 11px; padding: 11px 14px; font-size: .86rem; margin-bottom: 16px; }
        .alert-error { background: rgba(244,63,94,.14); border: 1px solid rgba(244,63,94,.35); color: #fecaca; }
        .alert-info  { background: rgba(56,189,248,.12); border: 1px solid rgba(56,189,248,.32); color: #bae6fd; }
        .muted { color: #94a3b8; font-size: .82rem; margin-top: 16px; text-align: center; line-height: 1.5; }
        .foot { text-align: center; margin-top: 22px; font-size: .74rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="logo">SH</div>
            <span>SimplyHiree</span>
        </div>
        @yield('content')
        <div class="foot">Secured by SimplyHiree · Do not share this link or code with anyone.</div>
    </div>
</body>
</html>

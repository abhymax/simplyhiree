<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->hero_headline }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: {{ $page->primary_color ?: '#10b981' }};
            --secondary: {{ $page->secondary_color ?: '#84cc16' }};
            --bg: #000000;
            --bg-2: #0a0f0d;
            --card: #0f1614;
            --card-2: #111a17;
            --line: rgba(255,255,255,0.08);
            --text: #e5e7eb;
            --muted: #9ca3af;
            --accent: #22c55e;
            --warn: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; font-size: 16px; padding-bottom: 88px; }
        img { max-width: 100%; display: block; }
        a { color: var(--primary); text-decoration: none; }

        /* Top countdown bar */
        .topbar { background: #141c1a; border-bottom: 1px solid rgba(16,185,129,0.15); padding: 14px 20px; display: flex; justify-content: center; align-items: center; gap: 18px; flex-wrap: wrap; position: sticky; top: 0; z-index: 40; box-shadow: 0 4px 18px rgba(0,0,0,0.5), inset 0 -1px 0 rgba(255,255,255,0.02); }
        .live-dot { width: 14px; height: 14px; border-radius: 50%; background: #ef4444; position: relative; flex-shrink: 0; }
        .live-dot::after { content: ''; position: absolute; inset: -6px; border-radius: 50%; background: rgba(239,68,68,0.35); animation: pulse 1.6s ease-out infinite; }
        @keyframes pulse { 0%{transform:scale(0.6);opacity:0.9} 100%{transform:scale(1.6);opacity:0} }
        .topbar-text { font-size: 14px; font-weight: 700; color: #ef4444; letter-spacing: 0.5px; text-transform: uppercase; }
        .cd-row { display: inline-flex; gap: 6px; }
        .cd-cell { background: #111; border: 1px solid rgba(239,68,68,0.35); color: #ef4444; padding: 6px 10px; border-radius: 8px; min-width: 42px; text-align: center; font-weight: 800; font-size: 16px; font-variant-numeric: tabular-nums; }

        /* Background ambience */
        body::before { content: ''; position: fixed; inset: 0; background: radial-gradient(800px 400px at 20% 10%, rgba(16,185,129,0.08), transparent 60%), radial-gradient(700px 400px at 80% 30%, rgba(132,204,22,0.05), transparent 60%); pointer-events: none; z-index: 0; }

        .wrap { max-width: 1180px; margin: 0 auto; padding: 0 24px; position: relative; z-index: 1; }

        /* Hero */
        .hero { padding: 48px 0 24px; text-align: center; }
        .hero-logo { max-height: 52px; margin: 0 auto 24px; }
        .hero h1 { font-size: clamp(28px, 4vw, 48px); font-weight: 800; line-height: 1.15; max-width: 960px; margin: 0 auto 18px; color: #fff; letter-spacing: -0.5px; }
        .hero h1 em { color: var(--primary); font-style: normal; }
        .hero .sub { font-size: 17px; color: var(--muted); max-width: 840px; margin: 0 auto; line-height: 1.7; }
        .hero .sub strong { color: var(--primary); font-weight: 600; text-decoration: underline; text-underline-offset: 4px; }

        /* Main content grid */
        .main-grid { padding: 40px 0 20px; display: grid; grid-template-columns: 1.1fr 1fr; gap: 40px; align-items: center; }

        /* Video */
        .video-wrap { position: relative; padding-top: 56.25%; border-radius: 16px; overflow: hidden; background: #000; border: 1px solid var(--line); box-shadow: 0 30px 80px -30px rgba(16,185,129,0.2); }
        .video-wrap iframe, .video-wrap video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

        /* Host caption below video */
        .host-caption { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 16px 20px; text-align: center; margin-top: 14px; }
        .host-caption-name { font-size: 20px; font-weight: 800; color: var(--primary); margin-bottom: 2px; letter-spacing: -0.2px; }
        .host-caption-title { font-size: 14px; color: var(--text); font-weight: 500; }

        /* Right panel */
        .panel-title { text-align: center; font-size: 18px; font-weight: 600; color: var(--primary); margin-bottom: 20px; position: relative; padding: 0 20px; }
        .panel-title::before, .panel-title::after { content: ''; position: absolute; top: 50%; width: 60px; height: 2px; background: linear-gradient(90deg, transparent, rgba(16,185,129,0.4)); }
        .panel-title::before { left: -40px; }
        .panel-title::after { right: -40px; transform: rotate(180deg); }

        .event-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
        .event-card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 18px; display: flex; align-items: center; gap: 14px; }
        .event-ic { width: 44px; height: 44px; border-radius: 10px; background: rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .event-ic svg { width: 22px; height: 22px; color: var(--text); }
        .event-lbl { font-size: 11px; color: var(--primary); font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2px; }
        .event-val { font-size: 15px; font-weight: 700; color: #fff; }

        /* CTA button (gradient) */
        .btn-grad { display: block; width: 100%; background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%); color: #052e1a; border: none; padding: 16px 22px; border-radius: 12px; font-size: 17px; font-weight: 800; text-align: center; text-decoration: none; cursor: pointer; font-family: inherit; letter-spacing: 0.3px; transition: transform 0.15s, box-shadow 0.2s, filter 0.2s; box-shadow: 0 10px 30px -10px rgba(16,185,129,0.5); }
        .btn-grad:hover { transform: translateY(-2px); filter: brightness(1.08); box-shadow: 0 18px 40px -10px rgba(16,185,129,0.55); }
        .btn-grad:active { transform: translateY(0); }
        .btn-sm { padding: 10px 20px; font-size: 14px; border-radius: 10px; }

        .seats-note { text-align: center; margin-top: 16px; font-size: 20px; color: #fff; font-weight: 600; }
        .seats-note span { color: var(--warn); font-weight: 800; display: inline-block; animation: seatPulse 1.2s ease-in-out infinite; }
        @keyframes seatPulse { 0%,100% { opacity: 1; transform: scale(1); text-shadow: 0 0 0 rgba(239,68,68,0); } 50% { opacity: 0.85; transform: scale(1.1); text-shadow: 0 0 16px rgba(239,68,68,0.6); } }

        /* Inline CTA block between sections */
        .cta-block { text-align: center; padding: 20px 0 10px; }
        .cta-block .btn-grad { display: inline-block; width: auto; min-width: 340px; padding: 20px 56px; font-size: 20px; border-radius: 14px; text-decoration: none; letter-spacing: 0.4px; }
        @media (max-width: 520px) {
            .cta-block .btn-grad { min-width: 0; width: 100%; padding: 16px 22px; font-size: 17px; }
            .seats-note { font-size: 17px; }
        }

        /* Registration form modal/inline */
        .reg-section { padding: 60px 0; }
        .reg-box { max-width: 520px; margin: 0 auto; background: var(--card); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
        .reg-head { background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(132,204,22,0.05)); border-bottom: 1px solid var(--line); padding: 22px; text-align: center; }
        .reg-head h3 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px; }
        .reg-head p { font-size: 14px; color: var(--muted); }
        .reg-body { padding: 24px; }
        .fld { width: 100%; padding: 13px 15px; background: var(--bg-2); border: 1px solid var(--line); border-radius: 10px; font-family: inherit; font-size: 14px; color: #fff; margin-bottom: 12px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .fld::placeholder { color: #6b7280; }
        .fld:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
        .safe { text-align: center; font-size: 12px; color: var(--muted); margin-top: 12px; }
        .success-box { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); padding: 22px; border-radius: 12px; text-align: center; }
        .success-box h4 { color: var(--primary); font-size: 18px; margin-bottom: 4px; }
        .success-box p { color: var(--text); font-size: 14px; }
        .err-box { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }

        /* Sections */
        section.s { padding: 64px 0; border-top: 1px solid var(--line); }
        .eyebrow { display: inline-block; color: var(--primary); font-weight: 700; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px; }
        h2.sec-title { font-size: clamp(26px, 3vw, 38px); font-weight: 800; line-height: 1.25; text-align: center; margin-bottom: 12px; color: #fff; letter-spacing: -0.3px; }
        h2.sec-title em { color: var(--primary); font-style: normal; }
        .sec-sub { text-align: center; font-size: 15px; color: var(--muted); max-width: 700px; margin: 0 auto 44px; }

        /* Learn grid */
        .learn-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; }
        .learn-card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 26px 22px; text-align: left; transition: transform 0.2s, border-color 0.2s; }
        .learn-card:hover { transform: translateY(-3px); border-color: rgba(16,185,129,0.35); }
        .learn-ic { width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(132,204,22,0.1)); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .learn-ic svg { width: 24px; height: 24px; }
        .learn-card h4 { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .learn-card p { font-size: 14px; color: var(--muted); line-height: 1.6; }

        /* For-you list */
        .for-list { max-width: 840px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; }
        .for-row { display: flex; gap: 12px; align-items: flex-start; font-size: 15px; color: var(--text); background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: 14px 16px; }
        .for-row .tick { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; background: rgba(16,185,129,0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; margin-top: 1px; }
        .for-row .tick svg { width: 14px; height: 14px; }

        /* Benefits */
        .ben-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 20px; }
        .ben-card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; padding: 26px 22px; text-align: center; transition: transform 0.2s, border-color 0.2s; }
        .ben-card:hover { transform: translateY(-3px); border-color: rgba(16,185,129,0.35); }
        .ben-ic { width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 14px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #052e1a; display: flex; align-items: center; justify-content: center; }
        .ben-ic svg { width: 28px; height: 28px; }
        .ben-card h4 { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .ben-card p { font-size: 13px; color: var(--muted); }

        /* Host */
        .host-box { max-width: 960px; margin: 0 auto; background: var(--card); border: 1px solid var(--line); border-radius: 18px; padding: 40px; display: grid; grid-template-columns: 220px 1fr; gap: 36px; align-items: center; }
        .host-img { width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); }
        .host-ph { width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #052e1a; display: flex; align-items: center; justify-content: center; font-size: 72px; font-weight: 800; }
        .host-name { font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .host-title { font-size: 15px; color: var(--primary); margin-bottom: 16px; font-weight: 600; }
        .host-bio { font-size: 15px; color: var(--muted); line-height: 1.8; }

        /* FAQ */
        .faq-list { max-width: 820px; margin: 0 auto; }
        .faq-item { border: 1px solid var(--line); border-radius: 12px; margin-bottom: 10px; background: var(--card); overflow: hidden; }
        .faq-q { display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; cursor: pointer; font-size: 15px; font-weight: 600; color: #fff; gap: 14px; user-select: none; transition: background 0.2s; }
        .faq-q:hover { background: var(--card-2); }
        .faq-q .chev { width: 18px; height: 18px; color: var(--primary); transition: transform 0.25s; flex-shrink: 0; }
        .faq-q.open .chev { transform: rotate(180deg); }
        .faq-a { display: none; padding: 0 22px 20px; font-size: 14px; color: var(--muted); line-height: 1.7; }
        .faq-a.open { display: block; }

        /* Final IMPORTANT CTA card */
        .final-cta { padding: 40px 20px 80px; }
        .final-cta-card { max-width: 1060px; margin: 0 auto; border-radius: 24px; padding: 64px 32px 56px; text-align: center; position: relative; overflow: hidden; background: radial-gradient(1200px 500px at 50% 10%, rgba(16,185,129,0.12), transparent 60%), radial-gradient(900px 500px at 80% 90%, rgba(88,28,135,0.45), transparent 55%), linear-gradient(135deg, #0a1a15 0%, #0d1020 55%, #1a0b2e 100%); border: 1px solid rgba(255,255,255,0.06); }
        .final-cta-card::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 22px 22px; background-position: 0 0; opacity: 0.35; pointer-events: none; mask-image: radial-gradient(ellipse at center, rgba(0,0,0,0.8) 20%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center, rgba(0,0,0,0.8) 20%, transparent 75%); }
        .final-cta-card > * { position: relative; z-index: 1; }
        .final-badge { display: inline-block; background: linear-gradient(90deg, var(--primary), var(--secondary)); color: #052e1a; font-weight: 800; font-size: 13px; letter-spacing: 2px; padding: 9px 22px; border-radius: 999px; margin-bottom: 26px; text-transform: uppercase; }
        .final-title { font-size: clamp(32px, 5vw, 54px); font-weight: 800; color: #fff; line-height: 1.12; letter-spacing: -0.5px; margin-bottom: 22px; }
        .final-sub { font-size: 18px; color: #cbd5e1; max-width: 680px; margin: 0 auto 34px; line-height: 1.7; }
        .final-sub em { color: #fbbf24; font-style: normal; font-weight: 600; }
        .final-cta-card .btn-grad { display: inline-block; width: auto; min-width: 520px; max-width: 100%; padding: 22px 56px; font-size: 22px; border-radius: 14px; text-decoration: none; letter-spacing: 0.4px; }
        .final-cta-card .seats-note { font-size: 22px; margin-top: 18px; }
        @media (max-width: 640px) {
            .final-cta-card { padding: 44px 22px 40px; border-radius: 18px; }
            .final-cta-card .btn-grad { min-width: 0; width: 100%; padding: 16px 22px; font-size: 17px; }
            .final-cta-card .seats-note { font-size: 18px; }
        }

        /* Footer */
        footer.lp { padding: 34px 20px; text-align: center; border-top: 1px solid var(--line); color: var(--muted); }
        footer.lp p { font-size: 12px; line-height: 1.8; max-width: 820px; margin: 0 auto; }

        /* ── FIXED BOTTOM BAR ────────────────────────────────────────────── */
        .fixed-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #000; border-top: 1px solid var(--line); padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; gap: 20px; z-index: 50; box-shadow: 0 -10px 30px rgba(0,0,0,0.5); }
        .fixed-bar-left { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .fixed-bar-deadline { font-size: 14px; color: var(--text); font-weight: 500; }
        .fixed-bar-deadline strong { color: #fff; font-weight: 700; }
        .fixed-bar-right a { white-space: nowrap; }

        /* Responsive */
        @media (max-width: 880px) {
            .main-grid { grid-template-columns: 1fr; gap: 28px; }
            .for-list { grid-template-columns: 1fr; }
            .host-box { grid-template-columns: 1fr; text-align: center; padding: 28px 22px; }
            .host-img, .host-ph { margin: 0 auto; }
            .panel-title::before, .panel-title::after { display: none; }
            .fixed-bar { padding: 12px 16px; }
            .fixed-bar-deadline { font-size: 12px; }
            .fixed-bar-right .btn-grad { padding: 11px 18px; font-size: 14px; }
        }
        @media (max-width: 520px) {
            .event-grid { grid-template-columns: 1fr; }
            body { padding-bottom: 74px; }
            .fixed-bar-left { flex: 1; min-width: 0; }
            .fixed-bar-deadline { font-size: 11px; }
        }
    </style>
</head>
<body>

{{-- ══ TOP COUNTDOWN BAR ══════════════════════════════════════════════════ --}}
<div class="topbar">
    <span class="live-dot"></span>
    @if($page->registration_deadline)
        <span class="topbar-text">Free Registration Ends In:</span>
        <div class="cd-row" id="countdown">
            <div class="cd-cell" id="cd-days">00</div>
            <div class="cd-cell" id="cd-hours">00</div>
            <div class="cd-cell" id="cd-mins">00</div>
            <div class="cd-cell" id="cd-secs">00</div>
        </div>
    @else
        <span class="topbar-text">Live Online — Limited Seats Available</span>
    @endif
</div>

{{-- ══ HERO ═══════════════════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="wrap">
        @if($page->logo_path)
            <img src="{{ Storage::url($page->logo_path) }}" alt="Logo" class="hero-logo">
        @endif
        <h1>{!! preg_replace('/(\S+\s+\S+\s+\S+\s+\S+)$/', '<em>$1</em>', e($page->hero_headline)) !!}</h1>
        @if($page->hero_subheadline)
        <p class="sub">{{ $page->hero_subheadline }}</p>
        @endif
    </div>
</section>

{{-- ══ VIDEO + EVENT DETAILS ══════════════════════════════════════════════ --}}
<section>
    <div class="wrap">
        <div class="main-grid">
            {{-- Left: Video --}}
            <div>
                @if($page->video_file_path || $page->embed_url)
                <div class="video-wrap">
                    @if($page->video_file_path)
                        <video controls preload="metadata" playsinline>
                            <source src="{{ Storage::url($page->video_file_path) }}" type="video/mp4">
                        </video>
                    @else
                        <iframe src="{{ $page->embed_url }}" title="Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @endif
                </div>
                @elseif($page->hero_image_path)
                <img src="{{ Storage::url($page->hero_image_path) }}" alt="" style="width:100%;border-radius:16px;border:1px solid var(--line);">
                @endif
                @if($page->host_name)
                <div class="host-caption">
                    <div class="host-caption-name">{{ $page->host_name }}</div>
                    @if($page->host_title)
                    <div class="host-caption-title">{{ $page->host_title }}</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Right: Event grid + CTA --}}
            <div>
                <div class="panel-title">{{ $page->video_section_title ?: '90 Minutes Webinar' }}</div>
                <div class="event-grid">
                    @if($page->event_date)
                    <div class="event-card">
                        <div class="event-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                        <div><div class="event-lbl">Date</div><div class="event-val">{{ $page->event_date->format('jS F Y') }}</div></div>
                    </div>
                    @endif
                    @if($page->event_time)
                    <div class="event-card">
                        <div class="event-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div><div class="event-lbl">Time</div><div class="event-val">{{ $page->event_time }}</div></div>
                    </div>
                    @endif
                    @if($page->event_platform)
                    <div class="event-card">
                        <div class="event-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
                        <div><div class="event-lbl">Platform</div><div class="event-val">{{ $page->event_platform }}</div></div>
                    </div>
                    @endif
                    @if($page->event_language)
                    <div class="event-card">
                        <div class="event-ic"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div><div class="event-lbl">Language</div><div class="event-val">{{ $page->event_language }}</div></div>
                    </div>
                    @endif
                </div>
                <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
                @if($page->seats_total > 0)
                <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span> Of {{ $page->seats_total }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ══ WHAT YOU'LL LEARN ═════════════════════════════════════════════════ --}}
@if($page->learnings && count($page->learnings))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <h2 class="sec-title">🎯 What You Will Learn In This <em>Webinar</em></h2>
        </div>
        <div class="learn-grid">
            @php
            $icons = [
                'M13 10V3L4 14h7v7l9-11h-7z',
                'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z',
                'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            ];
            @endphp
            @foreach($page->learnings as $i => $item)
            <div class="learn-card">
                <div class="learn-ic">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$i % count($icons)] }}"/></svg>
                </div>
                <h4>{{ $item['title'] }}</h4>
                @if($item['description'] ?? null)<p>{{ $item['description'] }}</p>@endif
            </div>
            @endforeach
        </div>
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ ABOUT ═════════════════════════════════════════════════════════════ --}}
@if($page->about_title || $page->about_description)
<section class="s">
    <div class="wrap" style="text-align:center;max-width:860px;">
        @if($page->about_title)
        <span class="eyebrow">About This Session</span>
        <h2 class="sec-title">{{ $page->about_title }}</h2>
        @endif
        @if($page->about_description)
        <p style="font-size:16px;color:var(--muted);line-height:1.9;">{{ $page->about_description }}</p>
        @endif
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ WHO SHOULD ATTEND ═════════════════════════════════════════════════ --}}
@if($page->qualifications && count($page->qualifications))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <h2 class="sec-title">🧠 This Masterclass is <em>for you if...</em></h2>
        </div>
        <div class="for-list">
            @foreach($page->qualifications as $item)
            <div class="for-row">
                <span class="tick">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span>{{ $item['text'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ BENEFITS ══════════════════════════════════════════════════════════ --}}
@if($page->benefits && count($page->benefits))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">Program Benefits</span>
            <h2 class="sec-title">What You'll <em>Walk Away With</em></h2>
            <p class="sec-sub">Everything you gain from attending this session</p>
        </div>
        <div class="ben-grid">
            @php
            $bIcons = [
                'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
            ];
            @endphp
            @foreach($page->benefits as $i => $item)
            <div class="ben-card">
                <div class="ben-ic">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bIcons[$i % count($bIcons)] }}"/></svg>
                </div>
                <h4>{{ $item['title'] }}</h4>
                @if($item['description'] ?? null)<p>{{ $item['description'] }}</p>@endif
            </div>
            @endforeach
        </div>
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ HOST ═════════════════════════════════════════════════════════════ --}}
@if($page->host_name)
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">Meet Your Host</span>
            <h2 class="sec-title">Learn From An <em>Industry Expert</em></h2>
        </div>
        <div class="host-box">
            @if($page->host_photo_path)
                <img src="{{ Storage::url($page->host_photo_path) }}" alt="{{ $page->host_name }}" class="host-img">
            @else
                <div class="host-ph">{{ strtoupper(substr($page->host_name, 0, 1)) }}</div>
            @endif
            <div>
                <div class="host-name">{{ $page->host_name }}</div>
                @if($page->host_title)<div class="host-title">{{ $page->host_title }}</div>@endif
                @if($page->host_bio)<div class="host-bio">{{ $page->host_bio }}</div>@endif
            </div>
        </div>
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ REGISTRATION FORM ═════════════════════════════════════════════════ --}}
<section class="s reg-section" id="register">
    <div class="wrap">
        <div class="reg-box">
            <div class="reg-head">
                <h3>Reserve Your FREE Seat</h3>
                <p>Join {{ $page->registrations()->count() + 120 }}+ people already registered</p>
            </div>
            <div class="reg-body">
                @if(session('registered'))
                    <div class="success-box">
                        <h4>🎉 You're Registered!</h4>
                        <p>We'll send you the event details shortly. See you there!</p>
                    </div>
                @else
                <form method="POST" action="{{ route('landing.register', $page->slug) }}">
                    @csrf
                    @if(session('error'))<div class="err-box">{{ session('error') }}</div>@endif
                    @php $ff = $page->form_fields ?? ['name'=>true,'email'=>true,'phone'=>true,'city'=>false]; @endphp
                    @if(!empty($ff['name']))
                    <input type="text" name="name" class="fld" placeholder="Your Full Name" value="{{ old('name') }}" required>
                    @error('name')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['email']))
                    <input type="email" name="email" class="fld" placeholder="Email Address" value="{{ old('email') }}" required>
                    @error('email')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['phone']))
                    <input type="tel" name="phone" class="fld" placeholder="Phone Number (WhatsApp)" value="{{ old('phone') }}" required>
                    @error('phone')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['city']))
                    <input type="text" name="city" class="fld" placeholder="Your City" value="{{ old('city') }}">
                    @error('city')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    <button type="submit" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</button>
                    <p class="safe">🔒 Your details are 100% safe. No spam.</p>
                </form>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ══ FAQ ══════════════════════════════════════════════════════════════ --}}
@if($page->faqs && count($page->faqs))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">FAQs</span>
            <h2 class="sec-title">Frequently Asked <em>Questions</em></h2>
            <p class="sec-sub">Everything you need to know before registering</p>
        </div>
        <div class="faq-list">
            @foreach($page->faqs as $i => $faq)
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    <span>{{ $faq['question'] }}</span>
                    <svg class="chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="faq-a">{{ $faq['answer'] }}</div>
            </div>
            @endforeach
        </div>
        <div class="cta-block">
            <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve My FREE Seat' }}</a>
            @if($page->seats_total > 0)
            <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ FINAL IMPORTANT CTA ═══════════════════════════════════════════════ --}}
<section class="final-cta">
    <div class="final-cta-card">
        <span class="final-badge">Important</span>
        <h2 class="final-title">Register Below to Save<br>Your FREE Seat.</h2>
        <p class="final-sub">You can keep wishing... Or you can take {{ $page->event_time ? '90 minutes' : 'action now' }} and <em>secure your spot</em> — once and for all.</p>
        <a href="#register" class="btn-grad">{{ $page->cta_text ?: 'Reserve Your FREE Seat' }}</a>
        @if($page->seats_total > 0)
        <p class="seats-note">Only <span>{{ $seatsLeft }} Seats Left</span></p>
        @endif
    </div>
</section>

{{-- ══ FOOTER ════════════════════════════════════════════════════════════ --}}
<footer class="lp">
    @if($page->footer_disclaimer)
        <p>{{ $page->footer_disclaimer }}</p>
    @else
        <p>This page is for informational purposes only. By registering, you agree to be contacted regarding this event. This site is not affiliated with Meta™, Google™, or YouTube™ in any way.</p>
    @endif
    <p style="margin-top:10px;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
</footer>

{{-- ══ FIXED BOTTOM BAR ═════════════════════════════════════════════════ --}}
<div class="fixed-bar">
    <div class="fixed-bar-left">
        @if($page->registration_deadline)
            <div class="fixed-bar-deadline">Deadline: <strong>{{ $page->registration_deadline->format('jS F Y') }}</strong></div>
        @elseif($page->event_date)
            <div class="fixed-bar-deadline">Event: <strong>{{ $page->event_date->format('jS F Y') }}</strong></div>
        @else
            <div class="fixed-bar-deadline">Limited seats — register now</div>
        @endif
    </div>
    <div class="fixed-bar-right">
        <a href="#register" class="btn-grad btn-sm">{{ $page->cta_text ?: 'Reserve Your FREE Seat' }}</a>
    </div>
</div>

<script>
@if($page->registration_deadline)
(function() {
    const deadline = new Date("{{ $page->registration_deadline->toIso8601String() }}").getTime();
    function update() {
        const diff = deadline - Date.now();
        if (diff <= 0) {
            const c = document.getElementById('countdown');
            if (c) c.innerHTML = '<span style="color:#ef4444;font-weight:700;">Registration Closed</span>';
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = String(v).padStart(2,'0'); };
        set('cd-days', d); set('cd-hours', h); set('cd-mins', m); set('cd-secs', s);
    }
    update();
    setInterval(update, 1000);
})();
@endif

function toggleFaq(el) {
    const ans = el.nextElementSibling;
    const isOpen = ans.classList.contains('open');
    document.querySelectorAll('.faq-a.open').forEach(a => { a.classList.remove('open'); a.previousElementSibling.classList.remove('open'); });
    if (!isOpen) { ans.classList.add('open'); el.classList.add('open'); }
}
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->hero_headline }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: {{ $page->primary_color ?: '#0ea5a4' }};
            --secondary: {{ $page->secondary_color ?: '#14b8a6' }};
            --accent: #0d9488;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e5e7eb;
            --bg: #ffffff;
            --soft: #f8fafc;
            --warn: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.6; font-size: 16px; }
        img { max-width: 100%; display: block; }
        a { color: var(--primary); }

        /* Top urgency bar */
        .topbar { background: var(--ink); color: #fff; padding: 10px 16px; font-size: 13px; text-align: center; letter-spacing: 0.3px; }
        .topbar b { color: #fcd34d; }

        /* Container */
        .wrap { max-width: 1140px; margin: 0 auto; padding: 0 20px; }

        /* HERO */
        .hero { background: var(--soft); padding: 32px 0 20px; }
        .hero-logo { max-height: 52px; margin-bottom: 18px; }
        .hero-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 36px; align-items: start; }
        .kicker { display: inline-block; background: #fff5d6; color: #92400e; font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 6px; letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 14px; }
        .hero h1 { font-size: clamp(26px, 3.2vw, 40px); line-height: 1.2; font-weight: 800; color: var(--ink); margin-bottom: 14px; }
        .hero h1 em { color: var(--primary); font-style: normal; }
        .hero .sub { font-size: 16px; color: #334155; margin-bottom: 18px; }
        .meta-list { list-style: none; margin: 18px 0 8px; padding: 0; }
        .meta-list li { display: flex; gap: 10px; align-items: center; padding: 6px 0; font-size: 15px; color: #1f2937; }
        .meta-list li .ic { width: 22px; height: 22px; color: var(--primary); flex-shrink: 0; }

        /* Registration Card */
        .regcard { background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 20px 50px -20px rgba(15,23,42,0.18); overflow: hidden; }
        .regcard .rc-head { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; padding: 18px 22px; text-align: center; }
        .regcard .rc-head h3 { font-size: 18px; font-weight: 700; margin-bottom: 2px; }
        .regcard .rc-head p { font-size: 13px; opacity: 0.9; }
        .regcard form { padding: 22px; }
        .fld { width: 100%; padding: 12px 14px; border: 1px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 14px; color: var(--ink); margin-bottom: 12px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .fld:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,164,0.12); }
        .btn-main { display: block; width: 100%; background: #ea580c; color: #fff; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; font-family: inherit; letter-spacing: 0.3px; transition: background 0.2s, transform 0.1s; text-transform: uppercase; }
        .btn-main:hover { background: #c2410c; }
        .btn-main:active { transform: translateY(1px); }
        .seats-tag { text-align: center; font-size: 13px; color: var(--warn); font-weight: 600; margin-top: 10px; }
        .safe { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 10px; letter-spacing: 0.3px; }
        .success-box { background: #ecfdf5; border: 1px solid #6ee7b7; padding: 20px; border-radius: 10px; text-align: center; }
        .success-box h4 { color: #047857; font-size: 18px; margin-bottom: 4px; }
        .success-box p { color: #065f46; font-size: 14px; }
        .err-box { background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 14px; }

        /* Countdown bar */
        .cd-wrap { background: #fff; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); padding: 22px 20px; text-align: center; }
        .cd-label { font-size: 13px; letter-spacing: 1px; text-transform: uppercase; color: var(--warn); font-weight: 700; margin-bottom: 10px; }
        .cd-row { display: inline-flex; gap: 10px; align-items: flex-end; justify-content: center; flex-wrap: wrap; }
        .cd-cell { background: var(--ink); color: #fff; padding: 14px 16px; border-radius: 10px; min-width: 78px; text-align: center; }
        .cd-num { font-size: 30px; font-weight: 800; line-height: 1; display: block; }
        .cd-tag { font-size: 11px; opacity: 0.7; letter-spacing: 1px; text-transform: uppercase; margin-top: 6px; display: block; }

        /* Section */
        section.s { padding: 60px 0; }
        section.s.alt { background: var(--soft); }
        section.s.dark { background: #0b1220; color: #e2e8f0; }
        section.s.dark h2, section.s.dark h3 { color: #fff; }
        .eyebrow { display: inline-block; background: rgba(14,165,164,0.12); color: var(--primary); font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 999px; letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 12px; }
        h2.sec-title { font-size: clamp(24px, 2.6vw, 34px); font-weight: 800; line-height: 1.25; text-align: center; margin-bottom: 10px; color: var(--ink); }
        h2.sec-title em { color: var(--primary); font-style: normal; }
        .sec-sub { text-align: center; font-size: 15px; color: var(--muted); max-width: 680px; margin: 0 auto 36px; }
        section.s.dark .sec-sub { color: #94a3b8; }

        /* Video */
        .video-wrap { position: relative; max-width: 900px; margin: 0 auto; padding-top: 56.25%; border-radius: 14px; overflow: hidden; background: #000; box-shadow: 0 20px 60px -20px rgba(15,23,42,0.35); }
        .video-wrap iframe, .video-wrap video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

        /* Learn grid */
        .learn-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 22px; }
        .learn-card { text-align: center; padding: 24px 16px; }
        .learn-ic { width: 64px; height: 64px; border-radius: 14px; background: rgba(14,165,164,0.1); color: var(--primary); margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; }
        .learn-ic svg { width: 32px; height: 32px; }
        .learn-card h4 { font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
        .learn-card p { font-size: 14px; color: var(--muted); line-height: 1.55; }

        /* For-you list */
        .for-list { max-width: 780px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 14px 22px; }
        .for-row { display: flex; gap: 12px; align-items: flex-start; font-size: 15px; color: #1f2937; }
        .for-row .tick { flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; background: rgba(16,185,129,0.12); color: #059669; display: flex; align-items: center; justify-content: center; margin-top: 1px; }
        .for-row .tick svg { width: 15px; height: 15px; }

        /* Benefits */
        .ben-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 22px; }
        .ben-card { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px 20px; text-align: center; transition: transform 0.2s, box-shadow 0.2s; }
        .ben-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px -15px rgba(15,23,42,0.15); }
        .ben-ic { width: 54px; height: 54px; margin: 0 auto 14px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; display: flex; align-items: center; justify-content: center; }
        .ben-ic svg { width: 26px; height: 26px; }
        .ben-card h4 { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
        .ben-card p { font-size: 13px; color: var(--muted); }

        /* Host */
        .host-box { max-width: 920px; margin: 0 auto; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 36px; display: grid; grid-template-columns: 200px 1fr; gap: 32px; align-items: center; }
        .host-img { width: 180px; height: 180px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); }
        .host-ph { width: 180px; height: 180px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 64px; font-weight: 800; border: 4px solid rgba(255,255,255,0.14); }
        .host-name { font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .host-title { font-size: 15px; color: var(--primary); margin-bottom: 14px; font-weight: 600; }
        .host-bio { font-size: 15px; color: #cbd5e1; line-height: 1.8; }

        /* FAQ */
        .faq-list { max-width: 780px; margin: 0 auto; }
        .faq-item { border: 1px solid var(--line); border-radius: 10px; margin-bottom: 10px; background: #fff; overflow: hidden; }
        .faq-q { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; cursor: pointer; font-size: 15px; font-weight: 600; color: var(--ink); gap: 14px; user-select: none; }
        .faq-q:hover { background: #f8fafc; }
        .faq-q .emo { margin-right: 4px; }
        .faq-q .chev { width: 18px; height: 18px; color: var(--primary); transition: transform 0.25s; flex-shrink: 0; }
        .faq-q.open .chev { transform: rotate(180deg); }
        .faq-a { display: none; padding: 0 20px 18px; font-size: 14px; color: #475569; line-height: 1.7; }
        .faq-a.open { display: block; }

        /* CTA final */
        .cta-final { background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 56px 20px; text-align: center; color: #fff; }
        .cta-final h2 { font-size: clamp(24px, 3.4vw, 34px); font-weight: 800; margin-bottom: 10px; color: #fff; }
        .cta-final p { font-size: 16px; opacity: 0.92; margin-bottom: 20px; }
        .cta-final .scar { display: inline-block; background: rgba(0,0,0,0.2); padding: 6px 18px; border-radius: 999px; font-size: 13px; font-weight: 600; margin-bottom: 22px; }
        .cta-btn { display: inline-block; background: #fff; color: var(--primary); padding: 14px 36px; border-radius: 999px; font-size: 16px; font-weight: 800; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .cta-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(0,0,0,0.25); }

        /* Footer */
        footer.lp { background: #0b1220; color: #94a3b8; padding: 34px 20px; text-align: center; }
        footer.lp p { font-size: 12px; line-height: 1.8; max-width: 780px; margin: 0 auto; }
        footer.lp a { color: #cbd5e1; }

        /* Responsive */
        @media (max-width: 860px) {
            .hero-grid { grid-template-columns: 1fr; }
            .for-list { grid-template-columns: 1fr; }
            .host-box { grid-template-columns: 1fr; text-align: center; padding: 28px 22px; }
            .host-img, .host-ph { margin: 0 auto; }
            .cd-cell { min-width: 64px; padding: 10px 12px; }
            .cd-num { font-size: 22px; }
        }
    </style>
</head>
<body>

{{-- ══ TOP URGENCY BAR ═══════════════════════════════════════════════════ --}}
<div class="topbar">
    @if($page->seats_total > 0)
        🔥 <b>Only {{ $seatsLeft }} Seats Left</b> — Register Before It's Too Late!
    @else
        🎯 <b>LIVE Online Session</b> — Limited Seats Available. Register Now!
    @endif
</div>

{{-- ══ HERO ═════════════════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="wrap">
        @if($page->logo_path)
            <img src="{{ Storage::url($page->logo_path) }}" alt="Logo" class="hero-logo">
        @endif
        <div class="hero-grid">
            {{-- Left content --}}
            <div>
                <span class="kicker">🎓 Free Online Masterclass</span>
                <h1>{!! preg_replace('/(\S+\s+\S+)$/', '<em>$1</em>', e($page->hero_headline)) !!}</h1>
                @if($page->hero_subheadline)
                <p class="sub">{{ $page->hero_subheadline }}</p>
                @endif

                <ul class="meta-list">
                    @if($page->event_date)
                    <li><svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <strong>Date:</strong>&nbsp;{{ $page->event_date->format('d M Y') }}@if($page->event_time) &nbsp;·&nbsp; {{ $page->event_time }}@endif</li>
                    @endif
                    @if($page->event_platform)
                    <li><svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <strong>Platform:</strong>&nbsp;{{ $page->event_platform }}</li>
                    @endif
                    @if($page->event_language)
                    <li><svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                    <strong>Language:</strong>&nbsp;{{ $page->event_language }}</li>
                    @endif
                    @if($page->seats_total > 0)
                    <li><svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <strong>Seats:</strong>&nbsp;Only {{ $seatsLeft }} left of {{ $page->seats_total }}</li>
                    @endif
                </ul>
            </div>

            {{-- Right: Registration Form --}}
            <div id="register">
                <div class="regcard">
                    <div class="rc-head">
                        <h3>Reserve Your FREE Seat</h3>
                        <p>Join {{ $page->registrations()->count() + 120 }}+ people already registered</p>
                    </div>
                    @if(session('registered'))
                        <div style="padding:22px;">
                            <div class="success-box">
                                <h4>🎉 You're Registered!</h4>
                                <p>We'll send you the event details shortly. See you there!</p>
                            </div>
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
                        <button type="submit" class="btn-main">{{ $page->cta_text ?: 'Reserve My FREE Seat!' }}</button>
                        @if($page->seats_total > 0)
                            <p class="seats-tag">⚠ Only {{ $seatsLeft }} Seats Left</p>
                        @endif
                        <p class="safe">🔒 Your details are 100% safe. No spam.</p>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ COUNTDOWN ═════════════════════════════════════════════════════════ --}}
@if($page->registration_deadline)
<div class="cd-wrap">
    <div class="cd-label">⏰ This Offer Ends In</div>
    <div class="cd-row" id="countdown">
        <div class="cd-cell"><span class="cd-num" id="cd-days">00</span><span class="cd-tag">Days</span></div>
        <div class="cd-cell"><span class="cd-num" id="cd-hours">00</span><span class="cd-tag">Hours</span></div>
        <div class="cd-cell"><span class="cd-num" id="cd-mins">00</span><span class="cd-tag">Minutes</span></div>
        <div class="cd-cell"><span class="cd-num" id="cd-secs">00</span><span class="cd-tag">Seconds</span></div>
    </div>
</div>
@endif

{{-- ══ VIDEO ═════════════════════════════════════════════════════════════ --}}
@if($page->video_file_path || $page->embed_url)
<section class="s alt">
    <div class="wrap" style="text-align:center;">
        @if($page->video_section_title)
        <span class="eyebrow">🎬 Watch This First</span>
        <h2 class="sec-title">{{ $page->video_section_title }}</h2>
        @endif
        @if($page->video_section_description)
        <p class="sec-sub">{{ $page->video_section_description }}</p>
        @endif
        <div class="video-wrap">
            @if($page->video_file_path)
                <video controls preload="metadata" playsinline>
                    <source src="{{ Storage::url($page->video_file_path) }}" type="video/mp4">
                </video>
            @else
                <iframe src="{{ $page->embed_url }}" title="Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ ABOUT ═════════════════════════════════════════════════════════════ --}}
@if($page->about_title || $page->about_description)
<section class="s">
    <div class="wrap" style="text-align:center;max-width:820px;">
        @if($page->about_title)
        <span class="eyebrow">🎯 About This Session</span>
        <h2 class="sec-title">{{ $page->about_title }}</h2>
        @endif
        @if($page->about_description)
        <p style="font-size:16px;color:#334155;line-height:1.8;">{{ $page->about_description }}</p>
        @endif
    </div>
</section>
@endif

{{-- ══ WHAT YOU'LL LEARN ═════════════════════════════════════════════════ --}}
@if($page->learnings && count($page->learnings))
<section class="s alt">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">🧠 What You'll Learn</span>
            <h2 class="sec-title">Key Takeaways From This <em>Masterclass</em></h2>
            <p class="sec-sub">Practical, actionable insights you can apply from Day 1</p>
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
    </div>
</section>
@endif

{{-- ══ WHO SHOULD ATTEND ═════════════════════════════════════════════════ --}}
@if($page->qualifications && count($page->qualifications))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">👤 Who Should Attend</span>
            <h2 class="sec-title">This Masterclass Is <em>For You If...</em></h2>
            <p class="sec-sub">Check if you match the profile of our ideal attendee</p>
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
    </div>
</section>
@endif

{{-- ══ INTERSTITIAL CTA ══════════════════════════════════════════════════ --}}
<section class="s alt" style="padding:40px 0;">
    <div class="wrap" style="text-align:center;">
        <a href="#register" class="btn-main" style="display:inline-block;width:auto;padding:14px 44px;text-decoration:none;">🔥 Reserve My FREE Seat Now</a>
        @if($page->seats_total > 0)
        <p style="margin-top:12px;font-size:13px;color:var(--warn);font-weight:600;">⚠ Only {{ $seatsLeft }} Seats Left!</p>
        @endif
    </div>
</section>

{{-- ══ BENEFITS ══════════════════════════════════════════════════════════ --}}
@if($page->benefits && count($page->benefits))
<section class="s">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">💰 Program Benefits</span>
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
    </div>
</section>
@endif

{{-- ══ HOST ═════════════════════════════════════════════════════════════ --}}
@if($page->host_name)
<section class="s dark">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">👨‍🏫 Meet Your Host</span>
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
    </div>
</section>
@endif

{{-- ══ FAQ ══════════════════════════════════════════════════════════════ --}}
@if($page->faqs && count($page->faqs))
<section class="s alt">
    <div class="wrap">
        <div style="text-align:center;">
            <span class="eyebrow">💬 FAQs</span>
            <h2 class="sec-title">Frequently Asked <em>Questions</em></h2>
            <p class="sec-sub">Everything you need to know before registering</p>
        </div>
        <div class="faq-list">
            @foreach($page->faqs as $i => $faq)
            <div class="faq-item">
                <div class="faq-q" onclick="toggleFaq(this)">
                    <span><span class="emo">❓</span> {{ $faq['question'] }}</span>
                    <svg class="chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="faq-a">{{ $faq['answer'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ FINAL CTA ═════════════════════════════════════════════════════════ --}}
<section class="cta-final">
    <div style="max-width:720px;margin:0 auto;">
        <h2>Ready To Transform Your Hiring?</h2>
        <p>Seats are filling up fast. Grab yours before registration closes.</p>
        @if($page->seats_total > 0)
            <span class="scar">🔥 Only {{ $seatsLeft }} Seats Remaining</span><br>
        @endif
        <a href="#register" class="cta-btn">{{ $page->cta_text ?: 'Reserve My FREE Seat!' }}</a>
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

<script>
@if($page->registration_deadline)
(function() {
    const deadline = new Date("{{ $page->registration_deadline->toIso8601String() }}").getTime();
    function update() {
        const diff = deadline - Date.now();
        if (diff <= 0) {
            const c = document.getElementById('countdown');
            if (c) c.innerHTML = '<div style="color:var(--warn);font-weight:700;font-size:16px;">Registration Closed</div>';
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

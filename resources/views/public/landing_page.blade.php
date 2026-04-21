<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->hero_headline }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: {{ $page->primary_color }};
            --secondary: {{ $page->secondary_color }};
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.6; }

        /* Header */
        .lp-header { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); padding: 12px 20px; text-align: center; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 20px rgba(0,0,0,0.2); }
        .lp-header-inner { max-width: 900px; margin: 0 auto; display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap; }
        .lp-urgent-badge { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); color: #fff; padding: 4px 14px; border-radius: 99px; font-size: 13px; font-weight: 600; letter-spacing: 0.5px; }
        .lp-countdown { display: flex; gap: 8px; align-items: center; }
        .lp-cd-block { background: rgba(0,0,0,0.25); border-radius: 8px; padding: 4px 10px; text-align: center; min-width: 52px; }
        .lp-cd-num { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; display: block; }
        .lp-cd-label { font-size: 9px; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.5px; }
        .lp-cd-sep { color: #fff; font-size: 22px; font-weight: 800; opacity: 0.7; }

        /* Hero */
        .lp-hero { background: linear-gradient(150deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); padding: 60px 20px; position: relative; overflow: hidden; }
        .lp-hero::before { content: ''; position: absolute; top: -100px; right: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%); border-radius: 50%; }
        .lp-hero::after { content: ''; position: absolute; bottom: -100px; left: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%); border-radius: 50%; }
        .lp-hero-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 380px; gap: 48px; align-items: center; position: relative; z-index: 1; }
        .lp-hero-badge { display: inline-block; background: rgba(99,102,241,0.25); border: 1px solid rgba(99,102,241,0.5); color: #a5b4fc; padding: 6px 16px; border-radius: 99px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .lp-hero h1 { font-size: clamp(28px, 4vw, 46px); font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 16px; }
        .lp-hero h1 span { background: linear-gradient(135deg, var(--primary), #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .lp-hero-sub { font-size: 17px; color: rgba(255,255,255,0.7); margin-bottom: 28px; }
        .lp-event-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px; }
        .lp-chip { display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #e2e8f0; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; }
        .lp-chip svg { width: 14px; height: 14px; opacity: 0.7; }
        .lp-seats-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; }
        .lp-seats-dot { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(1.3)} }

        /* Registration Card */
        .lp-reg-card { background: #fff; border-radius: 20px; box-shadow: 0 25px 60px rgba(0,0,0,0.4); padding: 32px 28px; }
        .lp-reg-card h3 { font-size: 18px; font-weight: 700; color: #1e293b; text-align: center; margin-bottom: 6px; }
        .lp-reg-card p { text-align: center; font-size: 13px; color: #64748b; margin-bottom: 20px; }
        .lp-input { width: 100%; padding: 11px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-family: inherit; color: #1e293b; transition: border-color 0.2s, box-shadow 0.2s; outline: none; margin-bottom: 12px; }
        .lp-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
        .lp-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #fff; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; font-family: inherit; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; letter-spacing: 0.3px; }
        .lp-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79,70,229,0.4); }
        .lp-btn:active { transform: translateY(0); }
        .lp-success { background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 20px; text-align: center; }
        .lp-success h4 { color: #16a34a; font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .lp-success p { color: #166534; font-size: 14px; }

        /* Sections */
        .lp-section { padding: 72px 20px; }
        .lp-section-alt { background: #f1f5f9; }
        .lp-section-dark { background: linear-gradient(150deg, #0f172a 0%, #1e1b4b 100%); color: #fff; }
        .lp-container { max-width: 900px; margin: 0 auto; }
        .lp-section-title { font-size: clamp(24px, 3vw, 36px); font-weight: 800; text-align: center; margin-bottom: 12px; }
        .lp-section-sub { text-align: center; font-size: 16px; color: #64748b; margin-bottom: 48px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .lp-section-dark .lp-section-sub { color: rgba(255,255,255,0.6); }
        .lp-title-accent { background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* About */
        .lp-about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
        .lp-about-text { font-size: 16px; color: #475569; line-height: 1.8; }

        /* Grid Cards */
        .lp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .lp-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; transition: transform 0.2s, box-shadow 0.2s; }
        .lp-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
        .lp-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; background: linear-gradient(135deg, var(--primary), var(--secondary)); }
        .lp-card-icon svg { width: 24px; height: 24px; color: #fff; }
        .lp-card h4 { font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .lp-card p { font-size: 13px; color: #64748b; line-height: 1.6; }

        /* Qualifications */
        .lp-qual-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
        .lp-qual-item { display: flex; align-items: flex-start; gap: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; padding: 16px; }
        .lp-check { width: 22px; height: 22px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 6px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; margin-top: 1px; }
        .lp-check svg { width: 13px; height: 13px; color: #fff; }
        .lp-qual-item span { font-size: 14px; color: rgba(255,255,255,0.85); line-height: 1.5; }

        /* Host */
        .lp-host { display: grid; grid-template-columns: 200px 1fr; gap: 40px; align-items: center; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; padding: 40px; }
        .lp-host-img { width: 180px; height: 180px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); box-shadow: 0 0 40px rgba(79,70,229,0.3); }
        .lp-host-placeholder { width: 180px; height: 180px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: 800; color: #fff; border: 4px solid rgba(255,255,255,0.2); }
        .lp-host-name { font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .lp-host-title { font-size: 15px; color: rgba(255,255,255,0.6); margin-bottom: 16px; }
        .lp-host-bio { font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.8; }

        /* FAQ */
        .lp-faq { max-width: 700px; margin: 0 auto; }
        .lp-faq-item { border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
        .lp-faq-q { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; cursor: pointer; font-size: 15px; font-weight: 600; color: #1e293b; background: #fff; user-select: none; transition: background 0.2s; }
        .lp-faq-q:hover { background: #f8fafc; }
        .lp-faq-q svg { width: 18px; height: 18px; color: var(--primary); transition: transform 0.3s; flex-shrink: 0; }
        .lp-faq-q.open svg { transform: rotate(180deg); }
        .lp-faq-a { display: none; padding: 0 20px 18px; font-size: 14px; color: #475569; line-height: 1.7; }
        .lp-faq-a.open { display: block; }

        /* CTA Section */
        .lp-cta-section { background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 72px 20px; text-align: center; }
        .lp-cta-section h2 { font-size: clamp(24px, 4vw, 40px); font-weight: 800; color: #fff; margin-bottom: 16px; }
        .lp-cta-section p { font-size: 17px; color: rgba(255,255,255,0.8); margin-bottom: 32px; }
        .lp-cta-btn { display: inline-block; background: #fff; color: var(--primary); padding: 16px 40px; border-radius: 99px; font-size: 17px; font-weight: 800; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 8px 30px rgba(0,0,0,0.2); cursor: pointer; border: none; font-family: inherit; }
        .lp-cta-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }

        /* Footer */
        .lp-footer { background: #0f172a; padding: 32px 20px; text-align: center; }
        .lp-footer p { font-size: 12px; color: rgba(255,255,255,0.4); line-height: 1.8; max-width: 700px; margin: 0 auto; }

        /* Logo */
        .lp-logo { max-height: 48px; object-fit: contain; }

        @media (max-width: 768px) {
            .lp-hero-inner { grid-template-columns: 1fr; }
            .lp-about-grid { grid-template-columns: 1fr; }
            .lp-host { grid-template-columns: 1fr; text-align: center; }
            .lp-host-img, .lp-host-placeholder { margin: 0 auto; }
            .lp-cd-num { font-size: 18px; }
            .lp-cd-block { min-width: 42px; padding: 4px 8px; }
        }
    </style>
</head>
<body>

{{-- ══ STICKY HEADER WITH COUNTDOWN ══════════════════════════════════════════ --}}
<div class="lp-header">
    <div class="lp-header-inner">
        @if($page->registration_deadline)
        <div class="lp-urgent-badge">⚡ Registration Closes In</div>
        <div class="lp-countdown" id="countdown">
            <div class="lp-cd-block"><span class="lp-cd-num" id="cd-days">00</span><span class="lp-cd-label">Days</span></div>
            <span class="lp-cd-sep">:</span>
            <div class="lp-cd-block"><span class="lp-cd-num" id="cd-hours">00</span><span class="lp-cd-label">Hrs</span></div>
            <span class="lp-cd-sep">:</span>
            <div class="lp-cd-block"><span class="lp-cd-num" id="cd-mins">00</span><span class="lp-cd-label">Min</span></div>
            <span class="lp-cd-sep">:</span>
            <div class="lp-cd-block"><span class="lp-cd-num" id="cd-secs">00</span><span class="lp-cd-label">Sec</span></div>
        </div>
        @else
        <div class="lp-urgent-badge">🎯 Limited Seats Available — Register Now</div>
        @endif
        <a href="#register" class="lp-cta-btn" style="padding: 8px 20px; font-size: 14px;">Reserve My Slot</a>
    </div>
</div>

{{-- ══ HERO SECTION ═══════════════════════════════════════════════════════════ --}}
<section class="lp-hero">
    <div class="lp-hero-inner">
        {{-- Left --}}
        <div>
            @if($page->logo_path)
                <img src="{{ Storage::url($page->logo_path) }}" alt="Logo" class="lp-logo mb-6">
            @endif
            <div class="lp-hero-badge">🎓 FREE Online Event</div>
            <h1>{{ $page->hero_headline }}</h1>
            @if($page->hero_subheadline)
            <p class="lp-hero-sub">{{ $page->hero_subheadline }}</p>
            @endif

            {{-- Event chips --}}
            @if($page->event_date || $page->event_time || $page->event_platform || $page->event_language)
            <div class="lp-event-chips">
                @if($page->event_date)
                <div class="lp-chip">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $page->event_date->format('d M Y') }}
                </div>
                @endif
                @if($page->event_time)
                <div class="lp-chip">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $page->event_time }}
                </div>
                @endif
                @if($page->event_platform)
                <div class="lp-chip">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    {{ $page->event_platform }}
                </div>
                @endif
                @if($page->event_language)
                <div class="lp-chip">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                    {{ $page->event_language }}
                </div>
                @endif
            </div>
            @endif

            @if($page->seats_total > 0)
            <div class="lp-seats-badge">
                <span class="lp-seats-dot"></span>
                Only {{ $seatsLeft }} Seats Left!
            </div>
            @endif
        </div>

        {{-- Right: Registration Form --}}
        <div id="register">
            <div class="lp-reg-card">
                @if(session('registered'))
                <div class="lp-success">
                    <h4>🎉 You're Registered!</h4>
                    <p>We'll send you the event details shortly. See you there!</p>
                </div>
                @else
                @if(session('error'))
                <div style="background:#fef2f2;border:1px solid #fca5a5;color:#b91c1c;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;">
                    {{ session('error') }}
                </div>
                @endif
                <h3>Reserve Your FREE Seat</h3>
                <p>Join {{ $page->registrations()->count() + 120 }}+ people already registered</p>
                <form method="POST" action="{{ route('landing.register', $page->slug) }}">
                    @csrf
                    @php $ff = $page->form_fields ?? ['name'=>true,'email'=>true,'phone'=>true,'city'=>false]; @endphp
                    @if(!empty($ff['name']))
                    <input type="text" name="name" class="lp-input @error('name') border-red-400 @enderror" placeholder="Your Full Name" value="{{ old('name') }}" required>
                    @error('name')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['email']))
                    <input type="email" name="email" class="lp-input @error('email') border-red-400 @enderror" placeholder="Email Address" value="{{ old('email') }}" required>
                    @error('email')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['phone']))
                    <input type="tel" name="phone" class="lp-input @error('phone') border-red-400 @enderror" placeholder="Phone Number" value="{{ old('phone') }}" required>
                    @error('phone')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    @if(!empty($ff['city']))
                    <input type="text" name="city" class="lp-input @error('city') border-red-400 @enderror" placeholder="Your City" value="{{ old('city') }}">
                    @error('city')<p style="font-size:12px;color:#ef4444;margin-top:-8px;margin-bottom:10px;">{{ $message }}</p>@enderror
                    @endif
                    <button type="submit" class="lp-btn">{{ $page->cta_text ?: 'Reserve My FREE Slot!' }}</button>
                </form>
                <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:12px;">🔒 100% Free. No spam. Ever.</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ══ ABOUT THE EVENT ═════════════════════════════════════════════════════════ --}}
@if($page->about_title || $page->about_description)
<section class="lp-section lp-section-alt">
    <div class="lp-container">
        @if($page->about_title)
        <h2 class="lp-section-title">{{ $page->about_title }}</h2>
        @endif
        @if($page->about_description)
        @if($page->hero_image_path)
        <div class="lp-about-grid">
            <div class="lp-about-text">{{ $page->about_description }}</div>
            <img src="{{ Storage::url($page->hero_image_path) }}" alt="Event" style="width:100%;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.12);">
        </div>
        @else
        <p class="lp-about-text" style="text-align:center;max-width:720px;margin:0 auto;">{{ $page->about_description }}</p>
        @endif
        @endif
    </div>
</section>
@endif

{{-- ══ WHAT YOU'LL LEARN ════════════════════════════════════════════════════════ --}}
@if($page->learnings && count($page->learnings))
<section class="lp-section">
    <div class="lp-container">
        <h2 class="lp-section-title">What You'll <span class="lp-title-accent">Learn</span></h2>
        <p class="lp-section-sub">Key insights and actionable takeaways from this session</p>
        <div class="lp-grid">
            @php
            $icons = ['M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'M13 10V3L4 14h7v7l9-11h-7z', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'];
            @endphp
            @foreach($page->learnings as $i => $item)
            <div class="lp-card">
                <div class="lp-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$i % count($icons)] }}"/></svg>
                </div>
                <h4>{{ $item['title'] }}</h4>
                @if($item['description'] ?? null)<p>{{ $item['description'] }}</p>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ WHO SHOULD ATTEND ════════════════════════════════════════════════════════ --}}
@if($page->qualifications && count($page->qualifications))
<section class="lp-section lp-section-dark">
    <div class="lp-container">
        <h2 class="lp-section-title" style="color:#fff;">This Is For <span class="lp-title-accent">You If...</span></h2>
        <p class="lp-section-sub">Check if you match the profile of our ideal attendee</p>
        <div class="lp-qual-grid">
            @foreach($page->qualifications as $item)
            <div class="lp-qual-item">
                <div class="lp-check">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span>{{ $item['text'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ PROGRAM BENEFITS ═════════════════════════════════════════════════════════ --}}
@if($page->benefits && count($page->benefits))
<section class="lp-section lp-section-alt">
    <div class="lp-container">
        <h2 class="lp-section-title">Why <span class="lp-title-accent">Attend?</span></h2>
        <p class="lp-section-sub">Everything you gain from this session</p>
        <div class="lp-grid">
            @php
            $bIcons = ['M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'];
            @endphp
            @foreach($page->benefits as $i => $item)
            <div class="lp-card">
                <div class="lp-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bIcons[$i % count($bIcons)] }}"/></svg>
                </div>
                <h4>{{ $item['title'] }}</h4>
                @if($item['description'] ?? null)<p>{{ $item['description'] }}</p>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ HOST / PRESENTER ═════════════════════════════════════════════════════════ --}}
@if($page->host_name)
<section class="lp-section lp-section-dark">
    <div class="lp-container">
        <h2 class="lp-section-title" style="color:#fff;">Meet Your <span class="lp-title-accent">Host</span></h2>
        <p class="lp-section-sub"></p>
        <div class="lp-host">
            @if($page->host_photo_path)
                <img src="{{ Storage::url($page->host_photo_path) }}" alt="{{ $page->host_name }}" class="lp-host-img">
            @else
                <div class="lp-host-placeholder">{{ strtoupper(substr($page->host_name, 0, 1)) }}</div>
            @endif
            <div>
                <div class="lp-host-name">{{ $page->host_name }}</div>
                @if($page->host_title)
                <div class="lp-host-title">{{ $page->host_title }}</div>
                @endif
                @if($page->host_bio)
                <div class="lp-host-bio">{{ $page->host_bio }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ FAQs ════════════════════════════════════════════════════════════════════ --}}
@if($page->faqs && count($page->faqs))
<section class="lp-section lp-section-alt">
    <div class="lp-container">
        <h2 class="lp-section-title">Frequently Asked <span class="lp-title-accent">Questions</span></h2>
        <p class="lp-section-sub">Everything you need to know before registering</p>
        <div class="lp-faq">
            @foreach($page->faqs as $i => $faq)
            <div class="lp-faq-item">
                <div class="lp-faq-q" onclick="toggleFaq(this)" data-index="{{ $i }}">
                    {{ $faq['question'] }}
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="lp-faq-a">{{ $faq['answer'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ FINAL CTA ════════════════════════════════════════════════════════════════ --}}
<section class="lp-cta-section">
    <div style="max-width:700px;margin:0 auto;">
        <h2>Ready to Join Us?</h2>
        <p>Seats are filling up fast. Secure your spot now before it's too late.</p>
        @if($page->seats_total > 0)
        <p style="font-size:14px;color:rgba(255,255,255,0.9);background:rgba(0,0,0,0.15);display:inline-block;padding:6px 20px;border-radius:99px;margin-bottom:24px;">
            🔥 Only {{ $seatsLeft }} seats remaining!
        </p>
        @endif
        <br>
        <a href="#register" class="lp-cta-btn">{{ $page->cta_text ?: 'Reserve My FREE Slot!' }}</a>
    </div>
</section>

{{-- ══ FOOTER ═══════════════════════════════════════════════════════════════════ --}}
<footer class="lp-footer">
    @if($page->footer_disclaimer)
    <p>{{ $page->footer_disclaimer }}</p>
    @else
    <p>This page is for informational purposes only. By registering, you agree to be contacted regarding this event.</p>
    @endif
</footer>

<script>
// Countdown Timer
@if($page->registration_deadline)
(function() {
    const deadline = new Date("{{ $page->registration_deadline->toIso8601String() }}").getTime();
    function update() {
        const now = Date.now();
        const diff = deadline - now;
        if (diff <= 0) { document.getElementById('countdown').innerHTML = '<div class="lp-urgent-badge">Registration Closed</div>'; return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        document.getElementById('cd-days').textContent  = String(d).padStart(2,'0');
        document.getElementById('cd-hours').textContent = String(h).padStart(2,'0');
        document.getElementById('cd-mins').textContent  = String(m).padStart(2,'0');
        document.getElementById('cd-secs').textContent  = String(s).padStart(2,'0');
    }
    update();
    setInterval(update, 1000);
})();
@endif

// FAQ Accordion
function toggleFaq(el) {
    const ans = el.nextElementSibling;
    const isOpen = ans.classList.contains('open');
    document.querySelectorAll('.lp-faq-a.open').forEach(a => { a.classList.remove('open'); a.previousElementSibling.classList.remove('open'); });
    if (!isOpen) { ans.classList.add('open'); el.classList.add('open'); }
}

// Smooth scroll to form on CTA click
document.querySelectorAll('a[href="#register"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.getElementById('register').scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>

</body>
</html>

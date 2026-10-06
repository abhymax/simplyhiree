@extends('layouts.web')

@section('title', 'Delete Your Account')

@section('content')
<style>
    .deletion-page { padding: 8.5rem 1.25rem 5rem; background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 45%, #ffffff 100%); }
    .deletion-shell { width: min(1080px, 100%); margin: 0 auto; }
    .deletion-hero { text-align: center; max-width: 760px; margin: 0 auto 2.5rem; }
    .deletion-kicker { color: #2563eb; font-size: .82rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .deletion-title { margin: .6rem 0 .75rem; color: #0f172a; font-size: clamp(2.2rem, 5vw, 3.7rem); font-weight: 800; line-height: 1.05; }
    .deletion-lead { color: #475569; font-size: 1.08rem; line-height: 1.75; }
    .deletion-grid { display: grid; grid-template-columns: .9fr 1.1fr; gap: 1.5rem; align-items: start; }
    .deletion-card { background: rgba(255,255,255,.94); border: 1px solid #dbeafe; border-radius: 18px; box-shadow: 0 22px 55px rgba(15,23,42,.08); padding: 1.75rem; }
    .deletion-card h2 { color: #0f172a; font-size: 1.35rem; font-weight: 800; margin-bottom: 1rem; }
    .deletion-card h3 { color: #0f172a; font-size: 1rem; font-weight: 800; margin: 1.3rem 0 .45rem; }
    .deletion-card p, .deletion-card li { color: #475569; line-height: 1.65; }
    .deletion-card ol, .deletion-card ul { margin: .5rem 0 0 1.25rem; }
    .deletion-card li { margin: .45rem 0; }
    .deletion-notice { border-left: 4px solid #2563eb; background: #eff6ff; border-radius: 10px; padding: 1rem; margin-top: 1.25rem; }
    .deletion-success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; border-radius: 12px; padding: 1rem 1.1rem; margin-bottom: 1.25rem; font-weight: 600; }
    .deletion-errors { background: #fff1f2; border: 1px solid #fda4af; color: #9f1239; border-radius: 12px; padding: 1rem 1.1rem; margin-bottom: 1.25rem; }
    .deletion-field { margin-bottom: 1rem; }
    .deletion-field label { display: block; color: #1e293b; font-size: .9rem; font-weight: 700; margin-bottom: .4rem; }
    .deletion-field input, .deletion-field select, .deletion-field textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: #0f172a; padding: .8rem .9rem; outline: none; transition: border-color .2s, box-shadow .2s; }
    .deletion-field input:focus, .deletion-field select:focus, .deletion-field textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.14); }
    .deletion-check { display: flex; gap: .65rem; align-items: flex-start; color: #475569; font-size: .9rem; line-height: 1.5; }
    .deletion-check input { width: 18px; height: 18px; margin-top: .15rem; accent-color: #2563eb; flex: 0 0 auto; }
    .deletion-submit { width: 100%; margin-top: 1.15rem; border: 0; border-radius: 10px; background: linear-gradient(135deg, #2563eb, #4f46e5); color: #fff; padding: .9rem 1.1rem; font-weight: 800; cursor: pointer; box-shadow: 0 12px 25px rgba(37,99,235,.22); transition: transform .2s, box-shadow .2s; }
    .deletion-submit:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(37,99,235,.3); }
    .deletion-honeypot { position: absolute !important; left: -9999px !important; }
    @media (max-width: 800px) { .deletion-page { padding-top: 7rem; } .deletion-grid { grid-template-columns: 1fr; } .deletion-card { padding: 1.25rem; } }
</style>

<section class="deletion-page">
    <div class="deletion-shell">
        <div class="deletion-hero">
            <div class="deletion-kicker">Privacy and account control</div>
            <h1 class="deletion-title">Request deletion of your SimplyHiree account</h1>
            <p class="deletion-lead">Use this page to request deletion of your SimplyHiree mobile app and web account, together with associated personal data.</p>
        </div>

        <div class="deletion-grid">
            <article class="deletion-card">
                <h2>What happens next</h2>
                <ol>
                    <li>Submit the form using the email registered with SimplyHiree.</li>
                    <li>Our team will contact you to verify that you own the account.</li>
                    <li>After verification, we will delete or anonymise eligible account and profile data.</li>
                    <li>We normally complete verified requests within 30 days.</li>
                </ol>

                <h3>Data deleted or anonymised</h3>
                <p>Account credentials, profile information, contact details, uploaded profile documents, preferences, and other personal data that is no longer required to provide the service.</p>

                <h3>Data we may retain</h3>
                <p>Invoices, payment and tax records, fraud-prevention records, dispute records, and information required for legal or regulatory compliance may be retained for the period required by applicable law. Residual encrypted backup copies may remain until routine backup expiry, normally no longer than 90 days.</p>

                <div class="deletion-notice">
                    Submitting this form does not immediately delete an account. Verification protects users from unauthorised deletion requests.
                </div>
            </article>

            <article class="deletion-card">
                <h2>Submit a deletion request</h2>

                @if (session('deletion_request_reference'))
                    <div class="deletion-success">
                        Your request has been received. Reference: {{ session('deletion_request_reference') }}. Our team will contact you for verification.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="deletion-errors">
                        Please review the highlighted fields and submit the form again.
                    </div>
                @endif

                <form method="POST" action="{{ route('account-deletion.store') }}">
                    @csrf
                    <div class="deletion-field">
                        <label for="name">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="120" required>
                        @error('name') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="deletion-field">
                        <label for="email">Registered email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="190" required>
                        @error('email') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="deletion-field">
                        <label for="account_type">Account type</label>
                        <select id="account_type" name="account_type" required>
                            <option value="">Select account type</option>
                            <option value="candidate" @selected(old('account_type') === 'candidate')>Candidate</option>
                            <option value="client" @selected(old('account_type') === 'client')>Client / Employer</option>
                            <option value="partner" @selected(old('account_type') === 'partner')>Sourcing Partner / Vendor</option>
                            <option value="referral_partner" @selected(old('account_type') === 'referral_partner')>Referral Partner</option>
                            <option value="other" @selected(old('account_type') === 'other')>Other</option>
                        </select>
                        @error('account_type') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="deletion-field">
                        <label for="reason">Reason (optional)</label>
                        <textarea id="reason" name="reason" rows="4" maxlength="1000">{{ old('reason') }}</textarea>
                        @error('reason') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="deletion-honeypot" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <label class="deletion-check">
                        <input name="confirmation" type="checkbox" value="1" required @checked(old('confirmation'))>
                        <span>I understand that verified deletion is permanent and may remove access to my account, profile, applications, jobs, or partner records.</span>
                    </label>
                    @error('confirmation') <small>{{ $message }}</small> @enderror

                    <button class="deletion-submit" type="submit">Request account deletion</button>
                </form>
            </article>
        </div>
    </div>
</section>
@endsection

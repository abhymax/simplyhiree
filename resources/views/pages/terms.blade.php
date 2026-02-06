@extends('layouts.web')

@section('title', 'Terms of Service')

@section('content')
    <section class="pt-32 pb-20 bg-slate-50">
        <div class="container mx-auto px-6 max-w-4xl bg-white p-10 md:p-16 rounded-3xl shadow-sm border border-slate-100">
            <h1 class="text-4xl font-bold text-slate-900 mb-2">Terms of Service</h1>
            <p class="text-slate-500 mb-10 text-sm uppercase tracking-wide">Last updated: {{ date('F d, Y') }}</p>

            <div class="prose prose-lg prose-slate max-w-none">
                <p>Welcome to SimplyHiree. By accessing or using our website, mobile application, or any other services (collectively, the "Services"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree, please do not use our Services.</p>

                <h3>1. Account Registration</h3>
                <p>To access certain features, you must register for an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>
                <ul>
                    <li>You are responsible for safeguarding your password.</li>
                    <li>You agree not to disclose your password to any third party.</li>
                    <li>You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</li>
                </ul>

                <h3>2. User Conduct</h3>
                <p>You agree not to engage in any of the following prohibited activities:</p>
                <ul>
                    <li>Copying, distributing, or disclosing any part of the Services in any medium.</li>
                    <li>Using any automated system, including "robots," "spiders," "offline readers," etc., to access the Services.</li>
                    <li>Transmitting spam, chain letters, or other unsolicited email.</li>
                    <li>Attempting to interfere with, compromise the system integrity or security, or decipher any transmissions to or from the servers running the Services.</li>
                </ul>

                <h3>3. Job Postings and Applications</h3>
                <p>SimplyHiree acts as a venue for employers to post job opportunities and candidates to post resumes. We do not screen or censor the listings, including profiles offered. We are not involved in the actual transaction between employers and candidates. As a result, we have no control over the quality, safety, or legality of the jobs or resumes posted.</p>

                <h3>4. Limitation of Liability</h3>
                <p>To the maximum extent permitted by applicable law, SimplyHiree shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly.</p>

                <div class="bg-indigo-50 border-l-4 border-primary p-4 mt-8">
                    <p class="text-sm text-indigo-800 font-medium m-0">If you have any questions about these Terms, please contact us at <a href="mailto:legal@simplyhiree.com" class="underline">legal@simplyhiree.com</a>.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
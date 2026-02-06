@extends('layouts.web')

@section('title', 'Privacy Policy')

@section('content')
    <section class="pt-32 pb-20 bg-slate-50">
        <div class="container mx-auto px-6 max-w-4xl bg-white p-10 md:p-16 rounded-3xl shadow-sm border border-slate-100">
            <h1 class="text-4xl font-bold text-slate-900 mb-2">Privacy Policy</h1>
            <p class="text-slate-500 mb-10 text-sm uppercase tracking-wide">Last updated: {{ date('F d, Y') }}</p>

            <div class="prose prose-lg prose-slate max-w-none">
                <p>At SimplyHiree, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>

                <h3>1. Information We Collect</h3>
                <p>We collect information that you voluntarily provide to us when you register on the Services, express an interest in obtaining information about us or our products and Services, when you participate in activities on the Services, or otherwise when you contact us.</p>
                <ul>
                    <li><strong>Personal Data:</strong> Name, email address, phone number, and professional resume data.</li>
                    <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the Site, such as your IP address, your browser type, your operating system, your access times, and the pages you have viewed.</li>
                </ul>

                <h3>2. How We Use Your Information</h3>
                <p>Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the Site to:</p>
                <ul>
                    <li>Create and manage your account.</li>
                    <li>Email you regarding your account or order.</li>
                    <li>Facilitate the job matching process between Candidates and Employers.</li>
                    <li>Compile anonymous statistical data and analysis for use internally.</li>
                </ul>

                <h3>3. Disclosure of Your Information</h3>
                <p>We may share information we have collected about you in certain situations. Your information may be disclosed as follows:</p>
                <p><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others, we may share your information as permitted or required by any applicable law, rule, or regulation.</p>

                <h3>4. Security of Your Information</h3>
                <p>We use administrative, technical, and physical security measures to help protect your personal information. While we have taken reasonable steps to secure the personal information you provide to us, please be aware that despite our efforts, no security measures are perfect or impenetrable, and no method of data transmission can be guaranteed against any interception or other type of misuse.</p>

                 <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mt-8">
                    <p class="text-sm text-emerald-800 font-medium m-0">Questions? Reach out to our Data Protection Officer at <a href="mailto:privacy@simplyhiree.com" class="underline">privacy@simplyhiree.com</a>.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
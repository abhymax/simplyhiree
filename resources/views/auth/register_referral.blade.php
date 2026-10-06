<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-black text-white">Referral Partner Registration</h2>
        <p class="mt-1 text-sm text-slate-400">Refer companies and earn after Finance verifies payment.</p>
    </div>

    <form id="referral-form" method="POST" action="{{ route('register.referral') }}" class="space-y-4">
        @csrf

        <input name="name" required value="{{ old('name') }}" placeholder="Full name" class="w-full rounded border-slate-600 bg-slate-900 text-white">
        <input name="email" type="email" required value="{{ old('email') }}" placeholder="Email" class="w-full rounded border-slate-600 bg-slate-900 text-white">
        <input id="phone_number" name="phone_number" required value="{{ old('phone_number') }}" placeholder="10-digit mobile" class="w-full rounded border-slate-600 bg-slate-900 text-white">
        <select name="partner_type" class="w-full rounded border-slate-600 bg-slate-900 text-white">
            @foreach(['Individual', 'Consultant', 'Freelancer', 'Ex HR', 'Sales Person', 'Channel Partner'] as $type)
                <option value="{{ $type }}" @selected(old('partner_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <input name="password" type="password" required placeholder="Password" class="w-full rounded border-slate-600 bg-slate-900 text-white">
        <input name="password_confirmation" type="password" required placeholder="Confirm password" class="w-full rounded border-slate-600 bg-slate-900 text-white">
        <input type="hidden" id="otp_verification_token" name="otp_verification_token">

        <div class="flex gap-2">
            <input id="otp" maxlength="6" inputmode="numeric" placeholder="6-digit OTP" class="min-w-0 flex-1 rounded border-slate-600 bg-slate-900 text-white">
            <button type="button" id="send" class="rounded bg-indigo-600 px-3 text-white">Send OTP</button>
            <button type="button" id="verify" class="rounded bg-emerald-600 px-3 text-white">Verify</button>
        </div>
        <p id="otp-status" class="text-xs text-slate-400"></p>

        @include('referral.partials.agreement', ['dark' => true])

        @if($errors->any())
            <p class="text-sm text-rose-300">{{ $errors->first() }}</p>
        @endif

        <button class="w-full rounded bg-indigo-600 py-3 font-bold text-white">Submit for approval</button>
    </form>

    <script>
        const phone = document.getElementById('phone_number');
        const otp = document.getElementById('otp');
        const token = document.getElementById('otp_verification_token');
        const status = document.getElementById('otp-status');

        async function call(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'OTP request failed');
            return data;
        }

        document.getElementById('send').onclick = async () => {
            try {
                status.textContent = (await call('/api/otp/send', {
                    phone_number: phone.value,
                    purpose: 'registration',
                    role: 'referral_partner',
                })).message;
            } catch (error) {
                status.textContent = error.message;
            }
        };

        document.getElementById('verify').onclick = async () => {
            try {
                const data = await call('/api/otp/verify', {
                    phone_number: phone.value,
                    otp: otp.value,
                    purpose: 'registration',
                    role: 'referral_partner',
                });
                token.value = data.verification_token;
                status.textContent = 'Phone verified.';
            } catch (error) {
                token.value = '';
                status.textContent = error.message;
            }
        };

        document.getElementById('referral-form').onsubmit = event => {
            if (!token.value) {
                event.preventDefault();
                status.textContent = 'Verify your phone first.';
            }
        };
    </script>
</x-guest-layout>

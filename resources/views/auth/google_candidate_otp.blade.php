<x-guest-layout>
    <form method="POST" action="{{ route('google.phone.verify.submit') }}">
        @csrf

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Verify Phone to Continue</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Hi {{ $userName }}, please verify your mobile number on WhatsApp OTP to complete Google login.
            </p>
        </div>

        <div>
            <x-input-label for="phone_number" :value="__('Phone Number (India)')" />
            <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number', $phoneNumber)" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="{{ old('otp_verification_token') }}">

        <div class="mt-4">
            <x-input-label for="phone_otp" :value="__('WhatsApp OTP')" />
            <div class="flex gap-2">
                <x-text-input id="phone_otp" class="block mt-1 w-full" type="text" maxlength="6" placeholder="Enter 6-digit OTP" />
                <button type="button" id="send_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">Send OTP</button>
                <button type="button" id="verify_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500">Verify</button>
            </div>
            <p id="otp_status" class="mt-2 text-sm text-gray-500"></p>
            <x-input-error :messages="$errors->get('otp_verification_token')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6 gap-2">
            <a href="{{ route('login') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                Cancel
            </a>

            <x-primary-button>
                {{ __('Continue to Dashboard') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<script>
    const otpForm = document.querySelector('form[action="{{ route('google.phone.verify.submit') }}"]');
    const phoneInput = document.getElementById('phone_number');
    const otpInput = document.getElementById('phone_otp');
    const tokenInput = document.getElementById('otp_verification_token');
    const sendBtn = document.getElementById('send_otp_btn');
    const verifyBtn = document.getElementById('verify_otp_btn');
    const statusEl = document.getElementById('otp_status');

    function setOtpStatus(message, ok = false) {
        statusEl.textContent = message;
        statusEl.className = ok ? 'mt-2 text-sm text-emerald-600' : 'mt-2 text-sm text-rose-600';
    }

    function resetVerificationToken() {
        tokenInput.value = '';
        if (otpInput.value.trim() !== '') {
            otpInput.value = '';
        }
    }

    phoneInput.addEventListener('input', resetVerificationToken);

    sendBtn.addEventListener('click', async function () {
        const phone = phoneInput.value.trim();
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            setOtpStatus('Enter a valid 10-digit Indian mobile number.');
            return;
        }

        sendBtn.disabled = true;
        setOtpStatus('Sending OTP...', true);

        try {
            const response = await fetch('/api/otp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone_number: phone,
                    purpose: '{{ $otpPurpose ?? 'google_login' }}',
                    role: '{{ $otpRole ?? 'candidate' }}',
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                setOtpStatus(data.message || 'Could not send OTP.');
                return;
            }

            setOtpStatus(data.message || 'OTP sent to WhatsApp.', true);
        } catch (error) {
            setOtpStatus('Could not send OTP. Please try again.');
        } finally {
            sendBtn.disabled = false;
        }
    });

    verifyBtn.addEventListener('click', async function () {
        const phone = phoneInput.value.trim();
        const otp = otpInput.value.trim();

        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            setOtpStatus('Enter a valid 10-digit Indian mobile number.');
            return;
        }
        if (!/^[0-9]{6}$/.test(otp)) {
            setOtpStatus('Enter valid 6-digit OTP.');
            return;
        }

        verifyBtn.disabled = true;
        setOtpStatus('Verifying OTP...', true);

        try {
            const response = await fetch('/api/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone_number: phone,
                    otp: otp,
                    purpose: '{{ $otpPurpose ?? 'google_login' }}',
                    role: '{{ $otpRole ?? 'candidate' }}',
                }),
            });
            const data = await response.json();
            if (!response.ok) {
                tokenInput.value = '';
                setOtpStatus(data.message || 'OTP verification failed.');
                return;
            }

            tokenInput.value = (data.verification_token || '').toString();
            setOtpStatus('Phone verified successfully.', true);
        } catch (error) {
            tokenInput.value = '';
            setOtpStatus('OTP verification failed.');
        } finally {
            verifyBtn.disabled = false;
        }
    });

    otpForm.addEventListener('submit', function (event) {
        if (!tokenInput.value) {
            event.preventDefault();
            setOtpStatus('Please verify your OTP before continuing.');
        }
    });
</script>

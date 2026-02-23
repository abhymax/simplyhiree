<x-guest-layout>
    <form method="POST" action="{{ route('register.partner') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Agency / Partner Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone_number" :value="__('Phone Number (India)')" />
            <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="{{ old('otp_verification_token') }}">

        <div class="mt-3">
            <x-input-label for="phone_otp" :value="__('WhatsApp OTP Verification')" />
            <div class="flex gap-2">
                <x-text-input id="phone_otp" class="block mt-1 w-full" type="text" maxlength="6" placeholder="Enter 6-digit OTP" />
                <button type="button" id="send_partner_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">Send OTP</button>
                <button type="button" id="verify_partner_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500">Verify</button>
            </div>
            <p id="partner_otp_status" class="mt-2 text-sm text-gray-500"></p>
            <x-input-error :messages="$errors->get('otp_verification_token')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="company_type" :value="__('Partner Type')" />
            <select id="company_type" name="company_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="" disabled selected>Select Type</option>
                <option value="Placement Agency" {{ old('company_type') == 'Placement Agency' ? 'selected' : '' }}>Placement Agency</option>
                <option value="Freelancer" {{ old('company_type') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                <option value="Recruiter" {{ old('company_type') == 'Recruiter' ? 'selected' : '' }}>Recruiter</option>
            </select>
            <x-input-error :messages="$errors->get('company_type')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div style="position: relative;">
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-10">
                <div id="togglePassword" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%);" class="flex items-center cursor-pointer">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-gray-500">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                    </svg>
                    <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-gray-500 hidden">
                        <path d="M3.53 2.47a.75.75 0 00-1.06 1.06l18 18a.75.75 0 101.06-1.06l-18-18zM22.676 12.553a11.249 11.249 0 01-2.631 4.31l-3.099-3.099a5.25 5.25 0 00-6.71-6.71L7.759 4.577a11.217 11.217 0 014.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113z" />
                        <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A5.25 5.25 0 0115.75 12zM12.53 15.713l-4.244-4.243a5.25 5.25 0 004.244 4.243z" />
                        <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 00-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 016.75 12z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div style="position: relative;">
                 <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-10">
                <div id="togglePasswordConfirmation" style="position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%);" class="flex items-center cursor-pointer">
                    <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-gray-500">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                        <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                    </svg>
                    <svg id="eyeSlashIconConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-gray-500 hidden">
                        <path d="M3.53 2.47a.75.75 0 00-1.06 1.06l18 18a.75.75 0 101.06-1.06l-18-18zM22.676 12.553a11.249 11.249 0 01-2.631 4.31l-3.099-3.099a5.25 5.25 0 00-6.71-6.71L7.759 4.577a11.217 11.217 0 014.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113z" />
                        <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A5.25 5.25 0 0115.75 12zM12.53 15.713l-4.244-4.243a5.25 5.25 0 004.244 4.243z" />
                        <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 00-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 016.75 12z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <p class="mt-6 text-xs text-gray-500">
            Partner signup requires WhatsApp OTP verification above.
        </p>

        <div class="mt-4">
            <a href="{{ route('google.login', ['role' => 'partner']) }}" class="w-full flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-5 w-5 mr-2" alt="Google Logo">
                Continue with Google (Partner)
            </a>
            <p class="mt-2 text-xs text-gray-500">After Google login, phone verification via WhatsApp OTP is required.</p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register as Partner') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<script>
    function setupPasswordToggle(toggleId, passwordId, eyeIconId, eyeSlashIconId) {
        const toggleButton = document.getElementById(toggleId);
        const passwordInput = document.getElementById(passwordId);
        const eyeIcon = document.getElementById(eyeIconId);
        const eyeSlashIcon = document.getElementById(eyeSlashIconId);

        toggleButton.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // This just toggles the 'hidden' class on the two icons
            eyeIcon.classList.toggle('hidden');
            eyeSlashIcon.classList.toggle('hidden');
        });
    }

    // Setup for the main password field
    setupPasswordToggle('togglePassword', 'password', 'eyeIcon', 'eyeSlashIcon');
    
    // Setup for the confirmation password field
    setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'eyeIconConfirm', 'eyeSlashIconConfirm');

    const partnerForm = document.querySelector('form[action="{{ route('register.partner') }}"]');
    const partnerPhone = document.getElementById('phone_number');
    const partnerOtp = document.getElementById('phone_otp');
    const partnerOtpToken = document.getElementById('otp_verification_token');
    const partnerSendBtn = document.getElementById('send_partner_otp_btn');
    const partnerVerifyBtn = document.getElementById('verify_partner_otp_btn');
    const partnerStatus = document.getElementById('partner_otp_status');

    function setPartnerOtpStatus(message, ok = false) {
        partnerStatus.textContent = message;
        partnerStatus.className = ok ? 'mt-2 text-sm text-emerald-600' : 'mt-2 text-sm text-rose-600';
    }

    function resetPartnerOtpVerification() {
        partnerOtpToken.value = '';
        if (partnerOtp.value.trim() !== '') {
            partnerOtp.value = '';
        }
    }

    partnerPhone.addEventListener('input', resetPartnerOtpVerification);

    partnerSendBtn.addEventListener('click', async function () {
        const phone = partnerPhone.value.trim();
        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            setPartnerOtpStatus('Enter a valid 10-digit Indian mobile number.');
            return;
        }

        partnerSendBtn.disabled = true;
        setPartnerOtpStatus('Sending OTP...', true);

        try {
            const response = await fetch('/api/otp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone_number: phone,
                    purpose: 'registration',
                    role: 'partner',
                }),
            });
            const data = await response.json();
            if (!response.ok) {
                setPartnerOtpStatus(data.message || 'Could not send OTP.');
                return;
            }
            setPartnerOtpStatus(data.message || 'OTP sent to WhatsApp.', true);
        } catch (error) {
            setPartnerOtpStatus('Could not send OTP. Please try again.');
        } finally {
            partnerSendBtn.disabled = false;
        }
    });

    partnerVerifyBtn.addEventListener('click', async function () {
        const phone = partnerPhone.value.trim();
        const otp = partnerOtp.value.trim();

        if (!/^[6-9][0-9]{9}$/.test(phone)) {
            setPartnerOtpStatus('Enter a valid 10-digit Indian mobile number.');
            return;
        }
        if (!/^[0-9]{6}$/.test(otp)) {
            setPartnerOtpStatus('Enter valid 6-digit OTP.');
            return;
        }

        partnerVerifyBtn.disabled = true;
        setPartnerOtpStatus('Verifying OTP...', true);

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
                    purpose: 'registration',
                    role: 'partner',
                }),
            });
            const data = await response.json();
            if (!response.ok) {
                partnerOtpToken.value = '';
                setPartnerOtpStatus(data.message || 'OTP verification failed.');
                return;
            }
            partnerOtpToken.value = (data.verification_token || '').toString();
            setPartnerOtpStatus('Phone verified successfully.', true);
        } catch (error) {
            partnerOtpToken.value = '';
            setPartnerOtpStatus('OTP verification failed.');
        } finally {
            partnerVerifyBtn.disabled = false;
        }
    });

    partnerForm.addEventListener('submit', function (event) {
        if (!partnerOtpToken.value) {
            event.preventDefault();
            setPartnerOtpStatus('Please verify your phone with OTP before registration.');
        }
    });
</script>

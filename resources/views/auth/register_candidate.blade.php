<x-guest-layout>
    <form method="POST" action="{{ route('register.candidate') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone_number" :value="__('Phone Number (India)')" />
            <x-text-input
                id="phone_number"
                class="block mt-1 w-full"
                type="tel"
                name="phone_number"
                :value="old('phone_number')"
                required
                maxlength="10"
                minlength="10"
                pattern="[6-9][0-9]{9}"
                placeholder="10-digit mobile number" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="{{ old('otp_verification_token') }}">

        <div class="mt-3">
            <x-input-label for="phone_otp" :value="__('WhatsApp OTP Verification')" />
            <div class="flex gap-2">
                <x-text-input id="phone_otp" class="block mt-1 w-full" type="text" maxlength="6" placeholder="Enter 6-digit OTP" />
                <button type="button" id="send_candidate_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">Send OTP</button>
                <button type="button" id="verify_candidate_otp_btn" class="mt-1 px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500">Verify</button>
            </div>
            <p id="candidate_otp_status" class="mt-2 text-sm text-gray-500"></p>
            <x-input-error :messages="$errors->get('otp_verification_token')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePassword('password', 'eyeIcon', 'eyeSlashIcon')">
                    <svg id="eyeIcon" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eyeSlashIcon" class="w-5 h-5 text-gray-500 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePassword('password_confirmation', 'eyeIconConfirm', 'eyeSlashIconConfirm')">
                     <svg id="eyeIconConfirm" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eyeSlashIconConfirm" class="w-5 h-5 text-gray-500 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <input type="hidden" name="role" value="candidate">

        <div class="mt-6">
            <a href="{{ route('google.login', ['role' => 'candidate']) }}" class="w-full flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-5 w-5 mr-2" alt="Google Logo">
                Sign up with Google
            </a>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register as Candidate') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const candidateForm = document.querySelector('form[action="{{ route('register.candidate') }}"]');
        const candidatePhone = document.getElementById('phone_number');
        const candidateOtp = document.getElementById('phone_otp');
        const candidateOtpToken = document.getElementById('otp_verification_token');
        const candidateSendBtn = document.getElementById('send_candidate_otp_btn');
        const candidateVerifyBtn = document.getElementById('verify_candidate_otp_btn');
        const candidateStatus = document.getElementById('candidate_otp_status');

        function setCandidateOtpStatus(message, ok = false) {
            candidateStatus.textContent = message;
            candidateStatus.className = ok ? 'mt-2 text-sm text-emerald-600' : 'mt-2 text-sm text-rose-600';
        }

        function resetCandidateOtpVerification() {
            candidateOtpToken.value = '';
            if (candidateOtp.value.trim() !== '') {
                candidateOtp.value = '';
            }
        }

        candidatePhone.addEventListener('input', resetCandidateOtpVerification);

        candidateSendBtn.addEventListener('click', async function () {
            const phone = candidatePhone.value.trim();
            if (!/^[6-9][0-9]{9}$/.test(phone)) {
                setCandidateOtpStatus('Enter a valid 10-digit Indian mobile number.');
                return;
            }

            candidateSendBtn.disabled = true;
            setCandidateOtpStatus('Sending OTP...', true);

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
                        role: 'candidate',
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    setCandidateOtpStatus(data.message || 'Could not send OTP.');
                    return;
                }
                setCandidateOtpStatus(data.message || 'OTP sent to WhatsApp.', true);
            } catch (error) {
                setCandidateOtpStatus('Could not send OTP. Please try again.');
            } finally {
                candidateSendBtn.disabled = false;
            }
        });

        candidateVerifyBtn.addEventListener('click', async function () {
            const phone = candidatePhone.value.trim();
            const otp = candidateOtp.value.trim();

            if (!/^[6-9][0-9]{9}$/.test(phone)) {
                setCandidateOtpStatus('Enter a valid 10-digit Indian mobile number.');
                return;
            }
            if (!/^[0-9]{6}$/.test(otp)) {
                setCandidateOtpStatus('Enter valid 6-digit OTP.');
                return;
            }

            candidateVerifyBtn.disabled = true;
            setCandidateOtpStatus('Verifying OTP...', true);

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
                        role: 'candidate',
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    candidateOtpToken.value = '';
                    setCandidateOtpStatus(data.message || 'OTP verification failed.');
                    return;
                }
                candidateOtpToken.value = (data.verification_token || '').toString();
                setCandidateOtpStatus('Phone verified successfully.', true);
            } catch (error) {
                candidateOtpToken.value = '';
                setCandidateOtpStatus('OTP verification failed.');
            } finally {
                candidateVerifyBtn.disabled = false;
            }
        });

        candidateForm.addEventListener('submit', function (event) {
            if (!candidateOtpToken.value) {
                event.preventDefault();
                setCandidateOtpStatus('Please verify your phone with OTP before registration.');
            }
        });

        function togglePassword(inputId, eyeId, slashId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const slash = document.getElementById(slashId);
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                slash.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                slash.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>

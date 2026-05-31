<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-black text-white tracking-tight">Register as Staffing Partner</h2>
        <p class="text-slate-400 text-xs mt-1">Scale your agency placements and serve premier corporate clients.</p>
    </div>

    <form method="POST" action="{{ route('register.partner') }}">
        @csrf

        @if(!empty($inviteToken))
            <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm">
                <i class="fa-solid fa-handshake mr-1"></i>
                You've been invited to join SimplyHiree
                @if(!empty($invitedBy))
                    by <strong>{{ $invitedBy }}</strong>
                @endif.
                Complete the form below — you'll be automatically connected to them after admin approval.
            </div>
            <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Row 1: Partner Name & Partner Type --}}
            <div>
                <x-input-label for="name" :value="__('Agency / Partner Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $prefillName ?? '')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="company_type" :value="__('Partner Type')" />
                <select id="company_type" name="company_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                    <option value="" disabled selected>Select Type</option>
                    <option value="Placement Agency" {{ old('company_type') == 'Placement Agency' ? 'selected' : '' }}>Placement Agency</option>
                    <option value="Freelancer" {{ old('company_type') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                    <option value="Recruiter" {{ old('company_type') == 'Recruiter' ? 'selected' : '' }}>Recruiter</option>
                </select>
                <x-input-error :messages="$errors->get('company_type')" class="mt-2" />
            </div>

            {{-- Row 2: Phone & Email --}}
            <div>
                <x-input-label for="phone_number" :value="__('Phone Number (India)')" />
                <x-text-input id="phone_number" class="block mt-1 w-full" type="tel" name="phone_number" :value="old('phone_number', $prefillPhone ?? '')" required autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $prefillEmail ?? '')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Row 3: Password & Confirm Password --}}
            <div>
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

            <div>
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

            {{-- Row 4: WhatsApp OTP Verification --}}
            <div class="md:col-span-2">
                <input type="hidden" name="otp_verification_token" id="otp_verification_token" value="{{ old('otp_verification_token') }}">
                
                <x-input-label for="phone_otp" :value="__('WhatsApp OTP Verification')" />
                <div class="flex gap-2">
                    <x-text-input id="phone_otp" class="block mt-1 w-full" type="text" maxlength="6" placeholder="Enter 6-digit OTP" />
                    <button type="button" id="send_partner_otp_btn" class="mt-1 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500 whitespace-nowrap">Send OTP</button>
                    <button type="button" id="verify_partner_otp_btn" class="mt-1 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-500 whitespace-nowrap">Verify</button>
                </div>
                <p id="partner_otp_status" class="mt-2 text-xs text-gray-400"></p>
                <x-input-error :messages="$errors->get('otp_verification_token')" class="mt-2" />
            </div>
        </div>

        {{-- Row 5: Submit & Google SSO --}}
        <div class="pt-2 grid grid-cols-1 md:grid-cols-2 gap-4 items-center border-t border-white/5 mt-4">
            <div>
                <a href="{{ route('google.login', ['role' => 'partner']) }}" class="w-full flex justify-center items-center px-4 py-3 bg-slate-950/40 border border-white/8 hover:border-blue-500/30 text-slate-200 hover:text-white rounded-xl font-extrabold text-[10px] uppercase tracking-widest shadow-md hover:bg-slate-900/60 transition-all duration-150">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-4 w-4 mr-2" alt="Google Logo">
                    Google Signup
                </a>
            </div>

            <div class="flex justify-between items-center">
                <a class="underline text-xs text-slate-400 hover:text-white transition" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-primary-button class="ms-2 text-xs py-2 px-4 rounded-xl">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
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

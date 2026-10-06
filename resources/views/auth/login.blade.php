<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePasswordVisibility()">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </div>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
             <a href="{{ route('google.login') }}" class="w-full flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <img src="{{ asset('images/google.svg') }}" class="h-5 w-5 mr-2" alt="Google">
                Continue with Google
            </a>
            <p class="mt-2 text-xs text-gray-500">After Google login, mobile verification via WhatsApp OTP is required.</p>
        </div>

        <div class="block mt-4 flex justify-between items-center">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3 w-full justify-center">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
    @if(session('account_on_hold'))
        <!-- FontAwesome for the Reactivation Modal -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Reactivation Request Modal -->
        <div x-data="{ 
                 showHoldModal: true, 
                 holdMessage: 'Hi Superadmin, my Sourcing Partner account ({{ session('hold_email') }}) has been put on hold due to 15 days of inactivity. Please reactivate my account.', 
                 isSending: false, 
                 sendSuccess: false, 
                 sendError: '' 
             }"
             x-show="showHoldModal"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md px-4"
             role="dialog" aria-modal="true"
             style="display: none;">
            
            <div class="bg-slate-900 border border-amber-500/40 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
                 @click.away="showHoldModal = false">
                 
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-amber-400/30 bg-amber-500/10 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-500/20 border border-amber-400/40 rounded-xl text-amber-300">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-extrabold text-lg leading-tight">Account Put On Hold</h3>
                            <p class="text-amber-200/80 text-xs mt-0.5">Inactive for 15+ Days</p>
                        </div>
                    </div>
                    <button type="button" @click="showHoldModal = false"
                            class="text-slate-300 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition flex-shrink-0"
                            aria-label="Close">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-5">
                    <div x-show="!sendSuccess">
                        <p class="text-slate-300 text-sm leading-relaxed mb-4">
                            Your partner account is currently on hold. To request reactivation, you can submit the request form below or contact the Superadmin directly via WhatsApp.
                        </p>

                        <!-- Error Message if any -->
                        <div x-show="sendError" x-text="sendError" class="mb-4 p-3 bg-rose-500/20 border border-rose-500/40 rounded-xl text-rose-300 text-xs font-semibold" style="display: none;"></div>

                        <!-- Reactivation Form -->
                        <form @submit.prevent="
                            isSending = true;
                            sendError = '';
                            fetch('{{ route('partner.reactivation.store', [], false) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    email: '{{ session('hold_email') }}',
                                    message: holdMessage
                                })
                            })
                            .then(response => {
                                isSending = false;
                                if (response.ok) {
                                    sendSuccess = true;
                                } else {
                                    return response.json().then(data => {
                                        throw new Error(data.message || 'Failed to send the request. Please try again.');
                                    });
                                }
                            })
                            .catch(error => {
                                isSending = false;
                                sendError = error.message || 'An error occurred. Please try again.';
                            });
                        ">
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Registered Email</label>
                                <input type="email" readonly value="{{ session('hold_email') }}" class="w-full bg-slate-800 border border-white/10 rounded-xl text-slate-300 text-sm h-11 px-3 focus:outline-none">
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Message to Superadmin</label>
                                <textarea x-model="holdMessage" required rows="3" class="w-full bg-slate-800 border border-white/10 rounded-xl text-white text-sm p-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all"></textarea>
                            </div>

                            <div class="flex flex-col gap-2 mt-5">
                                <button type="submit" :disabled="isSending"
                                        class="w-full py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 text-sm font-extrabold rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-paper-plane" x-show="!isSending"></i>
                                    <i class="fa-solid fa-spinner animate-spin" x-show="isSending" style="display: none;"></i>
                                    <span x-text="isSending ? 'Sending Request...' : 'Send Reactivation Request'"></span>
                                </button>
                                
                                <a href="https://wa.me/918888353984?text=Hi%20Superadmin,%20my%20Sourcing%20Partner%20account%20({{ session('hold_email') }})%20has%20been%20placed%20on%20hold%20due%20to%2015%20days%20of%20inactivity.%20Please%20reactivate%20my%20account."
                                   target="_blank"
                                   class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition shadow-lg flex items-center justify-center gap-2 decoration-none no-underline">
                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                    Contact via WhatsApp
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Success State -->
                    <div x-show="sendSuccess" style="display: none;" class="text-center py-6">
                        <div class="w-16 h-16 bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-circle-check text-3xl"></i>
                        </div>
                        <h4 class="text-white font-bold text-lg mb-2">Request Sent Successfully</h4>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Your reactivation request has been received. The Superadmin team will review it and notify you once your account is active again.
                        </p>
                        <button type="button" @click="showHoldModal = false"
                                class="px-6 py-2.5 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition border border-white/20">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>

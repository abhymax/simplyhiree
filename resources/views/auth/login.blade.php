<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h2 class="text-2xl font-black text-white tracking-tight">Access Account</h2>
        <p class="text-slate-400 text-xs mt-1">Please enter your credentials or continue with Google to access your dashboard.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400 text-xs font-semibold" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePasswordVisibility()">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 hover:text-white transition">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-400 hover:text-white transition hidden">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </div>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400 text-xs font-semibold" />
        </div>

        <div class="block mt-4 flex justify-between items-center text-xs">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded bg-slate-950/60 border-white/10 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-xs text-slate-400 hover:text-white transition rounded-md focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" class="w-full justify-center text-center font-extrabold uppercase">
                {{ __('Log in') }}
            </button>
        </div>

        <div class="relative py-2 flex items-center justify-center">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/5"></div>
            </div>
            <span class="relative z-10 bg-[#0d1222] px-3 text-[10px] text-slate-500 font-bold uppercase tracking-wider">or connect via</span>
        </div>

        <div>
             <a href="{{ route('google.login') }}" class="w-full flex justify-center items-center px-4 py-3 bg-slate-950/40 border border-white/8 hover:border-blue-500/30 text-slate-200 hover:text-white rounded-xl font-extrabold text-xs uppercase tracking-widest shadow-lg hover:bg-slate-900/60 active:scale-[0.98] transition-all duration-150">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="h-5 w-5 mr-2" alt="Google Logo">
                Continue with Google
            </a>
            <p class="mt-2 text-[10px] text-slate-500 font-medium">After Google authentication, mobile verification via WhatsApp OTP is required.</p>
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
</x-guest-layout>

<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? Enter your registered mobile number to receive a temporary password on WhatsApp, or enter your email to receive a reset link.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Mobile Number / Email -->
        <div>
            <x-input-label for="identifier" :value="__('Mobile Number or Email')" />
            <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier', old('email'))" required autofocus />
            <p class="mt-1 text-xs text-gray-500">For mobile: enter 10-digit Indian number.</p>
            <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Submit') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

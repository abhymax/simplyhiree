<section>
    <header>
        <h2 class="text-xl font-bold text-white">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="!text-blue-100" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                class="mt-1 block w-full !bg-slate-900/50 !border-white/20 !text-white rounded-xl"
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 !text-rose-300" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="!text-blue-100" />
            <x-text-input id="update_password_password" name="password" type="password"
                class="mt-1 block w-full !bg-slate-900/50 !border-white/20 !text-white rounded-xl"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 !text-rose-300" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="!text-blue-100" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full !bg-slate-900/50 !border-white/20 !text-white rounded-xl"
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 !text-rose-300" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white transition shadow-lg">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-300"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
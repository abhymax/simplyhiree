<section class="space-y-6">
    @php($isDeactivationAccount = auth()->user()?->hasRole('client') || auth()->user()?->hasRole('candidate'))
    <header>
        <h2 class="text-xl font-bold text-rose-200">
            {{ $isDeactivationAccount ? __('Deactivate Account') : __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            {{ $isDeactivationAccount
                ? __('Your account will be marked inactive and you can request reactivation from support later.')
                : __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-5 py-2.5 rounded-xl font-bold bg-rose-600 hover:bg-rose-700 text-white transition shadow-lg"
    >
        {{ $isDeactivationAccount ? __('Deactivate Account') : __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-white rounded-2xl">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900">
                {{ $isDeactivationAccount ? __('Are you sure you want to deactivate your account?') : __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-slate-600">
                {{ $isDeactivationAccount
                    ? __('Your account will be marked inactive and logged out immediately. Please enter your password to confirm.')
                    : __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
            </p>

            <div class="mt-5">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-xl border-slate-300"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 !text-rose-600" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold">
                    {{ $isDeactivationAccount ? __('Deactivate Account') : __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

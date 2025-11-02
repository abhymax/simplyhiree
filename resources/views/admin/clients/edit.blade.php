<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Client: ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-2xl font-semibold mb-2">Update Billable Period</h3>
                    <p class="mb-6 text-gray-600">Set the number of days after which this client is billable for a successful hire.</p>

                    <form action="{{ route('admin.clients.update', $user) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="billable_period_days" :value="__('Billable Period (in days)')" />
                            <x-text-input id="billable_period_days" 
                                          class="block mt-1 w-full md:w-1/2" 
                                          type="number" 
                                          name="billable_period_days" 
                                          :value="old('billable_period_days', $user->billable_period_days)" 
                                          required 
                                          min="1" />
                            <x-input-error :messages="$errors->get('billable_period_days')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                            <a href="{{ route('admin.clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
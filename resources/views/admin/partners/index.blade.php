<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Partner Management') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 font-medium transition duration-150">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> Back to Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-violet-500 to-purple-600 flex flex-col md:flex-row justify-between items-center">
                    <div class="text-white">
                        <h3 class="text-2xl font-bold">Registered Partners</h3>
                        <p class="text-purple-100 text-sm mt-1">Manage recruitment agencies and access.</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="{{ route('admin.partners.create') }}" class="inline-flex items-center bg-white text-purple-600 font-bold px-5 py-2.5 rounded-full shadow-md hover:bg-purple-50 hover:scale-105 transition transform">
                            <i class="fa-solid fa-plus mr-2"></i> New Partner
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Partner Name</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Joined On</th>
                                <th class="py-4 px-6 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold mr-3 text-lg">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $class = match($user->status) {
                                                'active' => 'bg-green-100 text-green-800 border-green-200',
                                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'on_hold' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'restricted' => 'bg-red-100 text-red-800 border-red-200',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $class }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-right text-sm font-medium">
                                        <div class="flex justify-end items-center gap-3">
                                            <a href="{{ route('admin.partners.show', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs bg-indigo-50 px-3 py-1 rounded transition">
                                                View Profile
                                            </a>
                                            
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition">
                                                    <i class="fa-solid fa-ellipsis-vertical px-2"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50 border border-gray-100 overflow-hidden" style="display: none;">
                                                    @if($user->status !== 'active')
                                                        <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="active">
                                                            <button class="w-full text-left px-4 py-3 text-sm text-green-600 hover:bg-green-50 flex items-center"><i class="fa-solid fa-check w-5"></i> Approve</button>
                                                        </form>
                                                    @endif
                                                    @if($user->status !== 'restricted')
                                                        <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="restricted">
                                                            <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center"><i class="fa-solid fa-ban w-5"></i> Restrict</button>
                                                        </form>
                                                    @endif
                                                    <button @click="$dispatch('open-modal', 'pwd-p-{{ $user->id }}')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-t flex items-center"><i class="fa-solid fa-key w-5"></i> Password</button>
                                                </div>
                                            </div>
                                        </div>

                                        <x-modal name="pwd-p-{{ $user->id }}">
                                            <div class="p-6 text-left">
                                                <h2 class="text-lg font-bold mb-4">Reset Password</h2>
                                                <form method="POST" action="{{ route('admin.users.credentials.update', $user->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4"><label class="block text-sm font-medium">New Password</label><input type="password" name="password" class="w-full rounded-md border-gray-300" required></div>
                                                    <div class="mb-6"><label class="block text-sm font-medium">Confirm</label><input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300" required></div>
                                                    <div class="flex justify-end"><button class="bg-indigo-600 text-white px-4 py-2 rounded-md">Update</button></div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-gray-500">No partners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
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
                <div class="px-8 py-6 bg-gradient-to-r from-blue-500 to-indigo-600 flex justify-between items-center">
                    <div class="text-white">
                        <h3 class="text-2xl font-bold">All Users</h3>
                        <p class="text-blue-100 text-sm mt-1">Manage candidates, partners, clients, and admins.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Identity</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">System Role</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Account Status</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Joined On</th>
                                <th class="py-4 px-6 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mr-3 border border-indigo-100">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @foreach($user->getRoleNames() as $role)
                                            <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide
                                                {{ $role == 'Superadmin' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $role == 'client' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $role == 'partner' ? 'bg-purple-100 text-purple-700' : '' }}
                                                {{ $role == 'candidate' ? 'bg-blue-100 text-blue-700' : '' }}">
                                                {{ $role }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-6">
                                        @php
                                            $colors = [
                                                'active' => 'bg-green-100 text-green-800 border-green-200',
                                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'on_hold' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'restricted' => 'bg-red-100 text-red-800 border-red-200',
                                            ];
                                            $class = $colors[$user->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $class }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->status)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-right text-sm font-medium">
                                        @if(!$user->hasRole('Superadmin'))
                                            <div class="flex justify-end items-center gap-3">
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition">
                                                        <i class="fa-solid fa-ellipsis-vertical px-2"></i>
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50 border border-gray-100 overflow-hidden" style="display: none;">
                                                        @if($user->status !== 'active')
                                                            <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="active">
                                                                <button class="w-full text-left px-4 py-3 text-sm text-green-600 hover:bg-green-50 flex items-center"><i class="fa-solid fa-check w-5"></i> Activate</button>
                                                            </form>
                                                        @endif
                                                        @if($user->status !== 'restricted')
                                                            <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="restricted">
                                                                <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center"><i class="fa-solid fa-ban w-5"></i> Restrict</button>
                                                            </form>
                                                        @endif
                                                        <button @click="$dispatch('open-modal', 'pwd-all-{{ $user->id }}')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-t flex items-center"><i class="fa-solid fa-key w-5"></i> Password</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <x-modal name="pwd-all-{{ $user->id }}">
                                                <div class="p-6 text-left">
                                                    <h2 class="text-lg font-bold mb-4">Reset Credentials</h2>
                                                    <p class="text-sm text-gray-500 mb-4">Set a new password for {{ $user->name }} ({{ $user->email }}).</p>
                                                    <form method="POST" action="{{ route('admin.users.credentials.update', $user->id) }}">
                                                        @csrf @method('PATCH')
                                                        <div class="mb-4"><label class="block text-sm font-medium">New Password</label><input type="password" name="password" class="w-full rounded-md border-gray-300" required></div>
                                                        <div class="mb-6"><label class="block text-sm font-medium">Confirm</label><input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300" required></div>
                                                        <div class="flex justify-end"><button class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Update Password</button></div>
                                                    </form>
                                                </div>
                                            </x-modal>
                                        @else
                                            <span class="text-xs text-gray-300 italic"><i class="fa-solid fa-shield-halved"></i> Protected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-500">No users found.</td></tr>
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
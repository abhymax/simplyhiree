<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sub-Admin Managers') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 font-medium transition duration-150">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> Back to Dashboard
                </a>
                
                <a href="{{ route('admin.sub_admins.create') }}" class="inline-flex items-center bg-indigo-600 text-white font-bold px-5 py-2.5 rounded-full shadow-md hover:bg-indigo-700 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus mr-2"></i> Add New Manager
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800">Team Members</h3>
                    <p class="text-gray-500 text-sm">Managers with special access permissions.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Manager</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Clients</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-4 px-6 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($managers as $manager)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-3">
                                                {{ substr($manager->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ $manager->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $manager->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex -space-x-2 overflow-hidden">
                                            @foreach($manager->assignedClients->take(3) as $client)
                                                <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 flex items-center justify-center text-xs font-bold" title="{{ $client->name }}">
                                                    {{ substr($client->name, 0, 1) }}
                                                </span>
                                            @endforeach
                                            @if($manager->assignedClients->count() > 3)
                                                <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                                    +{{ $manager->assignedClients->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($manager->assignedClients->isEmpty())
                                            <span class="text-gray-400 text-xs italic">No clients assigned</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($manager->status === 'active')
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold">Active</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-bold">{{ ucfirst($manager->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right text-sm font-medium" x-data>
                                        <div class="flex justify-end items-center gap-2">
                                            
                                            <button @click="$dispatch('open-modal', 'pwd-sub-{{ $manager->id }}')" class="text-gray-400 hover:text-indigo-600 p-2 rounded-full hover:bg-gray-100 transition" title="Change Password">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            <a href="{{ route('admin.sub_admins.edit', $manager->id) }}" class="text-blue-600 hover:text-blue-900 font-bold text-xs bg-blue-50 px-3 py-1.5 rounded transition">
                                                Edit
                                            </a>

                                            @if($manager->status !== 'active')
                                                <form action="{{ route('admin.users.status.update', $manager->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="text-green-600 hover:text-green-800 p-2 rounded-full hover:bg-green-50 transition" title="Activate">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.status.update', $manager->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="restricted">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition" title="Restrict">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <x-modal name="pwd-sub-{{ $manager->id }}">
                                            <div class="p-6 text-left">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h2 class="text-lg font-bold">Reset Password for {{ $manager->name }}</h2>
                                                    <button @click="$dispatch('close-modal', 'pwd-sub-{{ $manager->id }}')" class="text-gray-400 hover:text-gray-600">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.users.credentials.update', $manager->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium mb-1">New Password</label>
                                                        <input type="password" name="password" class="w-full rounded-md border-gray-300" required>
                                                    </div>
                                                    <div class="mb-6">
                                                        <label class="block text-sm font-medium mb-1">Confirm</label>
                                                        <input type="password" name="password_confirmation" class="w-full rounded-md border-gray-300" required>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-md">Update Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-gray-500">No managers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
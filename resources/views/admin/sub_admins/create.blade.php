<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add New Manager</h2>
    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('admin.sub_admins.store') }}" method="POST">
                @csrf
                
                <div class="bg-white p-6 rounded-2xl shadow-sm mb-6">
                    <h3 class="text-lg font-bold mb-4">Account Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm mb-6">
                    <h3 class="text-lg font-bold mb-4">Assign Clients</h3>
                    <p class="text-sm text-gray-500 mb-4">Select the clients this manager is allowed to manage/impersonate.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-2 border rounded-lg bg-gray-50">
                        @foreach($clients as $client)
                            <label class="flex items-center space-x-3 bg-white p-3 rounded shadow-sm border border-gray-100 hover:border-indigo-200 cursor-pointer">
                                <input type="checkbox" name="clients[]" value="{{ $client->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">{{ $client->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm mb-6">
                    <h3 class="text-lg font-bold mb-4">Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($permissions as $permission)
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ str_replace('_', ' ', ucfirst($permission)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.sub_admins.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md transition">Create Manager</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
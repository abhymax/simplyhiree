<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Superadmin Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-semibold">Welcome back, {{ Auth::user()->name }}!</h3>
                        <p class="text-gray-600">Here's an overview of the platform's activity.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-medium text-gray-700">{{ date('l, F j, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ date('g:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Card 1: Pending Jobs (Already a link) -->
                <a href="{{ route('admin.jobs.pending') }}" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-yellow-600 uppercase">Pending Jobs</p>
                                <p class="text-4xl font-bold text-gray-800">{{ $pendingJobs }}</p>
                            </div>
                            <div class="text-yellow-500">
                                <i class="fa-solid fa-briefcase fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Card 2: Pending Applications (Already a link) -->
                <a href="{{ route('admin.applications.index') }}" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-red-600 uppercase">Pending Applications</p>
                                <p class="text-4xl font-bold text-gray-800">{{ $pendingApplications }}</p>
                            </div>
                            <div class="text-red-500">
                                <i class="fa-solid fa-file-invoice fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Card 3: Total Clients (NOW A LINK) -->
                <a href="{{ route('admin.clients.index') }}" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-green-600 uppercase">Total Clients</p>
                                <p class="text-4xl font-bold text-gray-800">{{ $totalClients }}</p>
                            </div>
                            <div class="text-green-500">
                                <i class="fa-solid fa-user-tie fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Card 4: Total Partners (NOW A LINK) -->
                <a href="{{ route('admin.partners.index') }}" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-purple-600 uppercase">Total Partners</p>
                                <p class="text-4xl font-bold text-gray-800">{{ $totalPartners }}</p>
                            </div>
                            <div class="text-purple-500">
                                <i class="fa-solid fa-handshake-angle fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Card 5: Total Users (NOW A LINK) -->
                <a href="{{ route('admin.users.index') }}" class="transform hover:scale-105 transition-transform duration-300">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-blue-600 uppercase">Total Users</p>
                                <p class="text-4xl font-bold text-gray-800">{{ $totalUsers }}</p>
                            </div>
                            <div class="text-blue-500">
                                <i class="fa-solid fa-users fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>

            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Activity Feed</h3>
                    <p class="text-gray-500">(This is a placeholder for future activity logs, charts, or recent applications)</p>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
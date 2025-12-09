@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                <div class="flex">
                    <div class="py-1"><i class="fa-solid fa-check-circle mr-3"></i></div>
                    <div>
                        <p class="font-bold">Success</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap justify-between items-center bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6 rounded-lg shadow-lg mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold">Welcome, {{ Auth::user()->name }}!</h1>
                <p class="mt-2 text-lg text-blue-100">Partner Dashboard</p>
            </div>
            <a href="{{ route('partner.candidates.check') }}" class="mt-4 sm:mt-0 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-lg text-indigo-700 bg-white hover:bg-indigo-50 transform hover:scale-105 transition duration-300 ease-in-out">
                <i class="fa-solid fa-user-plus mr-2"></i> Add New Candidate
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-blue-500 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">My Profile</h3>
                        <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                            <i class="fa-solid fa-user-gear text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">Manage your company details, banking info, and professional preferences.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('partner.profile.business') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 transition">
                        Edit Profile <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-green-500 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Candidate Pool</h3>
                        <div class="p-3 bg-green-100 rounded-full text-green-600">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">View and manage all candidates you have added to the platform.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('partner.candidates.index') }}" class="inline-flex items-center text-green-600 font-semibold hover:text-green-800 transition">
                        View Candidates <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-indigo-500 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Available Jobs</h3>
                        <div class="p-3 bg-indigo-100 rounded-full text-indigo-600">
                            <i class="fa-solid fa-briefcase text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">Browse approved job vacancies and submit your candidates.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('partner.jobs') }}" class="inline-flex items-center text-indigo-600 font-semibold hover:text-indigo-800 transition">
                        View Jobs <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-purple-500 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Applications</h3>
                        <div class="p-3 bg-purple-100 rounded-full text-purple-600">
                            <i class="fa-solid fa-file-circle-check text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">Track the status of candidates you have submitted to clients.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('partner.applications') }}" class="inline-flex items-center text-purple-600 font-semibold hover:text-purple-800 transition">
                        Manage Applications <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-yellow-500 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-800">Earnings</h3>
                        <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                            <i class="fa-solid fa-sack-dollar text-xl"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">Track your payouts from successful placements.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('partner.earnings') }}" class="inline-flex items-center text-yellow-600 font-semibold hover:text-yellow-800 transition">
                        View Earnings <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
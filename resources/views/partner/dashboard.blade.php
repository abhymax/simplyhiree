@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">

        @if (session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex flex-wrap justify-between items-center bg-gradient-to-r from-blue-700 to-indigo-800 text-white p-6 rounded-lg shadow-lg mb-8">
            <div>
                <h1 class="text-4xl font-bold">Welcome, {{ Auth::user()->name }}!</h1>
                <p class="mt-2 text-xl opacity-90">Partner Dashboard</p>
            </div>
            <a href="{{ route('partner.candidates.create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-indigo-50 transform hover:scale-105 transition duration-300 ease-in-out">
                Add New Candidate
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-2xl font-bold text-gray-800">My Profile</h3>
                    <i class="fa-solid fa-user-gear text-blue-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 mb-4">Manage your company details, banking info, and preferences.</p>
                <a href="{{ route('partner.profile.business') }}" class="inline-block text-blue-600 font-semibold hover:underline">Edit Profile &rarr;</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-green-500">
                <h3 class="text-2xl font-bold text-gray-800">My Candidate Pool</h3>
                <p class="mt-4 text-gray-600">View and manage all candidates you have added to the platform.</p>
                <a href="{{ route('partner.candidates.index') }}" class="mt-4 inline-block text-green-600 font-semibold hover:underline">View Candidates &rarr;</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-indigo-500">
                <h3 class="text-2xl font-bold text-gray-800">Available Vacancies</h3>
                <p class="mt-4 text-gray-600">View all approved job vacancies you can work on.</p>
                <a href="{{ route('partner.jobs') }}" class="mt-4 inline-block text-indigo-600 font-semibold hover:underline">View Jobs &rarr;</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-purple-500">
                <h3 class="text-2xl font-bold text-gray-800">Your Applications</h3>
                <p class="mt-4 text-gray-600">Track the status of candidates you have submitted.</p>
                <a href="{{ route('partner.applications') }}" class="mt-4 inline-block text-purple-600 font-semibold hover:underline">Manage Applications &rarr;</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 border-t-4 border-yellow-500">
                <h3 class="text-2xl font-bold text-gray-800">Earnings</h3>
                <p class="mt-4 text-gray-600">Track your earnings from successful placements.</p>
                <a href="{{ route('partner.earnings') }}" class="mt-4 inline-block text-yellow-600 font-semibold hover:underline">View Earnings &rarr;</a>
            </div>
        </div>

    </div>
</div>
@endsection
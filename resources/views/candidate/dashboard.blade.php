@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-8">
        <div class="bg-blue-900 text-white p-6 rounded-lg shadow-lg">
            <h1 class="text-4xl font-bold">Welcome, {{ Auth::user()->name }}!</h1>
            <p class="mt-2 text-xl">This is your candidate dashboard. You can apply for jobs and track your applications here.</p>
        </div>

        <!-- Example Section for Job Applications -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-2xl font-bold">Your Applications</h3>
            <p class="mt-4 text-lg text-gray-600">See the status of your job applications here.</p>
            <a href="{{ route('candidate.applications') }}" class="text-blue-600 hover:underline">View Applications</a>
        </div>
    </div>
@endsection

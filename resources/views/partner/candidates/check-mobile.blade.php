@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Add New Candidate
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Let's check if they are already in your pool.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form action="{{ route('partner.candidates.verify') }}" method="POST">
                @csrf
                
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">
                        Mobile Number <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">🇮🇳 +91</span>
                        </div>
                        <input type="text" name="phone_number" id="phone_number" 
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-16 sm:text-sm border-gray-300 rounded-md" 
                               placeholder="9876543210" required autofocus>
                    </div>
                    @error('phone_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Check & Proceed <i class="fa-solid fa-arrow-right ml-2 mt-1"></i>
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Or
                        </span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('partner.candidates.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Cancel and go back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
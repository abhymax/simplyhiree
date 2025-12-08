@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Partner Account Details</h1>

        <form action="{{ route('partner.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">
                    <i class="fa-solid fa-building-columns mr-2 text-blue-600"></i> Bank Details
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Beneficiary Name <span class="text-red-500">*</span></label>
                        <input type="text" name="beneficiary_name" value="{{ old('beneficiary_name', $profile->beneficiary_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number', $profile->account_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number_confirmation" value="{{ old('account_number', $profile->account_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Type <span class="text-red-500">*</span></label>
                        <select name="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Select Type</option>
                            <option value="Savings" {{ old('account_type', $profile->account_type) == 'Savings' ? 'selected' : '' }}>Savings</option>
                            <option value="Current" {{ old('account_type', $profile->account_type) == 'Current' ? 'selected' : '' }}>Current</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">IFSC Code <span class="text-red-500">*</span></label>
                        <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Cancelled Cheque <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">jpg or png. Maximum 2.5mb</p>
                        @if($profile->cancelled_cheque_path)
                            <div class="text-sm text-green-600 mb-2 flex items-center">
                                <i class="fa-solid fa-check-circle mr-1"></i> File Uploaded: 
                                <a href="{{ asset('storage/'.$profile->cancelled_cheque_path) }}" target="_blank" class="underline ml-1">View</a>
                            </div>
                        @endif
                        <input type="file" name="cancelled_cheque" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">
                    <i class="fa-solid fa-id-card mr-2 text-green-600"></i> PAN Details
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name as on PAN card <span class="text-red-500">*</span></label>
                        <input type="text" name="pan_name" value="{{ old('pan_name', $profile->pan_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">PAN (Permanent Account Number) <span class="text-red-500">*</span></label>
                        <input type="text" name="pan_number" value="{{ old('pan_number', $profile->pan_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload PAN Card <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">jpg or png. Maximum 2.5mb</p>
                        @if($profile->pan_card_path)
                            <div class="text-sm text-green-600 mb-2 flex items-center">
                                <i class="fa-solid fa-check-circle mr-1"></i> File Uploaded: 
                                <a href="{{ asset('storage/'.$profile->pan_card_path) }}" target="_blank" class="underline ml-1">View</a>
                            </div>
                        @endif
                        <input type="file" name="pan_card" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">
                    <i class="fa-solid fa-file-invoice mr-2 text-purple-600"></i> GST Details
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">GST Number <span class="text-red-500">*</span></label>
                        <input type="text" name="gst_number" value="{{ old('gst_number', $profile->gst_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload GST Certificate <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">jpg or png. Maximum 2.5mb</p>
                        @if($profile->gst_certificate_path)
                            <div class="text-sm text-green-600 mb-2 flex items-center">
                                <i class="fa-solid fa-check-circle mr-1"></i> File Uploaded: 
                                <a href="{{ asset('storage/'.$profile->gst_certificate_path) }}" target="_blank" class="underline ml-1">View</a>
                            </div>
                        @endif
                        <input type="file" name="gst_certificate" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-10">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-10 rounded shadow-lg transform hover:scale-105 transition duration-150">
                    Submit Details
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Job / Vacancy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                
                <form action="{{ route('admin.jobs.store') }}" method="POST" x-data="{ visibility: 'all', clientMode: 'simplyhiree' }">
                    @csrf

                    <div class="mb-6 border-b pb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Posting Details</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Post On Behalf Of</label>
                            <select name="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" x-model="clientMode">
                                <option value="">Simplyhiree (Internal)</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }} (Client)</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="clientMode == ''" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Display Company Name</label>
                            <input type="text" name="company_name" value="Simplyhiree" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Job Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" required class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Salary</label>
                            <input type="text" name="salary" class="mt-1 block w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Experience <span class="text-red-500">*</span></label>
                            <select name="experience_level_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach($experienceLevels as $exp)
                                    <option value="{{ $exp->id }}">{{ $exp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Education <span class="text-red-500">*</span></label>
                            <select name="education_level_id" required class="mt-1 block w-full rounded-md border-gray-300">
                                @foreach($educationLevels as $edu)
                                    <option value="{{ $edu->id }}">{{ $edu->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Job Description <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                    </div>

                    <div class="mb-6 border-b pb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Payout Settings</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payout Amount (₹)</label>
                                <input type="number" name="payout_amount" class="mt-1 block w-full rounded-md border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Maturity Period (Days)</label>
                                <input type="number" name="minimum_stay_days" value="30" class="mt-1 block w-full rounded-md border-gray-300">
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Partner Visibility</h3>
                        
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="radio" name="partner_visibility" value="all" x-model="visibility" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-gray-700">Visible to All Active Partners</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="partner_visibility" value="selected" x-model="visibility" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-gray-700">Restrict to Specific Partners</span>
                            </label>
                        </div>

                        <div x-show="visibility === 'selected'" class="mt-4 p-4 bg-gray-50 rounded border">
                            <p class="text-sm font-bold text-gray-700 mb-2">Select Partners:</p>
                            <div class="h-40 overflow-y-auto grid grid-cols-2 gap-2">
                                @foreach($partners as $partner)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="allowed_partners[]" value="{{ $partner->id }}" class="rounded text-indigo-600">
                                        <span class="text-sm">{{ $partner->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Restricted Candidates</h3>
                        <p class="text-sm text-gray-500 mb-2">Select candidates who should NOT see this job.</p>
                        <div class="h-40 overflow-y-auto p-4 bg-gray-50 rounded border">
                            @foreach($candidates as $candidate)
                                <label class="flex items-center space-x-2 mb-1">
                                    <input type="checkbox" name="restricted_candidates[]" value="{{ $candidate->id }}" class="rounded text-red-600">
                                    <span class="text-sm">{{ $candidate->first_name }} {{ $candidate->last_name }} ({{ $candidate->email }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded shadow hover:bg-blue-700">
                            Create Job
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-crown text-indigo-500"></i> Partner Subscription Plans
            </h1>
            <p class="mt-1 text-sm text-slate-500">Manage the limits and features for each partner subscription tier.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
            <i class="fa-solid fa-check-circle text-emerald-500 text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col relative">
                <div class="h-2 w-full absolute top-0 left-0 @if($plan->name == 'Free') bg-slate-400 @elseif($plan->name == 'Basic') bg-blue-500 @elseif($plan->name == 'Pro') bg-indigo-500 @else bg-violet-600 @endif"></div>
                
                <div class="p-6 flex-1">
                    <h3 class="text-xl font-bold text-slate-900 mb-1">{{ $plan->name }} Plan</h3>
                    
                    <form action="{{ route('admin.partner-plans.update', $plan->id) }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Monthly Price ($)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" class="block w-full border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 bg-slate-50">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Monthly Resumes Cap</label>
                            <input type="number" name="monthly_submission_limit" value="{{ old('monthly_submission_limit', $plan->monthly_submission_limit) }}" placeholder="Leave blank for Unlimited" class="block w-full border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 bg-slate-50">
                            <p class="text-[11px] text-slate-400 mt-1">Leave empty for unlimited.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Max Sub-Recruiters</label>
                            <input type="number" name="max_team_members" value="{{ old('max_team_members', $plan->max_team_members) }}" required class="block w-full border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-slate-900 bg-slate-50">
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" name="can_view_premium_jobs" value="1" id="premium_{{ $plan->id }}" {{ $plan->can_view_premium_jobs ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="premium_{{ $plan->id }}" class="text-sm font-medium text-slate-700 block">Premium Job Visibility</label>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fa-solid fa-save mr-2"></i> Update Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

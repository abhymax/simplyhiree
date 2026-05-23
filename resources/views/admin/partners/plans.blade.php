@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-crown text-indigo-500"></i> Partner Subscription Plans
            </h1>
            <p class="mt-1 text-sm text-slate-500">Everything visible on the partner's <strong>Choose Your Plan</strong> page is controlled from here. Changes reflect live.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
            <i class="fa-solid fa-check-circle text-emerald-500 text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($plans as $plan)
            @php
                $bandColors = [
                    'slate'   => 'bg-slate-400',
                    'blue'    => 'bg-blue-500',
                    'purple'  => 'bg-indigo-500',
                    'rose'    => 'bg-rose-500',
                    'emerald' => 'bg-emerald-500',
                ];
                $band = $bandColors[$plan->accent_color] ?? 'bg-slate-400';
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
                <div class="h-2 w-full {{ $band }}"></div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-bold text-slate-900">{{ $plan->name }} Plan</h3>
                        @if($plan->is_most_popular)
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-1 rounded-full bg-purple-100 text-purple-700">Most Popular</span>
                        @endif
                    </div>

                    <form action="{{ route('admin.partner-plans.update', $plan->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        {{-- IDENTITY --}}
                        <fieldset class="border border-slate-200 rounded-lg p-3">
                            <legend class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider px-1">Identity</legend>
                            <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Subtitle</label>
                            <input type="text" name="subtitle" value="{{ old('subtitle', $plan->subtitle) }}" placeholder='e.g. "Entry / Freshers"'
                                   class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50 mb-3">

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Accent Color</label>
                                    <select name="accent_color" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                        @foreach(['slate','blue','purple','rose','emerald'] as $c)
                                            <option value="{{ $c }}" {{ $plan->accent_color === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                            </div>

                            <label class="inline-flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" name="is_most_popular" value="1" {{ $plan->is_most_popular ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-slate-300 rounded">
                                <span class="text-sm text-slate-700">Show <strong>Most Popular</strong> badge</span>
                            </label>
                        </fieldset>

                        {{-- PRICING --}}
                        <fieldset class="border border-slate-200 rounded-lg p-3">
                            <legend class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider px-1">Pricing</legend>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Price (₹)</label>
                                    <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Price Max (₹)</label>
                                    <input type="number" step="0.01" name="price_max" value="{{ old('price_max', $plan->price_max) }}" placeholder="Optional range upper bound" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Price Suffix</label>
                                <input type="text" name="price_suffix" value="{{ old('price_suffix', $plan->price_suffix) }}" placeholder='e.g. "/month" or "/month (custom)"' class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Commission Min %</label>
                                    <input type="number" step="0.01" name="commission_min" value="{{ old('commission_min', $plan->commission_min) }}" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Commission Max %</label>
                                    <input type="number" step="0.01" name="commission_max" value="{{ old('commission_max', $plan->commission_max) }}" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                            </div>
                        </fieldset>

                        {{-- LIMITS --}}
                        <fieldset class="border border-slate-200 rounded-lg p-3">
                            <legend class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider px-1">Limits</legend>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Monthly Resumes Cap</label>
                                    <input type="number" name="monthly_submission_limit" value="{{ old('monthly_submission_limit', $plan->monthly_submission_limit) }}" placeholder="blank = unlimited" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Max Sub-Recruiters</label>
                                    <input type="number" name="max_team_members" value="{{ old('max_team_members', $plan->max_team_members) }}" required class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50">
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" name="can_view_premium_jobs" value="1" {{ $plan->can_view_premium_jobs ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-slate-300 rounded">
                                <span class="text-sm text-slate-700">Premium Job Visibility</span>
                            </label>
                        </fieldset>

                        {{-- FEATURES --}}
                        <fieldset class="border border-slate-200 rounded-lg p-3">
                            <legend class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider px-1">Feature Bullets</legend>
                            <label class="block text-[11px] font-semibold text-emerald-600 uppercase mb-1">✔ Included Features (one per line)</label>
                            <textarea name="features" rows="6" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50 font-mono text-[12px]">{{ old('features', is_array($plan->features) ? implode("\n", $plan->features) : '') }}</textarea>

                            <label class="block text-[11px] font-semibold text-rose-600 uppercase mb-1 mt-3">✘ Not Included (one per line, optional)</label>
                            <textarea name="non_features" rows="3" class="block w-full border-slate-200 rounded-lg sm:text-sm text-slate-900 bg-slate-50 font-mono text-[12px]">{{ old('non_features', is_array($plan->non_features) ? implode("\n", $plan->non_features) : '') }}</textarea>
                        </fieldset>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium text-sm">
                            <i class="fa-solid fa-save mr-2"></i> Save {{ $plan->name }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

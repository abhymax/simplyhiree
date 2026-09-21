@extends('layouts.client')
@section('client_content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white">Assessment Results</h1>
        <p class="text-blue-200 mt-1">Candidates who took your questionnaires. Only your company's results are shown.</p>
    </div>
    @include('assessment_results._list')
</div>
@endsection

@extends('layouts.client')
@section('client_content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white">Questionnaires</h1>
        <p class="text-blue-200 mt-1">Build MCQ assessments to attach to your jobs. Only your company can see these.</p>
    </div>
    @include('assessments._list')
</div>
@endsection

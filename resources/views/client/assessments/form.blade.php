@extends('layouts.client')
@section('client_content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white">{{ ($assessment ?? null) ? 'Edit' : 'Create' }} Questionnaire</h1>
        <p class="text-blue-200 mt-1">Add MCQ questions, mark the correct option, and set passing %, time limit and attempts.</p>
    </div>
    @include('assessments._form')
</div>
@endsection

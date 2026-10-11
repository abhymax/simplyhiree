@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 py-8 px-4 sm:px-6 lg:px-8">
        @include('chat.partials.show')
    </div>
@endsection

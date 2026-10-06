@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 mx-auto max-w-7xl pb-8">
        @php
            $backUrl = route('client.jobs.applicants', $application->job_id);
            $backLabel = 'Back to Responses';
            if (url()->previous() && str_contains(url()->previous(), 'client/applications')) {
                $backUrl = url()->previous();
                $backLabel = 'Back to Applications';
            }
        @endphp

        <a href="{{ $backUrl }}" class="mb-5 inline-flex items-center gap-2 text-sm font-extrabold uppercase tracking-wider text-cyan-300 transition hover:text-white">
            <i class="fa-solid fa-arrow-left"></i> {{ $backLabel }}
        </a>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-300/30 bg-emerald-500/15 px-5 py-4 font-semibold text-emerald-50">
                <i class="fa-solid fa-circle-check mr-2 text-emerald-300"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-rose-300/30 bg-rose-500/15 px-5 py-4 font-semibold text-rose-50">
                <i class="fa-solid fa-circle-exclamation mr-2 text-rose-300"></i>{{ session('error') }}
            </div>
        @endif

        @include('client.jobs.partials.response-card', ['app' => $application, 'isDetailView' => true])
    </div>
@endsection

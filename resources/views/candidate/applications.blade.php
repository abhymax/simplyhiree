@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-4xl font-bold">Your Applications</h1>
        <div class="mt-8">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4">Job Title</th>
                        <th class="py-2 px-4">Status</th>
                        <th class="py-2 px-4">Date Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $application)
                        <tr>
                            <td class="py-2 px-4">{{ $application->job->title }}</td>
                            <td class="py-2 px-4">{{ $application->status }}</td>
                            <td class="py-2 px-4">{{ $application->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">Manage Your Jobs</h1>
        <a href="/client/post-job" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Post a New Job
        </a>
    </div>

    <div class="mt-8">
        <table class="min-w-full bg-white border">
            <thead>
                <tr class="w-full bg-gray-100 text-left">
                    <th class="py-2 px-4">Job Title</th>
                    <th class="py-2 px-4">Location</th>
                    <th class="py-2 px-4">Date Posted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                    <tr>
                        <td class="py-2 px-4 border-t">{{ $job->title }}</td>
                        <td class="py-2 px-4 border-t">{{ $job->location }}</td>
                        <td class="py-2 px-4 border-t">{{ $job->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 px-4 text-center">You have not posted any jobs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
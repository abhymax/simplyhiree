@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6 border-b border-white/10 pb-5">
            <div>
                <h1 class="text-3xl font-extrabold">Today's Scheduled Interviews</h1>
                <p class="text-blue-200">{{ date('F j, Y') }}</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="text-blue-200 hover:text-white">&larr; Back to Dashboard</a>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900/50 border-b border-white/10">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-blue-100 uppercase">Time</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-blue-100 uppercase">Candidate</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-blue-100 uppercase">Job Role</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-blue-100 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($todayInterviews as $app)
                            <tr class="hover:bg-white/5">
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-blue-200">{{ $app->interview_at->format('g:i A') }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-white">{{ $app->candidate_name }}</div>
                                    @if($app->candidate)
                                        <div class="text-xs text-slate-300">{{ $app->candidate->phone_number }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-sm text-slate-100">{{ $app->job->title }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <a href="{{ route('client.jobs.applicants', $app->job_id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded">
                                        View & Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-300">
                                    No interviews scheduled for today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
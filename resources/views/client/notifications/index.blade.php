@extends('layouts.client')

@section('client_content')
<div class="relative z-10 mx-auto max-w-5xl">
    <div class="mb-8 flex flex-col gap-4 border-b border-white/15 pb-6 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('client.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-cyan-300 transition hover:text-white"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-white">Notifications</h1>
            <p class="mt-2 text-blue-200">Your complete account activity history.</p>
        </div>
        <div class="rounded-2xl border border-cyan-300/25 bg-cyan-400/10 px-4 py-3 text-sm font-bold text-cyan-100">{{ $notifications->total() }} total</div>
    </div>

    <section class="overflow-hidden rounded-3xl border border-white/12 bg-slate-950/45 shadow-2xl backdrop-blur-xl">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $title = $data['title'] ?? class_basename($notification->type);
                $message = $data['message'] ?? $data['body'] ?? 'Account activity updated.';
            @endphp
            <article class="flex gap-4 border-b border-white/8 px-5 py-5 transition hover:bg-white/[.045]">
                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification->read_at ? 'bg-slate-700/60 text-slate-300' : 'bg-cyan-400/15 text-cyan-200 ring-1 ring-cyan-300/25' }}"><i class="fa-regular fa-bell"></i></div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-extrabold text-white">{{ $title }}</h2>
                        <time class="text-xs text-slate-400">{{ $notification->created_at->format('d M Y, g:i A') }}</time>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-blue-100">{{ $message }}</p>
                    @if(!$notification->read_at)<span class="mt-3 inline-flex rounded-full bg-cyan-400/12 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-cyan-200">Unread</span>@endif
                </div>
            </article>
        @empty
            <div class="px-6 py-20 text-center">
                <i class="fa-regular fa-bell-slash text-4xl text-cyan-300"></i>
                <h2 class="mt-4 text-lg font-black text-white">No notifications yet</h2>
                <p class="mt-2 text-sm text-blue-200">New hiring activity and account updates will appear here.</p>
            </div>
        @endforelse
    </section>

    @if($notifications->hasPages())<div class="mt-7">{{ $notifications->links() }}</div>@endif
</div>
@endsection

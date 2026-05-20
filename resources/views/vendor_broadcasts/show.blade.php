@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-5xl mx-auto">

        <div class="mb-6 border-b border-white/10 pb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white mt-2">{{ $broadcast->subject }}</h1>
            <p class="text-blue-200 mt-1">
                Sent by {{ $broadcast->sender->name ?? 'system' }} on
                {{ optional($broadcast->dispatched_at)->format('d M Y, H:i') }}
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-bold uppercase">Total</p>
                <p class="text-3xl font-extrabold text-white">{{ $broadcast->recipient_count }}</p>
            </div>
            <div class="bg-emerald-500/10 border border-emerald-400/30 rounded-2xl p-4">
                <p class="text-emerald-200 text-xs font-bold uppercase">Sent</p>
                <p class="text-3xl font-extrabold text-emerald-200">{{ $broadcast->sent_count }}</p>
            </div>
            <div class="bg-rose-500/10 border border-rose-400/30 rounded-2xl p-4 flex flex-col justify-between">
                <div>
                    <p class="text-rose-200 text-xs font-bold uppercase">Failed</p>
                    <p class="text-3xl font-extrabold text-rose-200">{{ $broadcast->failed_count }}</p>
                </div>
                @if($broadcast->failed_count > 0)
                    @php $retryRoute = auth()->user()->hasRole('client') ? 'client.broadcasts.retry' : 'admin.broadcasts.retry'; @endphp
                    <form method="POST" action="{{ route($retryRoute, $broadcast) }}" class="mt-2"
                          onsubmit="return confirm('Retry delivery for the {{ $broadcast->failed_count }} failed recipient(s)?');">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                            <i class="fa-solid fa-rotate-right"></i> Retry Failed
                        </button>
                    </form>
                @endif
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-bold uppercase">Channels</p>
                <p class="text-white font-bold text-sm mt-2">
                    @foreach($broadcast->channelList() as $c)<span class="inline-block mr-1">{{ ucfirst($c) }}</span>@endforeach
                </p>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 mb-6">
            <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3">Message</h3>
            <div class="whitespace-pre-wrap text-blue-100 text-sm leading-relaxed">{{ $broadcast->body }}</div>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
            <div class="p-4 border-b border-white/10 text-cyan-300 text-xs font-bold uppercase tracking-wider">Recipients</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs">
                        <tr>
                            <th class="px-5 py-3">Partner</th>
                            <th class="px-5 py-3">WhatsApp</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($broadcast->recipients as $r)
                            <tr class="hover:bg-white/10">
                                <td class="px-5 py-3">
                                    <div class="font-bold">{{ $r->partner->name ?? 'Deleted user' }}</div>
                                    <div class="text-xs text-blue-200">{{ $r->partner->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    @php $w = $r->whatsapp_status; @endphp
                                    @if($w === 'sent')<span class="bg-emerald-500/20 text-emerald-200 border border-emerald-400/40 text-xs font-bold px-2 py-0.5 rounded">✓ Sent</span>
                                    @elseif($w === 'failed')<span class="bg-rose-500/20 text-rose-200 border border-rose-400/40 text-xs font-bold px-2 py-0.5 rounded">✗ Failed</span>
                                    @elseif($w === 'skipped')<span class="bg-slate-500/20 text-slate-200 border border-slate-400/40 text-xs font-bold px-2 py-0.5 rounded">skipped</span>
                                    @else <span class="text-slate-500 text-xs">—</span>@endif
                                </td>
                                <td class="px-5 py-3">
                                    @php $e = $r->email_status; @endphp
                                    @if($e === 'sent')<span class="bg-emerald-500/20 text-emerald-200 border border-emerald-400/40 text-xs font-bold px-2 py-0.5 rounded">✓ Sent</span>
                                    @elseif($e === 'failed')<span class="bg-rose-500/20 text-rose-200 border border-rose-400/40 text-xs font-bold px-2 py-0.5 rounded">✗ Failed</span>
                                    @else <span class="text-slate-500 text-xs">—</span>@endif
                                </td>
                                <td class="px-5 py-3 text-xs text-rose-200">{{ $r->error }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-blue-200">No recipients.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">

    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-screen filter blur-[120px] opacity-30"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
            <div>
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-bullhorn mr-1"></i> {{ $scope['type'] === 'admin' ? 'Admin Broadcast' : 'My Vendor Broadcast' }}
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white mt-2">Broadcast to Vendors</h1>
                <p class="text-blue-200 mt-2">One click — message every {{ $scope['type'] === 'admin' ? 'active partner' : 'connected vendor' }} via WhatsApp and email.</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl mt-4 md:mt-0">
                <p class="text-xs text-blue-300 font-bold uppercase">Audience</p>
                <p class="text-white font-extrabold text-2xl"><i class="fa-solid fa-users text-blue-400 mr-1"></i> {{ $audience }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-100">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-100">
                <p class="font-bold mb-1">Please fix:</p>
                <ul class="list-disc ml-6 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Compose card --}}
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 md:p-8 mb-8">
            <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-3">
                <span class="w-1.5 h-7 bg-blue-500 rounded-full"></span>
                <i class="fa-solid fa-paper-plane text-blue-400"></i> Compose New Broadcast
            </h3>

            <form method="POST" action="{{ route($scope['type'] === 'admin' ? 'admin.broadcasts.store' : 'client.broadcasts.store') }}"
                  class="space-y-5" id="broadcast-form" onsubmit="return confirm('Send this broadcast to ' + {{ $audience }} + ' vendors right now?');">
                @csrf

                {{-- Templates --}}
                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Quick Templates</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($templates as $key => $tpl)
                            <button type="button"
                                onclick='applyTemplate(@json($key), @json($tpl["subject"]), @json($tpl["body"]))'
                                class="bg-slate-900/60 hover:bg-slate-800 border border-white/10 text-white text-xs font-bold px-3 py-2 rounded-lg transition">
                                @if($key === 'urgent_hiring')<i class="fa-solid fa-bolt text-amber-300 mr-1"></i>@endif
                                @if($key === 'salary_update')<i class="fa-solid fa-indian-rupee-sign text-emerald-300 mr-1"></i>@endif
                                @if($key === 'new_jobs')<i class="fa-solid fa-briefcase text-cyan-300 mr-1"></i>@endif
                                @if($key === 'gentle_nudge')<i class="fa-regular fa-bell text-purple-300 mr-1"></i>@endif
                                {{ $tpl['subject'] }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="template_key" id="template_key" value="custom">
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Subject</label>
                    <input type="text" name="subject" id="bc-subject" required maxlength="200"
                        value="{{ old('subject') }}"
                        placeholder="Short, punchy headline"
                        class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Message</label>
                    <textarea name="body" id="bc-body" rows="7" required maxlength="5000"
                        placeholder="Your message to all vendors..."
                        class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400">{{ old('body') }}</textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Plain text. Keep it short for WhatsApp.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Send via</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-900/60 hover:bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5">
                            <input type="checkbox" name="channels[]" value="whatsapp" checked class="rounded border-white/30 bg-slate-800 text-emerald-500 focus:ring-emerald-400">
                            <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                            <span class="text-white text-sm font-semibold">WhatsApp</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-900/60 hover:bg-slate-900 border border-white/10 rounded-xl px-4 py-2.5">
                            <input type="checkbox" name="channels[]" value="email" checked class="rounded border-white/30 bg-slate-800 text-blue-500 focus:ring-blue-400">
                            <i class="fa-regular fa-envelope text-blue-400"></i>
                            <span class="text-white text-sm font-semibold">Email</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/10">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-3 px-7 rounded-xl transition flex items-center gap-2 shadow-lg hover:shadow-blue-500/40">
                        <i class="fa-solid fa-paper-plane"></i> Send Broadcast to {{ $audience }} Vendor{{ $audience === 1 ? '' : 's' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- History --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-bold text-white flex items-center gap-3">
                    <span class="w-1.5 h-7 bg-purple-500 rounded-full"></span>
                    <i class="fa-solid fa-clock-rotate-left text-purple-400"></i> Broadcast History
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Sent</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Channels</th>
                            <th class="px-6 py-4">Recipients</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($broadcasts as $b)
                            <tr class="hover:bg-white/10">
                                <td class="px-6 py-4 text-blue-100 text-xs">{{ optional($b->dispatched_at)->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $b->subject }}</div>
                                    <div class="text-[10px] text-slate-300">{{ $b->sender->name ?? 'system' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @foreach($b->channelList() as $c)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold mr-1 {{ $c === 'whatsapp' ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40' : 'bg-blue-500/20 text-blue-200 border border-blue-400/40' }}">
                                            <i class="fa-{{ $c === 'whatsapp' ? 'brands fa-whatsapp' : 'regular fa-envelope' }}"></i> {{ ucfirst($c) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-white font-bold">{{ $b->sent_count }}</span>
                                    <span class="text-slate-400 text-xs">/ {{ $b->recipient_count }}</span>
                                    @if($b->failed_count > 0)
                                        <div class="text-[10px] text-rose-300">{{ $b->failed_count }} failed</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route($scope['type'] === 'admin' ? 'admin.broadcasts.show' : 'client.broadcasts.show', $b) }}"
                                       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                                        <i class="fa-regular fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-blue-200">No broadcasts sent yet. Use the compose form above.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($broadcasts->hasPages())
                <div class="p-4 border-t border-white/10 bg-slate-900/60">{{ $broadcasts->onEachSide(1)->links() }}</div>
            @endif
        </div>

    </div>
</div>

<script>
    const TEMPLATES = @json($templates);
    function applyTemplate(key, subject, body) {
        document.getElementById('bc-subject').value = subject;
        document.getElementById('bc-body').value    = body;
        document.getElementById('template_key').value = key;
    }
</script>
@endsection

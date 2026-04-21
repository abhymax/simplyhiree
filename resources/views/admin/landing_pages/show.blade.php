<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
        <div class="max-w-6xl mx-auto">

            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.landing-pages.index') }}" class="text-blue-300 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Leads — {{ $landingPage->title }}</h1>
                        <p class="text-blue-200 mt-1">Registrations from this landing page</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.landing-pages.edit', $landingPage) }}"
                       class="px-4 py-2 bg-indigo-600/70 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl transition">Edit Page</a>
                    <a href="{{ url('/l/' . $landingPage->slug) }}" target="_blank"
                       class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-xl transition">Preview</a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-5">
                    <div class="text-3xl font-bold text-white">{{ $registrations->total() }}</div>
                    <div class="text-blue-200 text-sm mt-1">Total Registrations</div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-5">
                    <div class="text-3xl font-bold text-white">{{ $landingPage->seats_total > 0 ? $landingPage->seats_left : '∞' }}</div>
                    <div class="text-blue-200 text-sm mt-1">Seats Left</div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-5">
                    @if($landingPage->status === 'published')
                        <div class="text-3xl font-bold text-emerald-400">Live</div>
                    @else
                        <div class="text-3xl font-bold text-amber-400">Draft</div>
                    @endif
                    <div class="text-blue-200 text-sm mt-1">Status</div>
                </div>
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-5">
                    <div class="text-xl font-bold text-white truncate">{{ $landingPage->event_date ? $landingPage->event_date->format('d M Y') : '—' }}</div>
                    <div class="text-blue-200 text-sm mt-1">Event Date</div>
                </div>
            </div>

            {{-- Registrations Table --}}
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                    <h2 class="text-white font-semibold">Registrations</h2>
                    <a href="{{ route('admin.landing-pages.export', $landingPage) }}"
                       class="px-4 py-1.5 bg-emerald-600/70 hover:bg-emerald-600 text-white text-xs font-semibold rounded-lg transition">
                        Export CSV
                    </a>
                </div>
                <table class="w-full text-sm text-white">
                    <thead class="bg-white/10 text-blue-200 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Phone</th>
                            <th class="px-6 py-3 text-left">City</th>
                            <th class="px-6 py-3 text-left">Registered At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($registrations as $reg)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-3 text-blue-300">{{ $reg->id }}</td>
                            <td class="px-6 py-3">{{ $reg->name ?? '—' }}</td>
                            <td class="px-6 py-3">{{ $reg->email ?? '—' }}</td>
                            <td class="px-6 py-3">{{ $reg->phone ?? '—' }}</td>
                            <td class="px-6 py-3">{{ $reg->city ?? '—' }}</td>
                            <td class="px-6 py-3 text-blue-200">{{ $reg->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-blue-300">No registrations yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($registrations->hasPages())
                <div class="px-6 py-4 border-t border-white/10">{{ $registrations->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

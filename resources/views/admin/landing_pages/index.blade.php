<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
        <div class="max-w-7xl mx-auto">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Landing Pages</h1>
                    <p class="text-blue-200 mt-1">Create and manage custom landing pages</p>
                </div>
                <a href="{{ route('admin.landing-pages.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Landing Page
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 px-4 py-3 bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 rounded-xl">{{ session('success') }}</div>
            @endif

            {{-- Table --}}
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl overflow-hidden">
                <table class="w-full text-sm text-white">
                    <thead class="bg-white/10 text-blue-200 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">Title / URL</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Registrations</th>
                            <th class="px-6 py-4 text-center">Event Date</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($pages as $page)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-white">{{ $page->title }}</div>
                                <a href="{{ url('/l/' . $page->slug) }}" target="_blank"
                                   class="text-blue-300 hover:text-blue-200 text-xs flex items-center gap-1 mt-0.5">
                                    /l/{{ $page->slug }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($page->status === 'published')
                                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-xs font-semibold border border-emerald-400/30">Published</span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-300 rounded-full text-xs font-semibold border border-amber-400/30">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.landing-pages.show', $page) }}" class="text-blue-300 hover:text-white font-semibold">
                                    {{ number_format($page->registrations_count) }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-center text-blue-200">
                                {{ $page->event_date ? $page->event_date->format('d M Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.landing-pages.edit', $page) }}"
                                       class="px-3 py-1.5 bg-indigo-600/70 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition">Edit</a>
                                    <a href="{{ route('admin.landing-pages.show', $page) }}"
                                       class="px-3 py-1.5 bg-blue-600/70 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition">Leads</a>
                                    <form method="POST" action="{{ route('admin.landing-pages.destroy', $page) }}"
                                          onsubmit="return confirm('Delete this landing page?')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 bg-rose-600/70 hover:bg-rose-600 text-white text-xs font-semibold rounded-lg transition">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-blue-300">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                No landing pages yet. <a href="{{ route('admin.landing-pages.create') }}" class="text-indigo-400 hover:text-white underline">Create your first one.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($pages->hasPages())
                <div class="px-6 py-4 border-t border-white/10">{{ $pages->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

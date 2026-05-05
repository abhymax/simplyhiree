<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 left-0 w-96 h-96 bg-rose-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Archived Jobs</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Deactivated jobs and their full historical record.</p>
                </div>
                @if(session('success'))
                    <div class="px-6 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-xl font-bold">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden">
                @if($jobs->isEmpty())
                    <div class="px-6 py-20 text-center">
                        <i class="fa-solid fa-box-archive text-5xl text-blue-300/40 mb-4"></i>
                        <p class="text-white font-bold text-lg">No archived jobs yet.</p>
                        <p class="text-blue-200 text-sm mt-1">Deactivated jobs (approved by Superadmin) will appear here.</p>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Job</th>
                                <th class="px-6 py-5">Client</th>
                                <th class="px-6 py-5">Applications</th>
                                <th class="px-6 py-5">Archived On</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($jobs as $job)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-5 align-top">
                                    <div class="text-white font-bold">{{ $job->title }}</div>
                                    <div class="text-blue-200 text-xs mt-0.5">{{ $job->company_name ?? '—' }} &middot; {{ $job->location }}</div>
                                </td>
                                <td class="px-6 py-5 align-top text-blue-100">
                                    {{ $job->user->name ?? '—' }}
                                    <div class="text-xs text-blue-300/70">{{ $job->user->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <span class="bg-cyan-500/20 border border-cyan-400/40 text-cyan-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                        {{ $job->job_applications_count }} application{{ $job->job_applications_count == 1 ? '' : 's' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-top text-blue-200">
                                    {{ $job->archived_at->format('M d, Y') }}
                                    <div class="text-xs text-blue-300/70">{{ $job->archived_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-5 align-top text-right">
                                    <a href="{{ route('admin.jobs.archived.show', $job) }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3 py-2 rounded-lg">
                                        <i class="fa-solid fa-folder-open"></i> View Archive
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-white/10">{{ $jobs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

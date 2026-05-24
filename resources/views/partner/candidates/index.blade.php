@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="mb-6 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-circle-check mr-3 text-xl"></i>{{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="{{ route('partner.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold tracking-tight">My Candidates</h1>
                <p class="text-blue-200 mt-1">Manage your talent pool and update candidate profiles.</p>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <div class="bg-blue-600 border border-blue-400 rounded-2xl px-5 py-3 shadow-xl text-center">
                    <div class="text-blue-100 text-xs font-bold uppercase">Total</div>
                    <div class="text-3xl font-black">{{ $candidates->total() }}</div>
                </div>
                <a href="{{ route('partner.candidates.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-slate-900 transition-all hover:-translate-y-0.5"
                   style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                    <i class="fa-solid fa-plus"></i> Add Candidate
                </a>
            </div>
        </div>

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('partner.candidates.index') }}" class="mb-6">
            <div class="bg-slate-900/50 backdrop-blur border border-white/15 rounded-2xl p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1.5">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, email or phone…"
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1.5">Experience</label>
                        <select name="experience" class="w-full bg-slate-800 border border-white/20 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            <option value="">All</option>
                            <option value="Fresher"    {{ request('experience') === 'Fresher'    ? 'selected' : '' }}>Fresher</option>
                            <option value="Experienced"{{ request('experience') === 'Experienced'? 'selected' : '' }}>Experienced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1.5">Location</label>
                        <select name="location" class="w-full bg-slate-800 border border-white/20 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ request('location') === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                        <i class="fa-solid fa-filter mr-1.5"></i> Apply Filters
                    </button>
                    @if(request()->hasAny(['search','experience','location']))
                        <a href="{{ route('partner.candidates.index') }}"
                           class="px-4 py-2.5 rounded-xl text-sm font-bold bg-white/10 hover:bg-white/20 text-white transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-5">Candidate</th>
                            <th class="px-6 py-5">Contact</th>
                            <th class="px-6 py-5">Role / Skills</th>
                            <th class="px-6 py-5">Experience</th>
                            <th class="px-6 py-5">Location</th>
                            <th class="px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($candidates as $candidate)
                            <tr class="hover:bg-white/5 transition border-l-4 border-transparent hover:border-cyan-400">
                                <td class="px-6 py-4">
                                    <a href="{{ route('partner.candidates.show', $candidate->id) }}" class="flex items-center gap-3 group">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white ring-2 ring-white/20 group-hover:ring-cyan-400 transition-all shrink-0">
                                            {{ strtoupper(substr($candidate->first_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-cyan-300 transition-colors">
                                                {{ $candidate->first_name }} {{ $candidate->last_name }}
                                            </div>
                                            <div class="text-xs text-blue-300">Added {{ $candidate->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white/80"><i class="fa-regular fa-envelope mr-1 text-blue-300"></i>{{ $candidate->email ?? 'N/A' }}</div>
                                    <div class="text-white/70 mt-0.5"><i class="fa-solid fa-phone mr-1 text-blue-300"></i>{{ $candidate->phone_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ $candidate->job_role_preference ?? '—' }}</div>
                                    @if($candidate->skills)
                                        <div class="text-xs text-blue-200 mt-0.5">{{ Str::limit($candidate->skills, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php $isExp = ($candidate->experience_status ?? 'Fresher') === 'Experienced'; @endphp
                                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $isExp ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30' : 'bg-blue-500/20 text-blue-200 border-blue-400/30' }}">
                                        {{ $candidate->experience_status ?? 'Fresher' }}
                                    </span>
                                    @if($candidate->total_experience_years !== null)
                                        <div class="text-xs text-blue-300 mt-0.5">{{ $candidate->total_experience_years }}y {{ $candidate->total_experience_months ?? 0 }}m</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-white/70 text-sm">{{ $candidate->location ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('partner.candidates.show', $candidate->id) }}"
                                           class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition-all">
                                            <i class="fa-solid fa-eye mr-1"></i> View
                                        </a>
                                        <a href="{{ route('partner.candidates.edit', $candidate->id) }}"
                                           class="px-3 py-1.5 rounded-lg text-xs font-bold border border-blue-300/40 text-blue-100 hover:bg-blue-500/20 transition-all">
                                            <i class="fa-solid fa-pen mr-1"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <i class="fa-solid fa-users-slash text-5xl text-blue-200 mb-3"></i>
                                    <p class="text-white font-bold">No candidates found.</p>
                                    <a href="{{ route('partner.candidates.create') }}" class="text-cyan-300 hover:text-white font-semibold mt-2 inline-block">Add a candidate</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-white/10 bg-slate-900/80">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

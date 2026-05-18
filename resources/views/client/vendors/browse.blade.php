@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-wrap items-end justify-between gap-3 mb-6 border-b border-white/10 pb-6">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">Browse Vendors</h1>
                <p class="text-blue-200 mt-1">Filter by rating, location, industry or badge. Add the best ones to your Preferred list.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('client.vendors.invite') }}" class="bg-blue-500 hover:bg-blue-400 text-white text-sm font-bold px-4 py-2 rounded-lg">+ Invite My Vendor</a>
                <a href="{{ route('client.vendors.assign-request') }}" class="bg-purple-500 hover:bg-purple-400 text-white text-sm font-bold px-4 py-2 rounded-lg">🤝 Ask SimplyHiree to Assign</a>
                <a href="{{ route('client.vendors.performance') }}" class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-lg">📊 Performance</a>
            </div>
        </div>

        @if(session('success'))<div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl font-bold">{{ session('error') }}</div>@endif

        <form method="GET" class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-3 mb-6 flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name/email…" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3 grow min-w-[180px]">
            <select name="min_rating" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3">
                <option value="">⭐ Any rating</option>
                <option value="4.5" {{ request('min_rating')=='4.5'?'selected':'' }}>⭐ 4.5+</option>
                <option value="4"   {{ request('min_rating')=='4'?'selected':'' }}>⭐ 4+</option>
                <option value="3.5" {{ request('min_rating')=='3.5'?'selected':'' }}>⭐ 3.5+</option>
            </select>
            <select name="level" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3">
                <option value="">All Tiers</option>
                @foreach(['Elite','Pro','Basic'] as $lvl)
                    <option value="{{ $lvl }}" {{ request('level')===$lvl?'selected':'' }}>{{ $lvl }}</option>
                @endforeach
            </select>
            <select name="badge" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3">
                <option value="">All Badges</option>
                @foreach(['Rising Talent','Top Recruiter','Elite Partner','Trusted Vendor'] as $bd)
                    <option value="{{ $bd }}" {{ request('badge')===$bd?'selected':'' }}>{{ $bd }}</option>
                @endforeach
            </select>
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Location…" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3 max-w-[160px]">
            <input type="text" name="industry" value="{{ request('industry') }}" placeholder="Industry…" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm px-3 max-w-[160px]">
            <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm">Filter</button>
            @if(request()->anyFilled(['search','min_rating','level','badge','location','industry']))
                <a href="{{ route('client.vendors.browse') }}" class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($vendors as $v)
                @php $isPreferred = in_array($v->id, $preferredIds); @endphp
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-5 flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-white font-extrabold text-lg leading-tight">{{ $v->name }}</div>
                            <div class="text-blue-200 text-xs">{{ $v->email }}</div>
                        </div>
                        <div class="text-amber-200 font-extrabold whitespace-nowrap">
                            ⭐ {{ $v->avg_rating ?? '—' }}
                            <span class="text-blue-200 text-xs font-normal">({{ $v->total_ratings }})</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5 text-[10px] font-bold uppercase">
                        @if($v->vendor_level)<span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/40">{{ $v->vendor_level }} Tier</span>@endif
                        @if($v->vendor_badge)<span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/40">{{ $v->vendor_badge }}</span>@endif
                        @if($v->profile?->location)<span class="px-2 py-0.5 rounded-full bg-white/5 text-slate-200 border border-white/10">📍 {{ $v->profile->location }}</span>@endif
                        @if($v->partnerProfile?->industry)<span class="px-2 py-0.5 rounded-full bg-white/5 text-slate-200 border border-white/10">{{ $v->partnerProfile->industry }}</span>@endif
                    </div>
                    <form method="POST" action="{{ route('client.vendors.toggle', $v->id) }}">
                        @csrf
                        <button type="submit" class="w-full py-2 rounded-lg text-sm font-bold transition {{ $isPreferred ? 'bg-rose-500/20 text-rose-200 border border-rose-400/40 hover:bg-rose-500/30' : 'bg-emerald-500 hover:bg-emerald-400 text-white' }}">
                            {{ $isPreferred ? 'Remove from Preferred' : '+ Add to Preferred' }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center text-blue-200 py-16">No vendors match your filters.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $vendors->links() }}</div>
    </div>
</div>
@endsection

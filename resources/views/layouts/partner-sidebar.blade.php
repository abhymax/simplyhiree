@php
    $partner = auth()->user();
    $initial = strtoupper(mb_substr($partner->name ?: 'P', 0, 1));
    $profileRoute = $partner->isPartnerOwner() ? 'partner.profile.business' : 'profile.edit';
    $profilePattern = $partner->isPartnerOwner() ? 'partner.profile.*' : 'profile.*';
    $link = function (string $route, string $activePattern, string $label, string $icon) {
        $active = request()->routeIs($activePattern) ? ' active' : '';
        return '<a href="'.route($route).'" class="partner-sidebar-link'.$active.'"><i class="fa-solid '.$icon.'"></i><span>'.$label.'</span></a>';
    };
@endphp
<aside class="partner-sidebar" aria-label="Partner navigation">
    <a href="{{ route('partner.dashboard') }}" class="partner-sidebar-logo"><span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-base shadow-lg">SH</span><span>SimplyHiree</span></a>
    <nav class="partner-sidebar-nav">
        <div class="partner-sidebar-label">Workspace</div>
        {!! $link('partner.dashboard', 'partner.dashboard', 'Dashboard', 'fa-chart-line') !!}
        {!! $link('partner.jobs', 'partner.jobs*', 'Browse Jobs', 'fa-briefcase') !!}
        {!! $link('partner.applications', 'partner.applications*', 'My Applications', 'fa-file-lines') !!}
        {!! $link('partner.candidates.index', 'partner.candidates.*', 'My Candidates', 'fa-users') !!}
        {!! $link('partner.assessments.index', 'partner.assessments.*', 'Assessments', 'fa-clipboard-check') !!}
        {!! $link('partner.replacements', 'partner.replacements', 'Replacements', 'fa-rotate') !!}
        <div class="partner-sidebar-label">Business</div>
        @if($partner->isPartnerOwner())
            {!! $link('partner.earnings', 'partner.earnings*', 'Earnings', 'fa-indian-rupee-sign') !!}
            {!! $link('partner.wallet', 'partner.wallet', 'Wallet', 'fa-wallet') !!}
        @endif
        {!! $link('partner.team.index', 'partner.team.*', 'My Team', 'fa-user-group') !!}
        {!! $link('partner.upgrade', 'partner.upgrade*', 'Plans & Upgrade', 'fa-gem') !!}
        <div class="partner-sidebar-label">Account</div>
        {!! $link($profileRoute, $profilePattern, 'My Account', 'fa-gear') !!}
        {!! $link('support', 'support*', 'Help & Support', 'fa-circle-question') !!}
    </nav>
    <div class="partner-sidebar-footer">
        <div class="mb-3 flex justify-center">
            @include('partials.google-play-link')
        </div>
        <a href="{{ route($profileRoute) }}" class="partner-user-card"><span class="partner-avatar">{{ $initial }}</span><span class="min-w-0"><span class="block truncate text-sm font-bold">{{ $partner->name }}</span><span class="block truncate text-[10px] font-semibold uppercase tracking-wide text-blue-200">Partner</span></span><i class="fa-solid fa-chevron-right ml-auto text-xs text-blue-200"></i></a>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-400/30 bg-rose-500/10 px-3 py-2.5 text-xs font-extrabold uppercase tracking-wide text-rose-200 transition hover:bg-rose-500/20"><i class="fa-solid fa-right-from-bracket"></i>Log out</button></form>
    </div>
</aside>
<div class="partner-sidebar-scrim hidden"></div>

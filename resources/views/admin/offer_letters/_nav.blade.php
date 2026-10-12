{{--
    Offer-letter section nav.

    Included on every page in the section so the tabs never disappear once you
    step into Templates, Signatures or Branding. Each tab stays lit for its own
    sub-pages too, so editing a template still shows Templates as current.
--}}
@php
    $olTabs = [
        ['label' => 'All Letters',   'icon' => 'fa-inbox',       'route' => 'admin.offer-letters.index',     'on' => ['admin.offer-letters.index']],
        ['label' => 'Create Letter', 'icon' => 'fa-plus',        'route' => 'admin.offer-letters.create',    'on' => ['admin.offer-letters.create', 'admin.offer-letters.compose', 'admin.offer-letters.edit']],
        ['label' => 'Templates',     'icon' => 'fa-file-lines',  'route' => 'admin.offer-letters.templates', 'on' => ['admin.offer-letters.templates', 'admin.offer-letters.template.*']],
        ['label' => 'Signatures',    'icon' => 'fa-signature',   'route' => 'admin.offer-letters.signatures','on' => ['admin.offer-letters.signatures', 'admin.offer-letters.signature.*']],
        ['label' => 'Branding',      'icon' => 'fa-image',       'route' => 'admin.offer-letters.settings',  'on' => ['admin.offer-letters.settings*']],
    ];
@endphp

<div class="mb-6 flex flex-wrap gap-2 border-b border-white/10 pb-4">
    @foreach($olTabs as $tab)
        @php $current = request()->routeIs($tab['on']); @endphp
        <a href="{{ route($tab['route']) }}"
           @class([
               'rounded-xl px-4 py-2.5 text-sm font-bold transition',
               'bg-cyan-500 text-slate-950 hover:bg-cyan-400' => $current,
               'border border-white/20 bg-white/5 text-slate-100 hover:bg-white/10' => !$current,
           ])
           @if($current) aria-current="page" @endif>
            <i class="fa-solid {{ $tab['icon'] }} mr-1"></i>{{ $tab['label'] }}
        </a>
    @endforeach
</div>

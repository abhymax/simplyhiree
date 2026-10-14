{{--
    Client picker for a manager.

    The list runs to every client on the platform, so it carries its own search,
    a show-only-selected filter and a live count. Filtering hides rows rather
    than removing them, so ticks made before searching stay ticked and are still
    submitted.

    Expects: $clients. $assignedClientIds (ids already assigned) is optional, so
    the create screen can use this too with nothing ticked.
--}}
@php $olAssigned = $assignedClientIds ?? []; @endphp

<div class="sh-clientpicker">
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="relative flex-1 min-w-[220px]">
            <input type="text" data-cp-search
                   placeholder="Search clients by name or email..."
                   class="w-full bg-slate-800/80 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition h-10 px-4 pl-10 text-sm">
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
            </div>
            <button type="button" data-cp-clear
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition hidden">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer whitespace-nowrap">
            <input type="checkbox" data-cp-only
                   class="w-4 h-4 rounded bg-slate-800 border-slate-600 text-emerald-500 focus:ring-emerald-500">
            Selected only
        </label>

        <span data-cp-count class="text-xs font-bold rounded-full bg-white/10 text-slate-300 px-2.5 py-1 whitespace-nowrap"></span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar" data-cp-list>
        @foreach($clients as $client)
            @php $isOn = in_array($client->id, $olAssigned); @endphp
            <label data-cp-item
                   data-search="{{ strtolower($client->name . ' ' . ($client->email ?? '')) }}"
                   class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all group
                   {{ $isOn ? 'bg-emerald-900/20 border-emerald-500/50' : 'bg-white/5 border-white/10 hover:bg-white/10 hover:border-emerald-500/30' }}">
                <input type="checkbox" name="clients[]" value="{{ $client->id }}" @checked($isOn)
                       class="w-5 h-5 flex-shrink-0 text-emerald-500 bg-slate-800 border-slate-600 rounded focus:ring-emerald-500 focus:ring-offset-slate-900">
                <span class="ml-3 text-sm font-bold {{ $isOn ? 'text-white' : 'text-slate-300 group-hover:text-white' }} transition-colors">
                    {{ $client->name }}
                </span>
            </label>
        @endforeach
    </div>

    <p data-cp-empty class="hidden mt-3 text-sm text-slate-400">No client matches that search.</p>
</div>

<script>
(function () {
    document.querySelectorAll('.sh-clientpicker').forEach(function (root) {
        if (root.dataset.cpReady) return;
        root.dataset.cpReady = '1';

        var search = root.querySelector('[data-cp-search]'),
            clear  = root.querySelector('[data-cp-clear]'),
            only   = root.querySelector('[data-cp-only]'),
            count  = root.querySelector('[data-cp-count]'),
            empty  = root.querySelector('[data-cp-empty]'),
            items  = Array.prototype.slice.call(root.querySelectorAll('[data-cp-item]'));

        function boxOf(item) { return item.querySelector('input[type="checkbox"]'); }

        function apply() {
            var q = (search.value || '').trim().toLowerCase(),
                selectedOnly = only.checked,
                shown = 0;

            items.forEach(function (item) {
                var matches = !q || (item.getAttribute('data-search') || '').indexOf(q) !== -1,
                    visible = matches && (!selectedOnly || boxOf(item).checked);
                item.style.display = visible ? '' : 'none';
                if (visible) shown++;
            });

            clear.classList.toggle('hidden', !q);
            empty.classList.toggle('hidden', shown !== 0);
            render();
        }

        function render() {
            var n = items.filter(function (i) { return boxOf(i).checked; }).length;
            count.textContent = n + (n === 1 ? ' client assigned' : ' clients assigned');
            count.className = 'text-xs font-bold rounded-full px-2.5 py-1 whitespace-nowrap '
                + (n ? 'bg-emerald-500/20 text-emerald-200' : 'bg-white/10 text-slate-300');
        }

        search.addEventListener('input', apply);
        only.addEventListener('change', apply);
        clear.addEventListener('click', function () { search.value = ''; apply(); search.focus(); });
        root.addEventListener('change', function (e) {
            if (e.target.matches('input[name="clients[]"]')) {
                if (only.checked) { apply(); } else { render(); }
            }
        });

        render();
    });
})();
</script>

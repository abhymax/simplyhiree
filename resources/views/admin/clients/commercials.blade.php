<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>

        <div class="relative z-10 max-w-7xl mx-auto" x-data="{ mode: '{{ $commercial->billing_type ?? 'percentage_based' }}' }">
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.clients.show', $user) }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Client
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Permanent Hiring Commercials</h1>
                    <p class="text-blue-200 mt-1 text-base">
                        {{ $user->name }} <span class="text-blue-300/70">· {{ $user->email }}</span>
                    </p>
                </div>
                @if(session('success'))
                    <div class="px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">
                        <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                    </div>
                @endif
            </div>

            @if($errors->any())
                <div class="mb-6 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl text-sm">
                    <strong>Please fix:</strong>
                    <ul class="list-disc list-inside mt-1">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.clients.commercials.update', $user) }}">
                @csrf
                @method('PUT')

                {{-- BILLING TYPE TABS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6 shadow-2xl">
                    <h2 class="text-lg font-bold text-amber-300 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Billing Type
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="cursor-pointer flex items-center gap-3 bg-slate-800/60 hover:bg-slate-800 border border-white/10 rounded-xl px-4 py-3 transition" :class="mode === 'percentage_based' ? 'ring-2 ring-amber-400 border-amber-400/50' : ''">
                            <input type="radio" name="billing_type" value="percentage_based" x-model="mode" class="text-amber-500 focus:ring-amber-400">
                            <div>
                                <div class="text-white font-bold text-sm">Percentage Based — Slabs</div>
                                <div class="text-blue-200 text-xs">Fee % varies by candidate's CTC band.</div>
                            </div>
                        </label>
                        <label class="cursor-pointer flex items-center gap-3 bg-slate-800/60 hover:bg-slate-800 border border-white/10 rounded-xl px-4 py-3 transition" :class="mode === 'profile_wise' ? 'ring-2 ring-amber-400 border-amber-400/50' : ''">
                            <input type="radio" name="billing_type" value="profile_wise" x-model="mode" class="text-amber-500 focus:ring-amber-400">
                            <div>
                                <div class="text-white font-bold text-sm">Profile Wise</div>
                                <div class="text-blue-200 text-xs">Fee % by seniority (Entry / Mid / Sr / CXO).</div>
                            </div>
                        </label>
                        <label class="cursor-pointer flex items-center gap-3 bg-slate-800/60 hover:bg-slate-800 border border-white/10 rounded-xl px-4 py-3 transition" :class="mode === 'flat' ? 'ring-2 ring-amber-400 border-amber-400/50' : ''">
                            <input type="radio" name="billing_type" value="flat" x-model="mode" class="text-amber-500 focus:ring-amber-400">
                            <div>
                                <div class="text-white font-bold text-sm">Flat Fee</div>
                                <div class="text-blue-200 text-xs">Fixed INR per category (e.g. BPO / Sales).</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- PERCENTAGE — SLABS --}}
                <div x-show="mode === 'percentage_based'" x-cloak class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white"><i class="fa-solid fa-layer-group text-amber-300 mr-2"></i> CTC Slabs</h2>
                        <button type="button" onclick="addSlab()" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold rounded-lg">+ Add Slab</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-amber-200 text-[11px] uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2">Label</th>
                                    <th class="px-3 py-2">Min CTC (₹)</th>
                                    <th class="px-3 py-2">Max CTC (₹) <span class="text-amber-200/60 normal-case">(blank = no upper limit)</span></th>
                                    <th class="px-3 py-2">Fee %</th>
                                    <th class="px-3 py-2">Replacement (days)</th>
                                    <th class="px-3 py-2 w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="slab-rows">
                                @foreach($contract['percentage_based'] as $row)
                                    <tr>
                                        <td class="px-2 py-1.5"><input type="text" name="slab_label[]" value="{{ $row['label'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="slab_min_ctc[]" value="{{ $row['min_ctc'] ?? '' }}" min="0" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="slab_max_ctc[]" value="{{ $row['max_ctc'] ?? '' }}" min="0" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="slab_fee_percent[]" step="0.01" min="0" max="100" value="{{ $row['fee_percent'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="slab_replacement[]" min="0" max="365" value="{{ $row['replacement_days'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PROFILE WISE --}}
                <div x-show="mode === 'profile_wise'" x-cloak class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white"><i class="fa-solid fa-user-tie text-amber-300 mr-2"></i> Profile Tiers</h2>
                        <button type="button" onclick="addProfile()" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold rounded-lg">+ Add Profile</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-amber-200 text-[11px] uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2">Profile</th>
                                    <th class="px-3 py-2">Fee %</th>
                                    <th class="px-3 py-2">Replacement (days)</th>
                                    <th class="px-3 py-2 w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="prof-rows">
                                @foreach($contract['profile_wise'] as $row)
                                    <tr>
                                        <td class="px-2 py-1.5"><input type="text" name="prof_profile[]" value="{{ $row['profile'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="prof_fee_percent[]" step="0.01" min="0" max="100" value="{{ $row['fee_percent'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="prof_replacement[]" min="0" max="365" value="{{ $row['replacement_days'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- FLAT --}}
                <div x-show="mode === 'flat'" x-cloak class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-white"><i class="fa-solid fa-indian-rupee-sign text-amber-300 mr-2"></i> Flat Fee Categories</h2>
                        <button type="button" onclick="addFlat()" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold rounded-lg">+ Add Category</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-amber-200 text-[11px] uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2">Category</th>
                                    <th class="px-3 py-2">Fee Amount (₹)</th>
                                    <th class="px-3 py-2">Replacement (days)</th>
                                    <th class="px-3 py-2 w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="flat-rows">
                                @foreach($contract['flat'] as $row)
                                    <tr>
                                        <td class="px-2 py-1.5"><input type="text" name="flat_category[]" value="{{ $row['category'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="flat_fee_amount[]" step="0.01" min="0" value="{{ $row['fee_amount'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><input type="number" name="flat_replacement[]" min="0" max="365" value="{{ $row['replacement_days'] ?? '' }}" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
                                        <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- COMMON TERMS --}}
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6 shadow-2xl">
                    <h2 class="text-lg font-bold text-white mb-4"><i class="fa-solid fa-handshake text-amber-300 mr-2"></i> Common Terms</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-amber-200 text-[11px] font-bold uppercase mb-1">Invoice Raise (days)</label>
                            <input type="number" name="invoice_raise_days" min="0" max="365" value="{{ old('invoice_raise_days', $commercial->invoice_raise_days ?? 30) }}" required class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-3 py-2 h-11">
                            <p class="text-[10px] text-blue-200/70 mt-1">Days after joining when invoice is raised.</p>
                        </div>
                        <div>
                            <label class="block text-amber-200 text-[11px] font-bold uppercase mb-1">Payment Terms (days)</label>
                            <input type="number" name="payment_terms_days" min="0" max="365" value="{{ old('payment_terms_days', $commercial->payment_terms_days ?? 30) }}" required class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-3 py-2 h-11">
                            <p class="text-[10px] text-blue-200/70 mt-1">Days after invoice when payment is due.</p>
                        </div>
                        <div>
                            <label class="block text-amber-200 text-[11px] font-bold uppercase mb-1">GST</label>
                            <label class="flex items-center gap-2 h-11">
                                <input type="hidden" name="is_gst_applicable" value="0">
                                <input type="checkbox" name="is_gst_applicable" value="1" {{ ($commercial->is_gst_applicable ?? true) ? 'checked' : '' }} class="h-5 w-5 rounded border-white/30 bg-slate-800 text-amber-500 focus:ring-amber-400">
                                <span class="text-white text-sm font-semibold">GST Extra Applicable</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.clients.show', $user) }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-900 font-extrabold rounded-xl shadow-lg shadow-amber-500/30">
                        <i class="fa-solid fa-save mr-2"></i> Save Commercials
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function addSlab() {
        const r = document.getElementById('slab-rows').insertRow();
        r.innerHTML = `
            <td class="px-2 py-1.5"><input type="text" name="slab_label[]" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5" placeholder="e.g. Up to 5 Lakh"></td>
            <td class="px-2 py-1.5"><input type="number" name="slab_min_ctc[]" min="0" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><input type="number" name="slab_max_ctc[]" min="0" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><input type="number" name="slab_fee_percent[]" step="0.01" min="0" max="100" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><input type="number" name="slab_replacement[]" min="0" max="365" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>`;
    }
    function addProfile() {
        const r = document.getElementById('prof-rows').insertRow();
        r.innerHTML = `
            <td class="px-2 py-1.5"><input type="text" name="prof_profile[]" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5" placeholder="e.g. VP / Director"></td>
            <td class="px-2 py-1.5"><input type="number" name="prof_fee_percent[]" step="0.01" min="0" max="100" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><input type="number" name="prof_replacement[]" min="0" max="365" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>`;
    }
    function addFlat() {
        const r = document.getElementById('flat-rows').insertRow();
        r.innerHTML = `
            <td class="px-2 py-1.5"><input type="text" name="flat_category[]" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5" placeholder="e.g. Customer Support"></td>
            <td class="px-2 py-1.5"><input type="number" name="flat_fee_amount[]" step="0.01" min="0" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><input type="number" name="flat_replacement[]" min="0" max="365" class="w-full bg-slate-800 border border-white/10 rounded-lg text-white text-sm px-2 py-1.5"></td>
            <td class="px-2 py-1.5"><button type="button" onclick="this.closest('tr').remove()" class="text-rose-300 hover:text-rose-200 text-lg">&times;</button></td>`;
    }
    </script>

    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>

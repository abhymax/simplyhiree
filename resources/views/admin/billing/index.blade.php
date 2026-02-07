<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Money/Chart Glows --}}
        <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Billing & Invoices</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Monitor revenue and payment status.</p>
                </div>
                
                {{-- SUMMARY CARDS --}}
                <div class="mt-4 md:mt-0 flex gap-4">
                    <div class="bg-emerald-500/20 backdrop-blur-md border border-emerald-500/30 text-white px-6 py-3 rounded-2xl shadow-lg">
                        <p class="text-emerald-300 text-xs font-bold uppercase tracking-wider">Total Revenue</p>
                        {{-- Use null coalescing operator to prevent errors if variable is missing --}}
                        <p class="text-3xl font-black text-white">₹{{ number_format($totalRevenue ?? 0) }}</p>
                    </div>
                    <div class="bg-rose-500/20 backdrop-blur-md border border-rose-500/30 text-white px-6 py-3 rounded-2xl shadow-lg">
                        <p class="text-rose-300 text-xs font-bold uppercase tracking-wider">Pending</p>
                        <p class="text-3xl font-black text-white">₹{{ number_format($pendingAmount ?? 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- ADVANCED FILTERS --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.billing.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        
                        {{-- Search (Col-3) --}}
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Client / Job</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-white"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoices..." 
                                    class="w-full pl-10 bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                            </div>
                        </div>

                        {{-- Status Filter (Col-2) --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">Status</label>
                            <select name="status" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                                <option value="" class="text-gray-400">All Statuses</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>

                        {{-- Date Range Start (Col-2) --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">From Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                        </div>

                        {{-- Date Range End (Col-2) --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-cyan-300 uppercase mb-1 ml-1">To Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-blue-200/50 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400 font-medium h-[44px]">
                        </div>

                        {{-- Filter Buttons (Col-3) --}}
                        <div class="md:col-span-3 flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 px-4 rounded-xl font-bold shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-0.5 h-[44px] flex items-center justify-center gap-2">
                                <i class="fa-solid fa-filter"></i> Filter
                            </button>
                            
                            @if(request()->anyFilled(['search', 'status', 'start_date', 'end_date']))
                                <a href="{{ route('admin.billing.index') }}" class="bg-rose-500 hover:bg-rose-400 text-white p-2 rounded-xl transition h-[44px] w-[44px] flex items-center justify-center shadow-lg" title="Clear Filters">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Invoice Info</th>
                                <th class="px-6 py-5">Candidate / Job</th>
                                <th class="px-6 py-5">Amount</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Date</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($placements as $item)
                                <tr class="hover:bg-white/5 transition duration-200 group">
                                    
                                    {{-- Invoice ID & Client --}}
                                    <td class="px-6 py-5">
                                        <div class="font-bold text-white text-lg">#INV-{{ $item->id }}</div>
                                        <div class="text-blue-300 text-xs mt-1 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-building"></i> {{ $item->client_name }}
                                        </div>
                                    </td>

                                    {{-- Candidate / Job --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-md">
                                                {{ substr($item->candidate_name ?? 'C', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white">{{ $item->candidate_name }}</div>
                                                <div class="text-xs text-slate-400">{{ $item->job_title }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Amount --}}
                                    <td class="px-6 py-5">
                                        {{-- Assuming payout_amount is available on $item, otherwise fallback to calculated logic --}}
                                        <span class="text-emerald-300 font-black text-lg tracking-wide">
                                            ₹{{ number_format($item->payout_amount ?? 0) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @if($item->payment_status === 'paid')
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold shadow-lg shadow-emerald-500/20">
                                                <i class="fa-solid fa-check-circle"></i> Paid
                                            </span>
                                        @elseif($item->is_due)
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-500/20 text-red-300 border border-red-500/50 text-xs font-bold shadow-lg shadow-red-500/20 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/50 text-xs font-bold shadow-lg shadow-amber-500/20">
                                                <i class="fa-regular fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-5">
                                        <div class="text-white font-medium">{{ $item->invoice_date }}</div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400 bg-white/5 px-2 py-0.5 rounded-full inline-block mt-1 border border-white/5">
                                            Term: {{ $item->billable_period }}
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right">
                                        @if($item->payment_status !== 'paid' && $item->is_due)
                                            <form action="{{ route('admin.applications.markPaid', $item->id) }}" method="POST" onsubmit="return confirm('Confirm payment received for {{ $item->candidate_name }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition border border-blue-500 hover:shadow-blue-500/30 flex items-center gap-2 ml-auto">
                                                    <i class="fa-solid fa-check"></i> Mark Paid
                                                </button>
                                            </form>
                                        @elseif($item->payment_status === 'paid')
                                            <button disabled class="bg-white/5 text-slate-500 px-4 py-2 rounded-xl text-xs font-bold border border-white/5 cursor-not-allowed flex items-center gap-2 ml-auto">
                                                <i class="fa-solid fa-lock"></i> Closed
                                            </button>
                                        @else
                                            <span class="text-slate-500 text-xs italic">In Maturity Period</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-solid fa-file-invoice text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No invoices found.</p>
                                        <p class="text-blue-200 mt-2">Adjust your date range or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="p-6 border-t border-white/10 bg-slate-900/80 backdrop-blur-md">
                    <style>
                        nav[role="navigation"] p { color: #e2e8f0 !important; font-weight: 600; }
                        nav[role="navigation"] span.relative, nav[role="navigation"] a.relative {
                            background-color: rgba(255, 255, 255, 0.1) !important;
                            border-color: rgba(255, 255, 255, 0.2) !important;
                            color: white !important;
                            font-weight: 700;
                        }
                        nav[role="navigation"] span[aria-current="page"] span {
                            background-color: #0ea5e9 !important;
                            border-color: #0ea5e9 !important;
                            color: white !important;
                        }
                    </style>
                    
                    {{-- Pagination Link --}}
                    @if(method_exists($placements, 'links'))
                        {{ $placements->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
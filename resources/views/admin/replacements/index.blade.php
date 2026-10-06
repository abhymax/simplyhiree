<x-app-layout>
<div class="min-h-screen bg-slate-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 text-slate-100">
<div class="max-w-[1600px] mx-auto">
    <div class="mb-6"><h1 class="text-3xl font-extrabold text-white">Replacement & Guarantee Monitoring</h1><p class="text-slate-400 mt-1">Live guarantee windows, replacement progress, and vendor cost adjustments.</p></div>
    @if(session('success'))<div class="mb-4 p-3 border border-emerald-500/40 bg-emerald-500/10 text-emerald-200 rounded-lg">{{ session('success') }}</div>@endif
    @if(session('error') || (isset($errors) && $errors->any()))<div class="mb-4 p-3 border border-rose-500/40 bg-rose-500/10 text-rose-200 rounded-lg">{{ session('error') ?: $errors->first() }}</div>@endif
    @php
      $labels=['under_guarantee'=>'Under Guarantee','pending'=>'Pending','in_progress'=>'In Progress','closed'=>'Closed','expired'=>'Expired'];
      $styles=['under_guarantee'=>'text-cyan-300 border-cyan-500/40 bg-cyan-500/10','pending'=>'text-amber-300 border-amber-500/40 bg-amber-500/10','in_progress'=>'text-blue-300 border-blue-500/40 bg-blue-500/10','closed'=>'text-emerald-300 border-emerald-500/40 bg-emerald-500/10','expired'=>'text-slate-400 border-slate-600 bg-slate-800'];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
      @foreach($labels as $key=>$label)<a href="{{ route('admin.replacements.index',['status'=>$key]) }}" class="border {{ $styles[$key] }} rounded-lg p-4"><div class="text-xs font-bold uppercase">{{ $label }}</div><div class="text-2xl font-black mt-1">{{ $counts[$key] }}</div></a>@endforeach
      <div class="border border-rose-500/30 bg-rose-500/10 rounded-lg p-4"><div class="text-xs font-bold uppercase text-rose-300">Pending Cost</div><div class="text-xl font-black mt-1">₹{{ number_format($costSummary['pending']) }}</div></div>
    </div>
    <div class="flex gap-2 mb-4 flex-wrap"><a href="{{ route('admin.replacements.index') }}" class="px-3 py-2 rounded-md border border-slate-700 text-sm">All cases</a>@foreach($labels as $key=>$label)<a href="{{ route('admin.replacements.index',['status'=>$key]) }}" class="px-3 py-2 rounded-md border text-sm {{ request('status')===$key ? $styles[$key] : 'border-slate-700 text-slate-300' }}">{{ $label }}</a>@endforeach</div>
    <div class="border border-slate-800 rounded-lg overflow-hidden bg-slate-900/60"><div class="overflow-x-auto"><table class="min-w-full text-sm">
      <thead class="bg-slate-900 text-slate-400 uppercase text-xs"><tr><th class="p-4 text-left">Candidate / Job</th><th class="p-4 text-left">Client / Vendor</th><th class="p-4 text-left">Guarantee Window</th><th class="p-4 text-left">Replacement</th><th class="p-4 text-left">Cost Adjustment</th><th class="p-4 text-right">Actions</th></tr></thead>
      <tbody class="divide-y divide-slate-800">@forelse($apps as $app)<tr class="align-top hover:bg-white/[.02]">
        <td class="p-4"><div class="font-bold text-white">{{ $app->candidate_name ?: trim(($app->candidate?->first_name ?? '').' '.($app->candidate?->last_name ?? '')) ?: $app->candidateUser?->name ?: 'Candidate #'.$app->id }}</div><div class="text-slate-400">{{ $app->job?->title ?? '—' }}</div><div class="text-xs text-slate-500 mt-1">Application #{{ $app->id }}</div></td>
        <td class="p-4"><div>{{ $app->job?->user?->name ?? '—' }}</div><div class="text-xs text-slate-400 mt-1">Vendor: {{ $app->candidate?->partner?->name ?? 'Direct' }}</div></td>
        <td class="p-4"><div>Joined {{ $app->joining_date?->format('d M Y') }}</div><div class="text-xs mt-1 {{ $app->guarantee_days_remaining > 0 ? 'text-cyan-300':'text-slate-500' }}">{{ $app->guarantee_deadline_at?->format('d M Y') ?? 'No deadline' }} · {{ $app->guarantee_days_remaining }} days remaining</div><span class="inline-flex mt-2 px-2 py-1 rounded border text-xs font-bold {{ $styles[$app->monitor_status] }}">{{ $labels[$app->monitor_status] }}</span></td>
        <td class="p-4"><div>{{ $app->replacement_requested_at?->format('d M Y') ?? 'Not requested' }}</div>@if($app->replacement_deadline)<div class="text-xs text-amber-300 mt-1">Partner deadline {{ $app->replacement_deadline->format('d M Y') }}</div>@endif @if($app->replacement_reason)<div class="text-xs text-slate-400 mt-1">{{ Str::limit($app->replacement_reason,80) }}</div>@endif</td>
        <td class="p-4">@if($app->partnerCreditNote)<div class="font-bold">₹{{ number_format($app->partnerCreditNote->amount,2) }}</div><div class="text-xs uppercase text-amber-300">{{ $app->partnerCreditNote->status }}</div>@else<span class="text-slate-500">None</span>@endif</td>
        <td class="p-4"><div class="flex flex-col items-end gap-2"><a class="text-cyan-300 text-xs" href="{{ route('admin.applications.show',$app) }}">View application</a>
          @if(in_array($app->monitor_status,['pending','in_progress']))
          <a href="{{ route('admin.replacements.candidate',$app) }}" class="rounded bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-950 hover:bg-amber-400">Nominate candidate</a>
          @endif
          @if($app->monitor_status==='pending')
          @php $options=\App\Models\JobApplication::where('job_id',$app->job_id)->where('id','!=',$app->id)->whereHas('candidate',fn($q)=>$q->where('partner_id',$app->candidate?->partner_id))->with('candidate')->get(); @endphp
          <form method="POST" action="{{ route('admin.replacements.approve',$app) }}" class="flex gap-1">@csrf<select required name="replacement_application_id" class="bg-slate-950 border-slate-700 rounded text-xs max-w-40"><option value="">Replacement</option>@foreach($options as $option)<option value="{{ $option->id }}">#{{ $option->id }} {{ $option->candidate?->first_name }}</option>@endforeach</select><button class="bg-blue-600 px-2 rounded text-xs">Link</button></form>
          @endif
          @if(in_array($app->monitor_status,['pending','in_progress']))<details class="text-right"><summary class="cursor-pointer text-xs text-rose-300">Add / edit cost adjustment</summary><form method="POST" action="{{ route('admin.replacements.cost-adjustment',$app) }}" class="mt-2 grid gap-1 w-56">@csrf<input required min="0.01" step="0.01" type="number" name="amount" value="{{ $app->replacement_cost_adjustment ?: '' }}" placeholder="Amount" class="bg-slate-950 border-slate-700 rounded text-xs"><input required name="reason" value="{{ $app->partnerCreditNote?->reason }}" placeholder="Adjustment reason" class="bg-slate-950 border-slate-700 rounded text-xs"><button class="bg-rose-700 py-1 rounded text-xs">Save pending deduction</button></form></details><form method="POST" action="{{ route('admin.replacements.close',$app) }}" onsubmit="return confirm('Close this replacement case?')">@csrf<button class="text-xs text-slate-400 underline">Close case</button></form>@endif
        </div></td>
      </tr>@empty<tr><td colspan="6" class="p-16 text-center text-slate-400">No placements match this filter.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4 border-t border-slate-800">{{ $apps->links() }}</div></div>
</div></div>
</x-app-layout>

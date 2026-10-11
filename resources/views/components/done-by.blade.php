{{--
    "Shortlisted by Aman · 2 days ago" beside a listing.

    Shows the most recent recorded action on an application so a client,
    vendor or Superadmin can see which of their team did what.

    :application  the JobApplication
    :side         optional — only show actions from this side
                  (client | vendor | simplyhiree)
--}}
@props(['application' => null, 'side' => null])

@php
    $dbApp = $application;
    $dbActivity = null;

    if ($dbApp) {
        $dbActivity = \App\Models\ApplicationActivity::query()
            ->where('job_application_id', $dbApp->id)
            ->when($side, fn ($q) => $q->where('actor_side', $side))
            ->latest('id')
            ->first();
    }

    // Fall back to the columns that pre-date the activity log, so older
    // records still show who submitted or selected the candidate.
    $dbFallbackName = null;
    $dbFallbackWhat = null;
    if (!$dbActivity && $dbApp) {
        if ($dbApp->selected_by_admin_id) {
            $dbFallbackName = optional(\App\Models\User::find($dbApp->selected_by_admin_id))->name;
            $dbFallbackWhat = 'Selected';
        } elseif ($dbApp->submitted_by_user_id) {
            $dbFallbackName = optional(\App\Models\User::find($dbApp->submitted_by_user_id))->name;
            $dbFallbackWhat = 'Submitted';
        }
    }
@endphp

@if($dbActivity && $dbActivity->actor_name)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border border-indigo-300/25 bg-indigo-500/10 px-2 py-0.5 text-[10px] font-bold text-indigo-100']) }}
          title="{{ $dbActivity->label() }}{{ $dbActivity->detail ? ' (' . $dbActivity->detail . ')' : '' }} by {{ $dbActivity->actor_name }} on {{ $dbActivity->created_at?->format('d M Y, h:i A') }}">
        <i class="fa-regular fa-user text-[9px] opacity-80"></i>
        {{ $dbActivity->label() }} by {{ \Illuminate\Support\Str::limit($dbActivity->actor_name, 18) }}
        <span class="opacity-60">· {{ $dbActivity->created_at?->diffForHumans(null, true) }} ago</span>
    </span>
@elseif($dbFallbackName)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border border-slate-300/20 bg-white/5 px-2 py-0.5 text-[10px] font-bold text-slate-200']) }}
          title="{{ $dbFallbackWhat }} by {{ $dbFallbackName }}">
        <i class="fa-regular fa-user text-[9px] opacity-80"></i>
        {{ $dbFallbackWhat }} by {{ \Illuminate\Support\Str::limit($dbFallbackName, 18) }}
    </span>
@endif

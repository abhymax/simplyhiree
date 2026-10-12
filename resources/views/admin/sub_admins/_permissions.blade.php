{{--
    Permission checkboxes for a manager.

    Driven by Permission::all(), so a newly created permission shows up here on
    its own. The map below only supplies a readable name, a line of explanation
    and a grouping; anything not listed still renders, under "Other", rather
    than going missing.

    Expects: $permissions (names). $userPermissions (names already held) is
    optional, so the create screen can use this too with nothing ticked.
--}}
@php
    // Two of these come from the Manager role rather than from the user, so they
    // were always drawn unchecked even though every manager has them, and
    // ticking them changed nothing. Show them as held, and not revocable here.
    $viaRole = isset($user)
        ? $user->getPermissionsViaRoles()->pluck('name')->all()
        : optional(\Spatie\Permission\Models\Role::where('name', 'Manager')->first())
            ->permissions->pluck('name')->all() ?? [];

    $meta = [
        'access_admin_panel'      => ['Access Admin Panel',   'Sign in to the admin panel at all. Without this nothing else applies.', 'Core'],
        'manage_sub_admins'       => ['Manage Managers',      'Create other managers and set what they can see.',                      'Core'],
        'manage_clients'          => ['Manage Clients',       'Open the Clients section and edit client records.',                     'Core'],
        'impersonate_clients'     => ['Impersonate Clients',  'Sign in as a client to see what they see.',                             'Core'],

        'view_application_data'   => ['All Applications',     'Every candidate application across clients.',                           'Recruitment'],
        'view_pending_jobs'       => ['Pending & Archived Jobs', 'Jobs awaiting approval, and the archive.',                           'Recruitment'],
        'view_candidate_data'     => ['Candidate Data',       'Candidate profiles and contact details.',                               'Recruitment'],
        'view_interview_data'     => ['Interview Data',       'Scheduled interviews and their outcomes.',                              'Recruitment'],
        'view_assessments'        => ['Questionnaires',       'The assessment question library.',                                      'Recruitment'],
        'view_assessment_results' => ['Assessment Results',   'Scores and reports from completed assessments.',                        'Recruitment'],

        'view_partner_data'       => ['Partner / Vendor Data', 'Vendors, their plans and assignment requests.',                        'Partners'],
        'view_vendor_ratings'     => ['Vendor Ratings',       'Vendor scorecards and ratings.',                                        'Partners'],

        'view_billing_data'       => ['Billing & Finance',    'Billing report, invoices, credit notes, payouts and the finance queue.', 'Money'],
        'manage_offer_letters'    => ['Offer Letters',        'Generate, edit and send offer letters, and manage templates.',          'Money'],
        'view_analytics'          => ['Advanced Analytics',   'Platform-wide analytics dashboards.',                                   'Money'],

        'view_chats'              => ['Chats',                'Answer chats from clients, vendors and candidates.',                    'Support'],
        'view_activity_logs'      => ['Activity Logs',        'The audit trail of who did what.',                                      'Support'],
    ];

    $order  = ['Core', 'Recruitment', 'Partners', 'Money', 'Support', 'Other'];
    $groups = [];
    foreach ($permissions as $permission) {
        $groups[$meta[$permission][2] ?? 'Other'][] = $permission;
    }
    uksort($groups, fn ($a, $b) => array_search($a, $order) <=> array_search($b, $order));
@endphp

@foreach($groups as $group => $names)
    <div class="mb-6">
        <p class="mb-3 text-xs font-extrabold uppercase tracking-wider text-amber-300/80">{{ $group }}</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($names as $permission)
                @php
                    $label    = $meta[$permission][0] ?? ucfirst(str_replace('_', ' ', $permission));
                    $fromRole = in_array($permission, $viaRole);
                @endphp
                <label class="flex items-start gap-3 rounded-lg p-3 transition {{ $fromRole ? 'opacity-70' : 'cursor-pointer hover:bg-white/5' }}">
                    <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                           class="mt-0.5 h-5 w-5 flex-shrink-0 rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900"
                           @checked($fromRole || in_array($permission, $userPermissions ?? []))
                           @disabled($fromRole)>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-100">
                            {{ $label }}
                            @if($fromRole)
                                <span class="ml-1 rounded bg-slate-700/80 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-300">from role</span>
                            @endif
                        </span>
                        @if(!empty($meta[$permission][1]))
                            <span class="block text-xs text-slate-400">{{ $meta[$permission][1] }}</span>
                        @endif
                        @if($fromRole)
                            <span class="block text-xs text-amber-300/70">Every manager has this. It cannot be removed here.</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    </div>
@endforeach

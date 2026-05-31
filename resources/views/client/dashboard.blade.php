@extends('layouts.client')

@section('client_content')


            {{-- Dashboard Intro --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 id="dynamic-welcome-greeting" class="text-2xl font-extrabold text-white tracking-tight">Welcome back, {{ Auth::user()->name }} 👋</h1>
                    <p class="text-xs text-slate-400 mt-1">Here's what's happening with your business today.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="#my-jobs" class="px-4 py-2 bg-slate-900/60 hover:bg-slate-900 border border-white/5 text-slate-200 hover:text-white text-xs font-bold rounded-lg transition">
                        <i class="fa-solid fa-list mr-1.5"></i> My Jobs
                    </a>
                    <a href="{{ route('client.jobs.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition shadow-md shadow-blue-500/20">
                        <i class="fa-solid fa-plus mr-1.5"></i> Post Job
                    </a>
                </div>
            </div>

            {{-- Row 1: Core Metrics Cards (Matches color combinations of JPEG exactly) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Metric Card 1: Awaiting Review (candidates needing the client's decision) --}}
                <a href="{{ route('client.applications.index', ['status' => 'Approved']) }}" class="metric-card-indigo p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-blue-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Awaiting Review</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $awaitingReview ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-blue-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-user-clock"></i>
                        <span>Candidates need your decision</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-blue-500"><i class="fa-solid fa-user-clock"></i></div>
                </a>

                {{-- Metric Card 2: Interviews Today (Purple) --}}
                <a href="{{ route('client.interviews.calendar') }}" class="metric-card-purple p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-purple-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Interviews Today</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $todayInterviews ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-purple-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Scheduled for Today</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-purple-500"><i class="fa-solid fa-video"></i></div>
                </a>

                {{-- Metric Card 3: Active Applicants / Earnings (Emerald) --}}
                <a href="{{ route('client.applications.index') }}" class="metric-card-emerald p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-emerald-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Active Applicants</p>
                    <h3 class="text-3xl font-extrabold text-emerald-400 mt-3 relative z-10">{{ $totalApplicants ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-emerald-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-arrow-up"></i>
                        <span>Approved Candidates Only</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-emerald-500"><i class="fa-solid fa-user-check"></i></div>
                </a>

                {{-- Metric Card 4: Invoices Due / Replacement Requests (Amber) --}}
                <a href="{{ route('client.billing') }}" class="metric-card-amber p-6 rounded-2xl relative overflow-hidden group block hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 cursor-pointer shadow-lg shadow-amber-500/5">
                    <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider relative z-10">Invoices Due</p>
                    <h3 class="text-3xl font-extrabold text-white mt-3 relative z-10">{{ $dueInvoicesCount ?? 0 }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-amber-400 mt-4 font-semibold relative z-10">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Outstanding: ₹{{ number_format($totalOutstandingInvoices ?? 145000) }}</span>
                    </div>
                    {{-- Failsafe absolute positioned background icon --}}
                    <div class="card-decor-icon text-amber-500"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </a>
            </div>

            {{-- Row 2: Daily Pulse + Submission Trend (compact) --}}
            <div class="glass-card rounded-2xl p-5 flex flex-col gap-5 w-full">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-heart-pulse text-blue-400"></i> Daily Pulse
                        </h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Your activity summary for today</p>
                    </div>
                    <span class="text-[10px] font-bold bg-blue-600/15 text-blue-300 px-2.5 py-1 rounded-full border border-blue-500/20">Active Pipeline</span>
                </div>

                {{-- Compact metric row: solid colour icon + value + label --}}
                @php
                    // [solid icon colour, tinted card bg, border tint]
                    $pulseSolid = [
                        'blue'    => ['#3b82f6', 'rgba(59,130,246,0.12)',  'rgba(59,130,246,0.30)'],
                        'indigo'  => ['#6366f1', 'rgba(99,102,241,0.12)',  'rgba(99,102,241,0.30)'],
                        'emerald' => ['#10b981', 'rgba(16,185,129,0.12)',  'rgba(16,185,129,0.30)'],
                        'amber'   => ['#f59e0b', 'rgba(245,158,11,0.12)',  'rgba(245,158,11,0.30)'],
                        'rose'    => ['#f43f5e', 'rgba(244,63,94,0.12)',   'rgba(244,63,94,0.30)'],
                    ];
                @endphp
                <div style="display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px;">
                    @foreach($dailyPulse ?? [] as $pulse)
                        @php $sc = $pulseSolid[$pulse['color']] ?? $pulseSolid['blue']; @endphp
                        <a href="{{ $pulse['link'] ?? '#' }}"
                           class="flex flex-col items-center text-center gap-1.5 px-2 py-3 rounded-xl transition hover:scale-[1.03] hover:brightness-110"
                           style="background: {{ $sc[1] }}; border: 1px solid {{ $sc[2] }};">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $sc[0] }};">
                                <i class="fa-solid {{ $pulse['icon'] }} text-white text-[11px]"></i>
                            </div>
                            <div class="text-base font-extrabold text-white leading-none">{{ $pulse['value'] }}</div>
                            <div class="text-[10px] text-slate-300 font-semibold leading-tight">{{ $pulse['label'] }}</div>
                        </a>
                    @endforeach
                </div>

                {{-- Interview Activity Trend — real SVG line chart --}}
                @php
                    $trend = $submissionTrend ?? [];
                    $n = max(count($trend), 1);
                    $trendMax = max(1, collect($trend)->max('count') ?? 0);
                    $trend7Total = collect($trend)->sum('count');
                    // Build polyline points across a 600x100 viewBox (10px top/bottom padding)
                    $pts = [];
                    foreach (array_values($trend) as $idx => $pt) {
                        $x = $n > 1 ? round($idx / ($n - 1) * 580 + 10, 1) : 300;
                        $y = round(90 - ($pt['count'] / $trendMax) * 78, 1); // 90 baseline, 78 usable height
                        $pts[] = ['x' => $x, 'y' => $y, 'c' => $pt['count'], 'l' => $pt['label']];
                    }
                    $linePath = collect($pts)->map(fn($p) => $p['x'].','.$p['y'])->implode(' ');
                    $areaPath = '10,100 '.$linePath.' '.($pts[count($pts)-1]['x'] ?? 590).',100';
                @endphp
                <div>
                    <div class="flex justify-between items-baseline mb-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Interview Activity · Last 2 Weeks</p>
                        <span class="text-[10px] font-bold text-blue-400">{{ $trend7Total }} interviews this fortnight</span>
                    </div>
                    <div class="h-28 bg-black/20 rounded-xl border border-white/5 relative px-2 pt-2">
                        <!-- Custom Tooltip -->
                        <div id="chart-tooltip" class="absolute pointer-events-none opacity-0 bg-slate-950/95 text-white text-[10px] px-2.5 py-1.5 rounded-lg border border-blue-500/40 shadow-2xl transition-all duration-150 backdrop-blur-md z-20 flex flex-col gap-0.5" style="left: 0; top: 0;">
                            <span class="font-bold text-blue-400" id="tooltip-date"></span>
                            <span class="text-slate-200" id="tooltip-stats"></span>
                        </div>
                        
                        <svg class="w-full h-[78px]" viewBox="0 0 600 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="trendGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.35"></stop>
                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"></stop>
                                </linearGradient>
                            </defs>
                            <polygon points="{{ $areaPath }}" fill="url(#trendGrad)"></polygon>
                            <polyline points="{{ $linePath }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"></polyline>
                            @foreach($pts as $p)
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="5" fill="#0b1020" stroke="#60a5fa" stroke-width="2" style="cursor:pointer"
                                        onmouseover="showChartTooltip(event, '{{ $p['l'] }}', '{{ $p['c'] }}')"
                                        onmouseout="hideChartTooltip()">
                                </circle>
                            @endforeach
                        </svg>
                        <div class="flex justify-between text-[6.5px] sm:text-[7.5px] text-slate-500 font-normal px-1 mt-1 tracking-tighter">
                            @foreach($pts as $p)
                                <span class="{{ $p['l'] === 'Today' ? 'text-blue-300 font-semibold' : '' }}">{{ $p['l'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 3: Funnel & Pipeline Chart Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Left Widget: Interview Pipeline (Horizontal Funnel matching JPEG shape exactly) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Interview Pipeline</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Hiring funnel overview</p>
                    </div>

                    @php
                        $funnelData = array_values($funnel ?? []);
                        $funnelShortlisted = $funnelShortlisted ?? (collect($funnelData)->firstWhere('label', 'Shortlisted')['count'] ?? 0);
                        $funnelJoined = $funnelJoined ?? (collect($funnelData)->firstWhere('label', 'Joined')['count'] ?? 0);
                        // Reference palette: blue shades -> teal -> orange tip
                        $funnelFill = ['#2563eb', '#3b82f6', '#0d9488', '#ea580c'];
                        // Cone centred at cx; tapers from 140 half-width to a point (last band = triangle).
                        $cx = 150; $halfW = [140, 105, 70, 35, 0]; $bandH = 55;
                    @endphp

                    <style>
                        .funnel-svg a { cursor: pointer; }
                        .funnel-svg a polygon { transition: filter .2s ease; }
                        .funnel-svg a:hover polygon { filter: brightness(1.18); }
                    </style>
                    <svg class="funnel-svg w-full" viewBox="0 0 380 232" style="max-height: 250px;">
                        @foreach($funnelData as $i => $stg)
                            @php
                                $yTop = $i * $bandH; $yBot = $yTop + $bandH + 1; // +1 overlap removes hairline seams
                                $tl = $cx - $halfW[$i];   $tr = $cx + $halfW[$i];
                                $bl = $cx - $halfW[$i+1]; $br = $cx + $halfW[$i+1];
                                $midY = $yTop + ($bandH / 2) + 4;
                            @endphp
                            <a href="{{ $stg['link'] ?? '#' }}">
                                <title>{{ $stg['label'] }}: {{ $stg['count'] }} — click to view</title>
                                <polygon points="{{ $tl }},{{ $yTop }} {{ $tr }},{{ $yTop }} {{ $br }},{{ $yBot }} {{ $bl }},{{ $yBot }}"
                                         fill="{{ $funnelFill[$i] ?? '#ea580c' }}"></polygon>
                                {{-- stage label centred inside the cone --}}
                                <text x="{{ $cx }}" y="{{ $midY }}" text-anchor="middle" fill="#fff" font-size="12.5" font-weight="700">{{ $stg['label'] }}</text>
                                {{-- count right-aligned in its own column --}}
                                <text x="372" y="{{ $midY }}" text-anchor="end" fill="#fff" font-size="16" font-weight="900">{{ $stg['count'] }}</text>
                            </a>
                        @endforeach
                    </svg>

                    {{-- conversion footnote --}}
                    @php
                        $subVal = $funnelData[0]['count'] ?? 0;
                        $joinVal = $funnelData[count($funnelData)-1]['count'] ?? 0;
                        $conv = $subVal > 0 ? round($joinVal / $subVal * 100, 1) : 0;
                    @endphp
                    <div class="mt-3 text-center text-[10px] text-slate-400 font-semibold">
                        Overall conversion: <span class="text-emerald-400 font-bold">{{ $conv }}%</span>
                        ({{ $joinVal }} joined of {{ $subVal }} shortlisted)
                    </div>
                </div>

                {{-- Right Widget: Billing & Hiring Analytics --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5">
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Billing &amp; Hiring Analytics</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Financial &amp; performance summary</p>
                    </div>

                    @php
                        $totalBilled = $totalPaidInvoices + $totalOutstandingInvoices;
                        $billingPaidPct = $totalBilled > 0 ? (int) round($totalPaidInvoices / $totalBilled * 100) : 0;
                        $hiringSuccessRate = $funnelShortlisted > 0 ? (int) round($funnelJoined / $funnelShortlisted * 100) : 0;
                        $reviewRate = $performance['response_rate'] ?? 0;
                        $interviewRate = $performance['interview_rate'] ?? 0;

                        $perfRows = [
                            ['label' => 'Shortlisted Hired',   'value' => $hiringSuccessRate, 'color' => 'emerald', 'hex' => '#10b981'],
                            ['label' => 'Invoices Settled',    'value' => $billingPaidPct,    'color' => 'blue',    'hex' => '#3b82f6'],
                            ['label' => 'Profiles Reviewed',   'value' => $reviewRate,        'color' => 'amber',   'hex' => '#f59e0b'],
                            ['label' => 'Interviews Scheduled', 'value' => $interviewRate,     'color' => 'purple',  'hex' => '#a855f7'],
                        ];
                    @endphp

                    <div class="space-y-4">
                        @foreach($perfRows as $row)
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-300">{{ $row['label'] }}</span>
                                <span class="text-xs font-bold text-{{ $row['color'] }}-400">{{ $row['value'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ min($row['value'], 100) }}%; background: {{ $row['hex'] }}"></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-3 bg-blue-600/10 border border-blue-500/20 rounded-xl text-center text-[10px] font-extrabold text-blue-400 uppercase tracking-wide">
                        {{ $funnelJoined }} of {{ $funnelShortlisted }} shortlisted candidates successfully joined
                    </div>
                </div>
            </div>

            {{-- Row 4: Quick Workspace --}}
            <div>
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Workspace
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                    
                    {{-- Workspace 1: Successful Hires --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-blue flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Successful Hires</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ number_format($funnelJoined) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Candidates onboarded successfully</p>
                        </div>
                        <a href="{{ route('client.applications.index', ['joined_status' => 'Joined']) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Hired
                        </a>
                    </div>

                    {{-- Workspace 2: Approved Applications --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-orange flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Applications</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $totalApplicants ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Approved candidates</p>
                        </div>
                        <a href="{{ route('client.applications.index') }}" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Review Applications
                        </a>
                    </div>

                    {{-- Workspace 3: Interviews Today --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-purple flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Interviews</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $todayInterviews ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Scheduled today</p>
                        </div>
                        <a href="{{ route('client.interviews.calendar') }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Calendar
                        </a>
                    </div>

                    {{-- Workspace 4: Active Openings --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-indigo flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Openings</p>
                            <h4 class="text-2xl font-extrabold text-white mt-1">{{ $activeJobs ?? 0 }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Active vacancies live</p>
                        </div>
                        <a href="#my-jobs" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            View Jobs
                        </a>
                    </div>

                    {{-- Workspace 5: Billing & Invoices --}}
                    <div class="glass-card rounded-2xl p-5 workspace-card-emerald flex flex-col justify-between min-h-[150px]">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Invoices & Billing</p>
                            <h4 class="text-2xl font-extrabold text-emerald-400 mt-1">₹{{ number_format($totalOutstandingInvoices) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Outstanding Invoices</p>
                        </div>
                        <a href="{{ route('client.billing') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10px] font-bold text-center transition uppercase">
                            Manage Invoices
                        </a>
                    </div>

                </div>
            </div>

            {{-- Row 5: My Job Postings Table Section (Requirements Workspace) --}}
            <div id="my-jobs" class="glass-card rounded-2xl overflow-hidden" style="scroll-margin-top: 100px;">
                <div class="p-5 border-b border-white/5 flex flex-col sm:flex-row justify-between gap-4 sm:items-center bg-slate-900/20">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span> My Job Requirements
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 ml-3">Currently listing <span class="text-white font-bold">{{ $totalJobs ?? 0 }}</span> requirements, including <span class="text-emerald-400 font-bold">{{ $activeJobs ?? 0 }}</span> active vacancies.</p>
                    </div>
                    <a href="{{ route('client.jobs.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5 shrink-0 shadow-md">
                        <i class="fa-solid fa-plus"></i> Post New Vacancy
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="jobs-table min-w-full text-left text-sm">
                        <thead class="bg-slate-950/40 text-cyan-400 uppercase font-extrabold border-b border-white/5 text-[11px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Designation / Role</th>
                                <th class="px-6 py-4">Requirements</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Posted On</th>
                                <th class="px-6 py-4 text-right" style="min-width:220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white">
                            @forelse($jobs->take(5) as $job)
                                @php
                                    $jobCode = $job->job_code ?? ('SH-JOB-' . str_pad((string) $job->id, 6, '0', STR_PAD_LEFT));
                                    $jobInitial = strtoupper(substr($job->title, 0, 1)) ?: 'J';
                                    $approvedCount = $job->jobApplications->where('status', 'Approved')->count();
                                @endphp
                                <tr class="hover:bg-white/5 transition duration-200">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold ring-2 ring-white/10 shrink-0 text-sm">{{ $jobInitial }}</div>
                                            <div class="min-w-0">
                                                <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-white hover:text-cyan-300 transition text-sm">{{ $job->title }}</a>
                                                <div class="text-cyan-200 text-xs truncate mt-0.5"><i class="fa-solid fa-location-dot mr-1 text-slate-500"></i> {{ $job->location }} · {{ $job->job_type }}</div>
                                                <div class="text-[10px] text-slate-500 mt-1">{{ $jobCode }} · {{ $job->openings ?? 1 }} opening(s)</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <div class="text-white font-semibold text-xs">{{ $job->formatted_experience }} exp</div>
                                        <div class="text-[11px] text-slate-400 mt-1">Gender: {{ $job->gender_preference ?? 'Any' }}</div>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        @php $st = $job->status; @endphp
                                        @if($st === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-check"></i> Active</span>
                                        @elseif($st === 'pending_approval')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold animate-pulse"><i class="fa-regular fa-clock"></i> Pending Approval</span>
                                        @elseif($st === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                                        @elseif($st === 'on_hold')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-pause"></i> On Hold</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-500/20 text-slate-400 border border-slate-500/30 text-[10px] font-bold"><i class="fa-solid fa-circle-info"></i> {{ ucwords(str_replace('_',' ',$st)) }}</span>
                                        @endif
                                        @if($job->deactivation_requested_at)
                                            <div class="mt-1.5 inline-flex items-center gap-1 text-[9px] font-bold bg-rose-500/25 text-rose-300 border border-rose-500/40 px-2 py-0.5 rounded uppercase tracking-wider">
                                                <i class="fa-solid fa-hourglass-half"></i> Closing Requested
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $job->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-3.5 text-right" style="min-width:220px;">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($job->status === 'pending_approval')
                                                <a href="{{ route('client.jobs.edit', $job) }}"
                                                   class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-lg text-xs font-bold border border-amber-300 shadow-md transition animate-pulse"
                                                   style="padding: 0.45rem 0.9rem;">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit Pending
                                                </a>
                                            @else
                                                <a href="{{ route('client.jobs.applicants', $job) }}"
                                                   class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold border border-indigo-400 shadow-md transition"
                                                   style="padding: 0.45rem 0.9rem;">
                                                    <i class="fa-regular fa-eye"></i> View Applicants ({{ $approvedCount }})
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="bg-white/5 inline-block p-6 rounded-full mb-4 border border-white/5">
                                            <i class="fa-regular fa-folder-open text-5xl text-blue-400"></i>
                                        </div>
                                        <p class="font-bold text-white text-lg">No requirements posted yet</p>
                                        <a href="{{ route('client.jobs.create') }}" class="text-blue-400 hover:text-white underline mt-2 inline-block text-sm">Post your first requirement</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($totalJobs ?? 0) > 5)
                    <div class="p-4 border-t border-white/5 text-center">
                        <a href="{{ route('client.jobs.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-400 hover:text-blue-300 transition">
                            View all {{ $totalJobs }} requirements <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Row 6: Detailed Widgets Grid (Exact Copy of JPEG sections & data) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                {{-- Widget 1: Notifications (Replacing Recent Activities) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Notifications</h4>
                        <a href="#" onclick="alert('Notification page coming soon'); return false;" class="text-xs font-bold text-blue-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-4 pt-1">
                        @php
                            $notifications = auth()->user()->notifications()->take(5)->get();
                        @endphp

                        @forelse($notifications as $notification)
                            @php
                                $icon = $notification->data['icon'] ?? 'circle-info';
                                $color = 'bg-blue-500/20 text-blue-400';
                                if($icon == 'check-circle' || $icon == 'user-check') $color = 'bg-green-500/20 text-green-400';
                                if($icon == 'x-circle' || $icon == 'user-xmark') $color = 'bg-red-500/20 text-red-400';
                                if($icon == 'calendar-event') $color = 'bg-purple-500/20 text-purple-400';
                            @endphp
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $color }} flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-{{ str_replace('circle-check', 'check-circle', str_replace('circle-xmark', 'x-circle', $icon)) }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-300 font-medium leading-relaxed">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    <span class="text-[9px] text-slate-500 font-bold block mt-1 capitalize tracking-wide">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-500/20 text-slate-400 flex items-center justify-center shrink-0 text-sm">
                                    <i class="fa-solid fa-bell-slash"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-300 font-medium leading-relaxed">No recent notifications.</p>
                                    <span class="text-[9px] text-slate-500 font-bold block mt-1 capitalize tracking-wide">System Idle</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Widget 2: Top Requirements (Dynamic Job Openings) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col gap-5">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Top Requirements</h4>
                        <a href="#my-jobs" class="text-xs font-bold text-blue-400 hover:underline">View All</a>
                    </div>

                    <div class="space-y-4 pt-1">
                        @php
                            $topJobs = collect();
                            if (isset($jobs)) {
                                $topJobs = $jobs->sortByDesc(function($j) {
                                    return $j->jobApplications->where('status', 'Approved')->count();
                                })->take(4);
                            }
                        @endphp

                        @foreach($topJobs as $job)
                            <div class="flex items-center justify-between gap-3 p-3 bg-slate-950/50 border border-white/10 rounded-xl hover:bg-slate-950/70 hover:border-white/20 transition block">
                                <a href="{{ route('jobs.show', $job->id) }}" class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-blue-600/10 text-blue-400 flex items-center justify-center text-sm shrink-0"><i class="fa-solid fa-briefcase"></i></div>
                                    <div class="min-w-0">
                                        <h5 class="font-bold text-xs text-white truncate hover:text-cyan-300 transition">{{ $job->title }}</h5>
                                        <p class="text-[9px] text-slate-500 mt-0.5 truncate">{{ $job->location ?? '—' }} · {{ $job->job_type ?? '—' }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('client.jobs.applicants', $job) }}" class="text-[10px] font-extrabold bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 px-2 py-0.5 rounded border border-blue-500/20 shadow-md shrink-0 transition cursor-pointer">
                                    {{ $job->jobApplications->where('status', 'Approved')->count() }} Applicants
                                </a>
                            </div>
                        @endforeach

                        @if($topJobs->isEmpty())
                            <div class="p-6 bg-white/5 border border-white/5 rounded-xl text-center text-xs text-slate-400">
                                No job postings found. Post a job to see requirements here!
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Widget 3: Hiring & Billing Success (Replicating JPEG data & circular gauge exactly) --}}
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between gap-5 col-span-1 md:col-span-2 xl:col-span-1">
                    <div class="flex justify-between items-baseline">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Hiring &amp; Billing Success</h4>
                        <span class="text-xs font-bold text-slate-400">This Month</span>
                    </div>

                    @php
                        $perf = $performance ?? [];
                        $totalBilled = $totalPaidInvoices + $totalOutstandingInvoices;
                        $billingPaidPct = $totalBilled > 0 ? (int) round($totalPaidInvoices / $totalBilled * 100) : 0;
                        $hiringSuccessRate = $funnelShortlisted > 0 ? (int) round($funnelJoined / $funnelShortlisted * 100) : 0;
                        $reviewRate = $perf['response_rate'] ?? 0;
                        $interviewRate = $perf['interview_rate'] ?? 0;

                        // Overall score = average of the 4 client-centric metrics
                        $overall = (int) round(collect([
                            $hiringSuccessRate,
                            $billingPaidPct,
                            $reviewRate,
                            $interviewRate,
                        ])->avg());
                        $circumference = 251.2;
                        $offset = $circumference - ($circumference * min($overall, 100) / 100);
                        $rating = $overall >= 75 ? 'Excellent' : ($overall >= 50 ? 'Good' : ($overall >= 25 ? 'Fair' : 'Getting Started'));
                        $perfBars = [
                            ['label' => 'Shortlisted Hired',   'value' => $hiringSuccessRate, 'hex' => '#10b981'],
                            ['label' => 'Invoices Settled',    'value' => $billingPaidPct,    'hex' => '#3b82f6'],
                            ['label' => 'Profiles Reviewed',   'value' => $reviewRate,        'hex' => '#f59e0b'],
                            ['label' => 'Interviews Scheduled', 'value' => $interviewRate,     'hex' => '#a855f7'],
                        ];
                    @endphp

                    {{-- Circular progress ring (real overall score) --}}
                    <div class="flex items-center justify-center gap-6 pt-2">
                        <div class="relative w-36 h-36 flex items-center justify-center shrink-0 shadow-lg shadow-cyan-500/10 rounded-full">
                            <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 96 96">
                                <circle cx="48" cy="48" r="42" fill="transparent" stroke="#111827" stroke-width="5"></circle>
                                <circle cx="48" cy="48" r="42" fill="transparent" stroke="#06b6d4" stroke-width="5"
                                        stroke-dasharray="{{ 2 * 3.1416 * 42 }}" stroke-dashoffset="{{ (2 * 3.1416 * 42) - ((2 * 3.1416 * 42) * min($overall,100) / 100) }}" stroke-linecap="round"></circle>
                            </svg>
                            <div class="text-center relative z-10 leading-tight" style="max-width: 88px;">
                                <span class="text-2xl font-black text-white block leading-none">{{ $overall }}%</span>
                                <span class="text-[9px] text-cyan-300 font-bold leading-tight block mt-1 whitespace-normal">{{ $rating }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-300">Hiring &amp; Payment health score</p>
                            <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">Average across candidate conversions, invoice clearings, and interview rates in your workspace.</p>
                        </div>
                    </div>

                    {{-- Progress Bars (real) --}}
                    <div class="space-y-2 pt-1">
                        @foreach($perfBars as $bar)
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] font-bold text-slate-300">
                                    <span>{{ $bar['label'] }}</span>
                                    <span class="text-white">{{ $bar['value'] }}%</span>
                                </div>
                                <div class="w-full h-1 bg-slate-950 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ min($bar['value'], 100) }}%; background: {{ $bar['hex'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>



@endsection

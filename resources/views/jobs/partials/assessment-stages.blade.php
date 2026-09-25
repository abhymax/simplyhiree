{{--
    Reusable "Assessment / Questionnaire stages" section for the job create &
    edit forms (client + superadmin). Submits an ordered list of stages:
        assessment_stages[i][assessment_id]
        assessment_stages[i][next_stage_start_hours]
    Expected variables:
      $availableAssessments  – Collection of active assessments the poster may attach
      $job                   – nullable Job (edit) for pre-seeding stages
      $assessmentCreateRoute – route name for the "Create Questionnaire" link
--}}
@php
    $availableAssessments = $availableAssessments ?? collect();
    $assessmentCreateRoute = $assessmentCreateRoute ?? null;

    // Pre-seed: prefer old() input on validation bounce, else existing job stages.
    $seedStages = [];
    if (is_array(old('assessment_stages'))) {
        foreach (old('assessment_stages') as $s) {
            if (empty($s['assessment_id'])) continue;
            $seedStages[] = [
                'assessment_id'          => (string) $s['assessment_id'],
                'next_stage_start_hours' => (string) ($s['next_stage_start_hours'] ?? ''),
            ];
        }
    } elseif (!empty($job) && $job->exists) {
        foreach ($job->assessmentStages()->orderBy('stage_order')->get() as $s) {
            $seedStages[] = [
                'assessment_id'          => (string) $s->assessment_id,
                'next_stage_start_hours' => $s->next_stage_start_hours === null ? '' : (string) $s->next_stage_start_hours,
            ];
        }
    }

    $assessmentOptions = $availableAssessments->map(fn ($a) => [
        'id'   => $a->id,
        'name' => $a->name,
        'tag'  => $a->tag,
    ])->values();
@endphp

<section class="job-form-section mb-6" style="border:1px solid rgba(129,140,248,.25);background:rgba(79,70,229,.08);border-radius:1rem;padding:1.25rem;">
    <h2 class="job-section-title" style="color:#fff;font-size:.88rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;display:flex;align-items:center;gap:.55rem;margin-bottom:.35rem;">
        <i class="fa-solid fa-clipboard-question" style="width:1.9rem;height:1.9rem;display:inline-flex;align-items:center;justify-content:center;border-radius:.6rem;background:rgba(99,102,241,.18);color:#c4b5fd;"></i>
        Assessment Questionnaires <span style="font-weight:600;text-transform:none;letter-spacing:0;color:#c7d2fe;font-size:.8rem;">(optional)</span>
    </h2>
    <p class="text-xs" style="color:#c7d2fe;margin-bottom:1rem;line-height:1.5;">
        Attach one or more questionnaires as ordered stages. Candidates must clear each stage in order before they can apply.
        Leave empty to allow direct applications.
    </p>

    <div x-data="jobAssessmentStages({{ Illuminate\Support\Js::from($seedStages) }}, {{ Illuminate\Support\Js::from($assessmentOptions) }})">
        <template x-if="options.length === 0">
            <div class="rounded-xl border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-100">
                You don't have any active questionnaires yet.
                @if($assessmentCreateRoute)
                    <a href="{{ route($assessmentCreateRoute) }}" target="_blank" rel="noopener" class="font-bold underline text-amber-200 hover:text-white">Create a questionnaire</a>
                    first, then return here to attach it.
                @endif
            </div>
        </template>

        <div class="space-y-3" x-show="options.length > 0" x-cloak>
            <template x-for="(stage, idx) in stages" :key="stage._key">
                <div class="rounded-xl border border-white/15 bg-slate-900/40 p-3.5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-500/25 text-indigo-200 font-extrabold text-sm" x-text="idx + 1"></span>
                        <div class="flex-1 min-w-0">
                            <label class="block text-[11px] font-bold uppercase tracking-wide text-indigo-200 mb-1">Stage <span x-text="idx + 1"></span> — Questionnaire</label>
                            <select :name="`assessment_stages[${idx}][assessment_id]`" x-model="stage.assessment_id"
                                    class="block w-full rounded-lg border border-white/20 bg-slate-900/70 text-white text-sm px-3 py-2">
                                <option value="">— Select a questionnaire —</option>
                                {{-- Options rendered server-side so x-model can preselect saved
                                     values reliably (Alpine can't preselect against x-for options). --}}
                                @foreach($assessmentOptions as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['name'] }}@if(!empty($opt['tag'])) · {{ $opt['tag'] }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" @click="removeStage(idx)" title="Remove stage"
                                class="flex-shrink-0 mt-5 text-rose-300 hover:text-rose-200 px-2 py-1">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                    <div class="mt-3 pl-11" x-show="idx < stages.length - 1">
                        <label class="block text-[11px] font-bold uppercase tracking-wide text-slate-300 mb-1">
                            Window to start next stage (hours)
                        </label>
                        <input type="number" min="0" max="8760" :name="`assessment_stages[${idx}][next_stage_start_hours]`"
                               x-model="stage.next_stage_start_hours" placeholder="Leave blank = no time limit"
                               class="block w-full max-w-xs rounded-lg border border-white/20 bg-slate-900/70 text-white text-sm px-3 py-2">
                        <p class="mt-1 text-[11px] text-slate-400">After passing Stage <span x-text="idx + 1"></span>, the candidate has this many hours to begin Stage <span x-text="idx + 2"></span>.</p>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3" x-show="options.length > 0" x-cloak>
            <button type="button" @click="addStage()"
                    class="inline-flex items-center gap-2 rounded-lg border border-indigo-400/40 bg-indigo-500/15 text-indigo-100 text-sm font-bold px-3.5 py-2 hover:bg-indigo-500/25 transition">
                <i class="fa-solid fa-plus"></i> Add stage
            </button>
            @if($assessmentCreateRoute)
                <a href="{{ route($assessmentCreateRoute) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-300 hover:text-white transition">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Create new questionnaire
                </a>
            @endif
        </div>
    </div>

    @error('assessment_stages') <span class="text-rose-300 text-xs mt-2 block">{{ $message }}</span> @enderror
    @error('assessment_stages.*.assessment_id') <span class="text-rose-300 text-xs mt-1 block">{{ $message }}</span> @enderror
</section>

<script>
    function jobAssessmentStages(seed, options) {
        let keyCounter = 0;
        const makeStage = (s = {}) => ({
            _key: keyCounter++,
            assessment_id: s.assessment_id ?? '',
            next_stage_start_hours: s.next_stage_start_hours ?? '',
        });
        return {
            options: options || [],
            stages: (seed && seed.length ? seed.map(makeStage) : []),
            addStage() { this.stages.push(makeStage()); },
            removeStage(i) { this.stages.splice(i, 1); },
        };
    }
</script>

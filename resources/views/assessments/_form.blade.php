@php
    $isEdit = (bool) ($assessment ?? null);
    $formAction = $isEdit ? route($routes['update'], $assessment) : route($routes['store']);
    $scoringType = old('scoring_type', $assessment->scoring_type ?? 'mcq');

    // Build the initial questions array for whichever mode we're in.
    if ($isEdit && ($assessment->scoring_type ?? 'mcq') === 'weighted') {
        $initialQuestions = $assessment->questions->map(function ($q) {
            $sa = $q->options->firstWhere('option_text', 'Strongly Agree');
            $direction = ($sa && (int) $sa->weight === 1) ? 'reverse' : 'direct';
            return [
                'question_text' => $q->question_text,
                'category'      => $q->category ?? '',
                'direction'     => $direction,
                'marks'         => 1, 'correct' => 0,
                'options'       => [['text' => ''], ['text' => '']],
            ];
        })->values()->all();
    } elseif ($isEdit) {
        $initialQuestions = $assessment->questions->map(function ($q) {
            $idx = $q->options->values()->search(fn ($o) => $o->is_correct);
            return [
                'question_text' => $q->question_text,
                'marks'         => (int) $q->marks,
                'correct'       => $idx === false ? 0 : (int) $idx,
                'options'       => $q->options->map(fn ($o) => ['text' => $o->option_text])->values(),
                'category'      => '', 'direction' => 'direct',
            ];
        })->values()->all();
    } else {
        $initialQuestions = [[
            'question_text' => '', 'marks' => 1, 'correct' => 0,
            'options' => [['text' => ''], ['text' => '']],
            'category' => '', 'direction' => 'direct',
        ]];
    }
    $ic = 'w-full bg-slate-800/80 border border-white/15 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition px-3 py-2.5';
    $lc = 'block text-xs font-bold text-cyan-300 uppercase tracking-wide mb-1';
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-2xl border border-rose-500/40 bg-rose-500/15 p-4 text-rose-100 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formAction }}"
      x-data="{ scoringType: '{{ $scoringType }}',
                questions: {{ \Illuminate\Support\Js::from($initialQuestions) }},
                likert(direction) {
                    const w = direction === 'reverse' ? [5,4,3,2,1] : [1,2,3,4,5];
                    return ['Strongly Disagree','Disagree','Neutral','Agree','Strongly Agree'].map((l,i)=>({label:l,weight:w[i]}));
                },
                addQuestion() { this.questions.push({ question_text:'', marks:1, correct:0, options:[{text:''},{text:''}], category:'', direction:'direct' }); },
                removeQuestion(i) { if (this.questions.length > 1) this.questions.splice(i,1); },
                addOption(qi) { if (this.questions[qi].options.length < 6) this.questions[qi].options.push({text:''}); },
                removeOption(qi,oi) { let q=this.questions[qi]; if (q.options.length > 2){ q.options.splice(oi,1); if(q.correct>=q.options.length) q.correct=0; } } }">
    @csrf
    @if($isEdit) @method('PATCH') @endif
    <input type="hidden" name="scoring_type" :value="scoringType">

    {{-- Scoring mode toggle --}}
    <div class="rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl p-4 mb-6 shadow-xl">
        <label class="{{ $lc }} mb-2">Scoring type</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button type="button" @click="scoringType='mcq'"
                    :class="scoringType==='mcq' ? 'border-cyan-400 bg-cyan-500/15' : 'border-white/15 bg-slate-800/40'"
                    class="text-left rounded-2xl border p-4 transition">
                <div class="font-bold text-white"><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>MCQ — right/wrong</div>
                <div class="text-xs text-slate-300 mt-1">One correct option per question, full marks if correct. Best for knowledge/aptitude tests.</div>
            </button>
            <button type="button" @click="scoringType='weighted'"
                    :class="scoringType==='weighted' ? 'border-violet-400 bg-violet-500/15' : 'border-white/15 bg-slate-800/40'"
                    class="text-left rounded-2xl border p-4 transition">
                <div class="font-bold text-white"><i class="fa-solid fa-sliders text-violet-300 mr-2"></i>Likert / Weighted — psychometric</div>
                <div class="text-xs text-slate-300 mt-1">5-point agreement scale, every answer scores 1–5, rolled up by competency. No single "correct" answer.</div>
            </button>
        </div>
    </div>

    {{-- Meta --}}
    <div class="rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl p-6 mb-6 shadow-xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="{{ $lc }}">Questionnaire name *</label>
                <input type="text" name="name" value="{{ old('name', $assessment->name ?? '') }}" required placeholder="e.g. Psychometric Screening" class="{{ $ic }}">
            </div>
            <div>
                <label class="{{ $lc }}">Tag / Category</label>
                <input type="text" name="tag" list="asmt-tags" value="{{ old('tag', $assessment->tag ?? '') }}" placeholder="e.g. Psychometric, Electrical" class="{{ $ic }}">
                <datalist id="asmt-tags">@foreach($tags as $t)<option value="{{ $t }}">@endforeach</datalist>
            </div>
            <div>
                <label class="{{ $lc }}">Status</label>
                <select name="status" class="{{ $ic }}">
                    <option value="active" @selected(old('status', $assessment->status ?? 'active')==='active')>Active</option>
                    <option value="archived" @selected(old('status', $assessment->status ?? '')==='archived')>Archived</option>
                </select>
            </div>
            <div>
                <label class="{{ $lc }}">
                    <span x-show="scoringType==='mcq'">Passing percentage *</span>
                    <span x-show="scoringType==='weighted'" x-cloak>Qualifying % (0 = profile only)</span>
                </label>
                <input type="number" name="passing_percentage" min="0" max="100" value="{{ old('passing_percentage', $assessment->passing_percentage ?? 50) }}" required class="{{ $ic }}">
                <span x-show="scoringType==='weighted'" x-cloak class="text-[11px] text-slate-400 mt-1 block">Set 0 to record scores as a profile without pass/fail, or a % to gate on the overall score.</span>
            </div>
            <div>
                <label class="{{ $lc }}">Time limit (minutes)</label>
                <input type="number" name="time_limit_minutes" min="1" max="600" value="{{ old('time_limit_minutes', $assessment->time_limit_minutes ?? '') }}" placeholder="e.g. 30 (blank = no limit)" class="{{ $ic }}">
            </div>
            <div>
                <label class="{{ $lc }}">Number of attempts *</label>
                <input type="number" name="max_attempts" min="1" max="10" value="{{ old('max_attempts', $assessment->max_attempts ?? 1) }}" required class="{{ $ic }}">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 text-sm text-slate-200 cursor-pointer">
                    <input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $assessment->shuffle_questions ?? true)) class="rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                    Shuffle questions
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lc }}">Description (optional)</label>
                <textarea name="description" rows="2" class="{{ $ic }}" placeholder="Shown to the candidate before starting">{{ old('description', $assessment->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Questions --}}
    <div class="rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl p-6 mb-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">
                <i class="fa-solid fa-list-check text-cyan-400 mr-2"></i>
                <span x-show="scoringType==='mcq'">Questions (MCQ)</span>
                <span x-show="scoringType==='weighted'" x-cloak>Statements (Likert)</span>
            </h3>
            <button type="button" @click="addQuestion()" class="px-3 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold"><i class="fa-solid fa-plus mr-1"></i>Add</button>
        </div>

        {{-- MCQ editor --}}
        <template x-if="scoringType==='mcq'">
            <div>
                <template x-for="(q, qi) in questions" :key="qi">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 mb-4">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span class="text-xs font-bold text-slate-400 mt-2">Q<span x-text="qi+1"></span></span>
                            <textarea rows="2" required :name="'questions['+qi+'][question_text]'" x-model="q.question_text" placeholder="Enter the question" class="{{ $ic }} flex-1"></textarea>
                            <div class="w-20">
                                <input type="number" min="1" max="100" :name="'questions['+qi+'][marks]'" x-model.number="q.marks" title="Marks" class="{{ $ic }} text-center">
                                <span class="block text-[10px] text-slate-500 text-center mt-0.5">marks</span>
                            </div>
                            <button type="button" @click="removeQuestion(qi)" x-show="questions.length>1" class="mt-1 h-9 w-9 shrink-0 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white" title="Remove"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <p class="text-[11px] text-slate-400 mb-2">Select the correct option:</p>
                        <template x-for="(o, oi) in q.options" :key="oi">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="radio" :name="'questions['+qi+'][correct]'" :value="oi" x-model.number="q.correct" class="text-emerald-500 focus:ring-emerald-500" title="Mark correct">
                                <input type="text" required :name="'questions['+qi+'][options]['+oi+'][text]'" x-model="o.text" :placeholder="'Option '+(oi+1)" class="{{ $ic }} flex-1">
                                <button type="button" @click="removeOption(qi,oi)" x-show="q.options.length>2" class="h-8 w-8 shrink-0 rounded-lg bg-white/10 hover:bg-rose-500 text-slate-300 hover:text-white" title="Remove option"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </template>
                        <button type="button" @click="addOption(qi)" x-show="q.options.length<6" class="mt-1 text-xs font-bold text-cyan-300 hover:text-cyan-200"><i class="fa-solid fa-plus mr-1"></i>Add option</button>
                    </div>
                </template>
            </div>
        </template>

        {{-- Likert / weighted editor --}}
        <template x-if="scoringType==='weighted'">
            <div>
                <p class="text-xs text-violet-200/80 mb-4">Each statement is answered on a fixed 5-point scale. Pick a <b>direction</b>: <b>Direct</b> = agreement is positive (Strongly Agree scores 5). <b>Reverse</b> = agreement is negative (Strongly Agree scores 1). Group statements by <b>competency</b> to get per-competency scores.</p>
                <template x-for="(q, qi) in questions" :key="qi">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4 mb-4">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <span class="text-xs font-bold text-slate-400 mt-2">S<span x-text="qi+1"></span></span>
                            <textarea rows="2" required :name="'questions['+qi+'][question_text]'" x-model="q.question_text" placeholder="Enter the statement" class="{{ $ic }} flex-1"></textarea>
                            <button type="button" @click="removeQuestion(qi)" x-show="questions.length>1" class="mt-1 h-9 w-9 shrink-0 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white" title="Remove"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="{{ $lc }}">Competency / category</label>
                                <input type="text" :name="'questions['+qi+'][category]'" x-model="q.category" list="asmt-cats" placeholder="e.g. Teamwork, Initiative" class="{{ $ic }}">
                            </div>
                            <div>
                                <label class="{{ $lc }}">Direction</label>
                                <select :name="'questions['+qi+'][direction]'" x-model="q.direction" class="{{ $ic }}">
                                    <option value="direct">Direct (agree = high score)</option>
                                    <option value="reverse">Reverse (agree = low score)</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="opt in likert(q.direction)" :key="opt.label">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-slate-900/60 px-2.5 py-1 text-xs text-slate-300">
                                    <span x-text="opt.label"></span>
                                    <span class="font-bold text-violet-300" x-text="'= '+opt.weight"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
                <datalist id="asmt-cats"></datalist>
            </div>
        </template>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route($routes['index']) }}" class="px-6 py-3 rounded-xl border border-white/15 text-slate-200 font-bold">Cancel</a>
        <button class="px-8 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold">{{ $isEdit ? 'Save changes' : 'Create questionnaire' }}</button>
    </div>
</form>

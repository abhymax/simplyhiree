<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public const TAGS = ['Psychometric', 'Aptitude', 'Mechanical', 'Electrical', 'Technical', 'General'];

    private function isAdmin(): bool
    {
        return request()->routeIs('admin.*');
    }

    /** Route-name prefix + view namespace for the current panel. */
    private function panel(): array
    {
        if ($this->isAdmin()) {
            return ['prefix' => 'admin.assessments', 'views' => 'admin.assessments'];
        }
        return ['prefix' => 'client.assessments', 'views' => 'client.assessments'];
    }

    /** Company owner id whose questionnaires we scope to (client side). */
    private function ownerId(): ?int
    {
        return $this->isAdmin() ? null : Auth::user()->clientOwnerId();
    }

    private function routeNames(): array
    {
        $p = $this->panel()['prefix'];
        return [
            'index'   => "$p.index",
            'create'  => "$p.create",
            'store'   => "$p.store",
            'edit'    => "$p.edit",
            'update'  => "$p.update",
            'destroy' => "$p.destroy",
        ];
    }

    private function authorizeAccess(Assessment $assessment): void
    {
        if ($this->isAdmin()) {
            return; // superadmin/manager manages all
        }
        if ((int) $assessment->user_id !== (int) Auth::user()->clientOwnerId()) {
            abort(403, 'This questionnaire belongs to another company.');
        }
    }

    public function index()
    {
        $query = Assessment::withCount('questions')->latest();
        if (!$this->isAdmin()) {
            $query->where('user_id', $this->ownerId());
        }
        return view($this->panel()['views'] . '.index', [
            'assessments' => $query->get(),
            'routes'      => $this->routeNames(),
            'isAdmin'     => $this->isAdmin(),
        ]);
    }

    public function create()
    {
        return view($this->panel()['views'] . '.form', [
            'assessment' => null,
            'routes'     => $this->routeNames(),
            'tags'       => self::TAGS,
            'isAdmin'    => $this->isAdmin(),
        ]);
    }

    public function edit(Assessment $assessment)
    {
        $this->authorizeAccess($assessment);
        $assessment->load('questions.options');
        return view($this->panel()['views'] . '.form', [
            'assessment' => $assessment,
            'routes'     => $this->routeNames(),
            'tags'       => self::TAGS,
            'isAdmin'    => $this->isAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $request) {
            $assessment = Assessment::create([
                'user_id'            => $this->isAdmin() ? null : $this->ownerId(),
                'is_global'          => $this->isAdmin(),
                'name'               => $data['name'],
                'tag'                => $data['tag'] ?? null,
                'description'        => $data['description'] ?? null,
                'passing_percentage' => $data['passing_percentage'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'max_attempts'       => $data['max_attempts'],
                'shuffle_questions'  => (bool) ($request->boolean('shuffle_questions')),
                'status'             => $data['status'] ?? 'active',
            ]);
            $this->syncQuestions($assessment, $request->input('questions', []));
        });
        return redirect()->route($this->routeNames()['index'])->with('success', 'Questionnaire saved.');
    }

    public function update(Request $request, Assessment $assessment)
    {
        $this->authorizeAccess($assessment);
        $data = $this->validated($request);
        DB::transaction(function () use ($assessment, $data, $request) {
            $assessment->update([
                'name'               => $data['name'],
                'tag'                => $data['tag'] ?? null,
                'description'        => $data['description'] ?? null,
                'passing_percentage' => $data['passing_percentage'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'max_attempts'       => $data['max_attempts'],
                'shuffle_questions'  => (bool) ($request->boolean('shuffle_questions')),
                'status'             => $data['status'] ?? 'active',
            ]);
            $assessment->questions()->delete(); // cascade removes options; recreate fresh
            $this->syncQuestions($assessment, $request->input('questions', []));
        });
        return redirect()->route($this->routeNames()['index'])->with('success', 'Questionnaire updated.');
    }

    public function destroy(Assessment $assessment)
    {
        $this->authorizeAccess($assessment);
        $assessment->delete();
        return redirect()->route($this->routeNames()['index'])->with('success', 'Questionnaire deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'                       => 'required|string|max:255',
            'tag'                        => 'nullable|string|max:60',
            'description'                => 'nullable|string|max:2000',
            'passing_percentage'         => 'required|integer|min:1|max:100',
            'time_limit_minutes'         => 'nullable|integer|min:1|max:600',
            'max_attempts'               => 'required|integer|min:1|max:10',
            'status'                     => 'nullable|in:active,archived',
            'questions'                  => 'required|array|min:1',
            'questions.*.question_text'  => 'required|string|max:2000',
            'questions.*.marks'          => 'nullable|integer|min:1|max:100',
            'questions.*.correct'        => 'required',
            'questions.*.options'        => 'required|array|min:2|max:6',
            'questions.*.options.*.text' => 'required|string|max:1000',
        ], [
            'questions.required'            => 'Add at least one question.',
            'questions.*.correct.required'  => 'Mark the correct option for each question.',
            'questions.*.options.min'       => 'Each question needs at least two options.',
        ]);
    }

    private function syncQuestions(Assessment $assessment, array $questions): void
    {
        foreach (array_values($questions) as $qi => $q) {
            $question = $assessment->questions()->create([
                'question_text' => $q['question_text'],
                'marks'         => (int) ($q['marks'] ?? 1),
                'sort_order'    => $qi,
            ]);
            $correct = (string) ($q['correct'] ?? '');
            foreach (array_values($q['options'] ?? []) as $oi => $opt) {
                $question->options()->create([
                    'option_text' => $opt['text'],
                    'is_correct'  => ((string) $oi === $correct),
                    'sort_order'  => $oi,
                ]);
            }
        }
    }
}

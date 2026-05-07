<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPage::withCount('registrations')->latest()->paginate(20);
        return view('admin.landing_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.landing_pages.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->handleUploads($request, $data);
        $data = $this->handleVideoUpload($request, $data);
        $data = $this->buildJsonSections($request, $data);
        $data['form_fields'] = $this->buildFormFields($request);
        $data['slug'] = $this->uniqueSlug($request->slug ?: $request->title);

        LandingPage::create($data);

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'Landing page created successfully.');
    }

    public function edit(LandingPage $landingPage)
    {
        return view('admin.landing_pages.edit', compact('landingPage'));
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $data = $this->validateData($request, $landingPage->id);
        $data = $this->handleUploads($request, $data, $landingPage);
        $data = $this->handleVideoUpload($request, $data, $landingPage);
        $data = $this->buildJsonSections($request, $data);
        $data['form_fields'] = $this->buildFormFields($request);

        $newSlug = $request->slug ?: $request->title;
        if (Str::slug($newSlug) !== $landingPage->slug) {
            $data['slug'] = $this->uniqueSlug($newSlug, $landingPage->id);
        } else {
            $data['slug'] = $landingPage->slug;
        }

        $landingPage->update($data);

        return redirect()->route('admin.landing-pages.edit', $landingPage)
            ->with('success', 'Landing page updated successfully.');
    }

    public function show(LandingPage $landingPage)
    {
        $registrations = $landingPage->registrations()->latest()->paginate(30);
        return view('admin.landing_pages.show', compact('landingPage', 'registrations'));
    }

    public function exportRegistrations(LandingPage $landingPage)
    {
        $registrations = $landingPage->registrations()->latest()->get();
        $filename = 'leads_' . $landingPage->slug . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registrations) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'City', 'Registered At']);
            foreach ($registrations as $r) {
                fputcsv($out, [$r->id, $r->name, $r->email, $r->phone, $r->city, $r->created_at->format('d M Y H:i')]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(LandingPage $landingPage)
    {
        if ($landingPage->logo_path) Storage::disk('public')->delete($landingPage->logo_path);
        if ($landingPage->hero_image_path) Storage::disk('public')->delete($landingPage->hero_image_path);
        if ($landingPage->host_photo_path) Storage::disk('public')->delete($landingPage->host_photo_path);
        if ($landingPage->video_file_path) Storage::disk('public')->delete($landingPage->video_file_path);
        $landingPage->delete();

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'Landing page deleted.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'                 => 'required|string|max:255',
            'slug'                  => 'nullable|string|max:255',
            'status'                => 'required|in:draft,published',
            'meta_title'            => 'nullable|string|max:255',
            'meta_description'      => 'nullable|string|max:500',
            'primary_color'         => 'nullable|string|max:20',
            'secondary_color'       => 'nullable|string|max:20',
            'hero_headline'         => 'required|string|max:255',
            'hero_subheadline'      => 'nullable|string|max:500',
            'cta_text'              => 'nullable|string|max:100',
            'video_url'             => 'nullable|string|max:500',
            'video_section_title'   => 'nullable|string|max:255',
            'video_section_description' => 'nullable|string|max:500',
            'video_file'            => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:51200',
            'event_date'            => 'nullable|date',
            'event_time'            => 'nullable|string|max:50',
            'event_platform'        => 'nullable|string|max:100',
            'event_language'        => 'nullable|string|max:100',
            'seats_total'           => 'nullable|integer|min:0',
            'registration_deadline' => 'nullable|date',
            'about_title'           => 'nullable|string|max:255',
            'about_description'     => 'nullable|string',
            'host_name'             => 'nullable|string|max:255',
            'host_title'            => 'nullable|string|max:255',
            'host_bio'              => 'nullable|string',
            'footer_disclaimer'     => 'nullable|string',
            'earnings_summary'      => 'nullable|string',
            'contact_info'          => 'nullable|string|max:255',
            'tagline'               => 'nullable|string|max:255',
            'logo'                  => 'nullable|image|max:2048',
            'hero_image'            => 'nullable|image|max:4096',
            'host_photo'            => 'nullable|image|max:2048',
        ]);
    }

    private function handleVideoUpload(Request $request, array $data, ?LandingPage $page = null): array
    {
        if ($request->hasFile('video_file')) {
            if ($page && $page->video_file_path) {
                Storage::disk('public')->delete($page->video_file_path);
            }
            $data['video_file_path'] = $request->file('video_file')->store('landing_pages/videos', 'public');
        }
        unset($data['video_file']);
        return $data;
    }

    private function handleUploads(Request $request, array $data, ?LandingPage $page = null): array
    {
        foreach (['logo' => 'logo_path', 'hero_image' => 'hero_image_path', 'host_photo' => 'host_photo_path'] as $field => $column) {
            if ($request->hasFile($field)) {
                if ($page && $page->{$column}) {
                    Storage::disk('public')->delete($page->{$column});
                }
                $data[$column] = $request->file($field)->store('landing_pages', 'public');
            }
        }
        unset($data['logo'], $data['hero_image'], $data['host_photo']);
        return $data;
    }

    private function buildJsonSections(Request $request, array $data): array
    {
        // Learnings
        $learnings = [];
        foreach ((array) $request->input('learning_title', []) as $i => $title) {
            $desc = $request->input('learning_description', [])[$i] ?? '';
            if (trim($title)) $learnings[] = ['title' => $title, 'description' => $desc];
        }
        $data['learnings'] = $learnings ?: null;

        // Qualifications
        $quals = [];
        foreach ((array) $request->input('qualification_text', []) as $text) {
            if (trim($text)) $quals[] = ['text' => $text];
        }
        $data['qualifications'] = $quals ?: null;

        // Benefits
        $benefits = [];
        foreach ((array) $request->input('benefit_title', []) as $i => $title) {
            $desc = $request->input('benefit_description', [])[$i] ?? '';
            if (trim($title)) $benefits[] = ['title' => $title, 'description' => $desc];
        }
        $data['benefits'] = $benefits ?: null;

        // FAQs
        $faqs = [];
        foreach ((array) $request->input('faq_question', []) as $i => $q) {
            $a = $request->input('faq_answer', [])[$i] ?? '';
            if (trim($q)) $faqs[] = ['question' => $q, 'answer' => $a];
        }
        $data['faqs'] = $faqs ?: null;

        // Trust badges (icon + text)
        $trust = [];
        foreach ((array) $request->input('trust_text', []) as $i => $text) {
            $icon = $request->input('trust_icon', [])[$i] ?? '';
            if (trim($text)) $trust[] = ['icon' => $icon, 'text' => $text];
        }
        $data['trust_badges'] = $trust ?: null;

        // Career outcomes (icon + text)
        $outcomes = [];
        foreach ((array) $request->input('outcome_text', []) as $i => $text) {
            $icon = $request->input('outcome_icon', [])[$i] ?? '';
            if (trim($text)) $outcomes[] = ['icon' => $icon, 'text' => $text];
        }
        $data['career_outcomes'] = $outcomes ?: null;

        // Bonuses (single text per row)
        $bonuses = [];
        foreach ((array) $request->input('bonus_text', []) as $text) {
            if (trim($text)) $bonuses[] = ['text' => $text];
        }
        $data['bonuses'] = $bonuses ?: null;

        return $data;
    }

    private function buildFormFields(Request $request): array
    {
        return [
            'name'  => $request->boolean('field_name'),
            'email' => $request->boolean('field_email'),
            'phone' => $request->boolean('field_phone'),
            'city'  => $request->boolean('field_city'),
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 2;
        while (LandingPage::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}

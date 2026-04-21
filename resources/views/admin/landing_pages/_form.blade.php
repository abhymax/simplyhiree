{{-- Shared form partial used by both create and edit --}}

<div class="space-y-8">

    {{-- ── SECTION: Page Settings ──────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Page Settings
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="lp-label">Internal Title *</label>
                <input type="text" name="title" value="{{ old('title', $landingPage->title ?? '') }}" required class="lp-input" placeholder="e.g. Webinar Registration - April">
                @error('title')<p class="lp-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lp-label">Custom URL Slug</label>
                <div class="flex items-center">
                    <span class="px-3 py-2 bg-white/5 border border-white/20 rounded-l-lg text-blue-300 text-sm whitespace-nowrap">/l/</span>
                    <input type="text" name="slug" id="slug_input" value="{{ old('slug', $landingPage->slug ?? '') }}" class="lp-input rounded-l-none flex-1" placeholder="auto-generated-from-title">
                </div>
                @error('slug')<p class="lp-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="lp-label">Status *</label>
                <select name="status" class="lp-input">
                    <option value="draft" {{ old('status', $landingPage->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $landingPage->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            <div>
                <label class="lp-label">Logo (optional)</label>
                <input type="file" name="logo" accept="image/*" class="lp-file">
                @if(!empty($landingPage->logo_path))
                    <img src="{{ Storage::url($landingPage->logo_path) }}" class="mt-2 h-10 rounded">
                @endif
            </div>
            <div>
                <label class="lp-label">Primary Color</label>
                <div class="flex gap-2 items-center">
                    <input type="color" name="primary_color" value="{{ old('primary_color', $landingPage->primary_color ?? '#4f46e5') }}" class="h-10 w-16 rounded border border-white/20 bg-transparent cursor-pointer">
                    <input type="text" name="primary_color_hex" class="lp-input flex-1" value="{{ old('primary_color', $landingPage->primary_color ?? '#4f46e5') }}" placeholder="#4f46e5">
                </div>
            </div>
            <div>
                <label class="lp-label">Secondary Color</label>
                <div class="flex gap-2 items-center">
                    <input type="color" name="secondary_color" value="{{ old('secondary_color', $landingPage->secondary_color ?? '#2563eb') }}" class="h-10 w-16 rounded border border-white/20 bg-transparent cursor-pointer">
                    <input type="text" name="secondary_color_hex" class="lp-input flex-1" value="{{ old('secondary_color', $landingPage->secondary_color ?? '#2563eb') }}" placeholder="#2563eb">
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">Meta Title (SEO)</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $landingPage->meta_title ?? '') }}" class="lp-input" placeholder="Page title for search engines">
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">Meta Description (SEO)</label>
                <textarea name="meta_description" rows="2" class="lp-input" placeholder="Short description for search engines">{{ old('meta_description', $landingPage->meta_description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── SECTION: Hero ────────────────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Hero Section
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="lp-label">Main Headline *</label>
                <input type="text" name="hero_headline" value="{{ old('hero_headline', $landingPage->hero_headline ?? '') }}" required class="lp-input text-lg" placeholder="e.g. Join Our Free Webinar on Hiring Strategies">
                @error('hero_headline')<p class="lp-error">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">Sub-Headline</label>
                <textarea name="hero_subheadline" rows="2" class="lp-input" placeholder="Supporting text below the headline">{{ old('hero_subheadline', $landingPage->hero_subheadline ?? '') }}</textarea>
            </div>
            <div>
                <label class="lp-label">CTA Button Text</label>
                <input type="text" name="cta_text" value="{{ old('cta_text', $landingPage->cta_text ?? 'Reserve My FREE Slot!') }}" class="lp-input" placeholder="Reserve My FREE Slot!">
            </div>
            <div>
                <label class="lp-label">Hero Image</label>
                <input type="file" name="hero_image" accept="image/*" class="lp-file">
                @if(!empty($landingPage->hero_image_path))
                    <img src="{{ Storage::url($landingPage->hero_image_path) }}" class="mt-2 h-20 rounded object-cover">
                @endif
            </div>
        </div>
    </div>

    {{-- ── SECTION: Video ───────────────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Video Section (optional)
        </h2>
        <p class="text-xs text-blue-300 mb-5">Either paste a YouTube/Vimeo link <strong>or</strong> upload a video file. If both are provided, the uploaded file takes priority.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="lp-label">Section Heading</label>
                <input type="text" name="video_section_title" value="{{ old('video_section_title', $landingPage->video_section_title ?? '') }}" class="lp-input" placeholder="e.g. Watch a Quick Preview">
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">Section Description</label>
                <textarea name="video_section_description" rows="2" class="lp-input" placeholder="Short intro shown above the video">{{ old('video_section_description', $landingPage->video_section_description ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">YouTube / Vimeo URL</label>
                <input type="url" name="video_url" value="{{ old('video_url', $landingPage->video_url ?? '') }}" class="lp-input" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                <p class="text-xs text-blue-300/60 mt-1">Supports youtube.com, youtu.be, and vimeo.com links. They are automatically converted to embed format.</p>
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">OR Upload Video File (max 50MB)</label>
                <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="lp-file">
                @if(!empty($landingPage->video_file_path))
                    <div class="mt-3 p-3 bg-emerald-500/10 border border-emerald-400/30 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2 text-emerald-300 text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Video uploaded
                        </div>
                        <a href="{{ Storage::url($landingPage->video_file_path) }}" target="_blank" class="text-xs text-blue-300 hover:text-white underline">Preview</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── SECTION: Event Details ───────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Event Details
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="lp-label">Event Date</label>
                <input type="date" name="event_date" value="{{ old('event_date', isset($landingPage) && $landingPage->event_date ? $landingPage->event_date->format('Y-m-d') : '') }}" class="lp-input">
            </div>
            <div>
                <label class="lp-label">Event Time</label>
                <input type="text" name="event_time" value="{{ old('event_time', $landingPage->event_time ?? '') }}" class="lp-input" placeholder="e.g. 6:00 PM IST">
            </div>
            <div>
                <label class="lp-label">Platform</label>
                <input type="text" name="event_platform" value="{{ old('event_platform', $landingPage->event_platform ?? '') }}" class="lp-input" placeholder="e.g. Zoom, Google Meet">
            </div>
            <div>
                <label class="lp-label">Language</label>
                <input type="text" name="event_language" value="{{ old('event_language', $landingPage->event_language ?? '') }}" class="lp-input" placeholder="e.g. English, Hindi">
            </div>
            <div>
                <label class="lp-label">Total Seats (0 = unlimited)</label>
                <input type="number" name="seats_total" value="{{ old('seats_total', $landingPage->seats_total ?? 0) }}" min="0" class="lp-input">
            </div>
            <div>
                <label class="lp-label">Registration Deadline (for countdown)</label>
                <input type="datetime-local" name="registration_deadline"
                       value="{{ old('registration_deadline', isset($landingPage) && $landingPage->registration_deadline ? $landingPage->registration_deadline->format('Y-m-d\TH:i') : '') }}"
                       class="lp-input">
            </div>
        </div>
    </div>

    {{-- ── SECTION: About ───────────────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5">About the Event</h2>
        <div class="space-y-4">
            <div>
                <label class="lp-label">Section Heading</label>
                <input type="text" name="about_title" value="{{ old('about_title', $landingPage->about_title ?? '') }}" class="lp-input" placeholder="e.g. About This Webinar">
            </div>
            <div>
                <label class="lp-label">Description</label>
                <textarea name="about_description" rows="4" class="lp-input" placeholder="Describe what this event is about...">{{ old('about_description', $landingPage->about_description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── SECTION: What You'll Learn ────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">What You'll Learn</h2>
            <button type="button" onclick="addRow('learnings-list', learningRowTpl)" class="lp-add-btn">+ Add Point</button>
        </div>
        <div id="learnings-list" class="space-y-3">
            @php $learnings = old('learning_title') ? array_map(fn($t,$d)=>['title'=>$t,'description'=>$d], old('learning_title',[]), old('learning_description',[])) : ($landingPage->learnings ?? []) @endphp
            @forelse($learnings as $item)
            <div class="lp-repeater-row">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="learning_title[]" value="{{ $item['title'] ?? '' }}" class="lp-input" placeholder="Point title">
                    <input type="text" name="learning_description[]" value="{{ $item['description'] ?? '' }}" class="lp-input" placeholder="Short description">
                </div>
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @empty
            <div class="lp-repeater-row">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="learning_title[]" class="lp-input" placeholder="Point title">
                    <input type="text" name="learning_description[]" class="lp-input" placeholder="Short description">
                </div>
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── SECTION: Who Should Attend ──────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">Who Should Attend</h2>
            <button type="button" onclick="addRow('quals-list', qualRowTpl)" class="lp-add-btn">+ Add Point</button>
        </div>
        <div id="quals-list" class="space-y-3">
            @php $quals = old('qualification_text') ? array_map(fn($t)=>['text'=>$t], old('qualification_text',[])) : ($landingPage->qualifications ?? []) @endphp
            @forelse($quals as $item)
            <div class="lp-repeater-row">
                <input type="text" name="qualification_text[]" value="{{ $item['text'] ?? '' }}" class="lp-input flex-1" placeholder="e.g. HR professionals wanting to scale hiring">
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @empty
            <div class="lp-repeater-row">
                <input type="text" name="qualification_text[]" class="lp-input flex-1" placeholder="e.g. HR professionals wanting to scale hiring">
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── SECTION: Program Benefits ───────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">Program Benefits</h2>
            <button type="button" onclick="addRow('benefits-list', benefitRowTpl)" class="lp-add-btn">+ Add Benefit</button>
        </div>
        <div id="benefits-list" class="space-y-3">
            @php $benefits = old('benefit_title') ? array_map(fn($t,$d)=>['title'=>$t,'description'=>$d], old('benefit_title',[]), old('benefit_description',[])) : ($landingPage->benefits ?? []) @endphp
            @forelse($benefits as $item)
            <div class="lp-repeater-row">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="benefit_title[]" value="{{ $item['title'] ?? '' }}" class="lp-input" placeholder="Benefit title">
                    <input type="text" name="benefit_description[]" value="{{ $item['description'] ?? '' }}" class="lp-input" placeholder="Brief description">
                </div>
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @empty
            <div class="lp-repeater-row">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="benefit_title[]" class="lp-input" placeholder="Benefit title">
                    <input type="text" name="benefit_description[]" class="lp-input" placeholder="Brief description">
                </div>
                <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── SECTION: Host / Presenter ────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5">Host / Presenter</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="lp-label">Name</label>
                <input type="text" name="host_name" value="{{ old('host_name', $landingPage->host_name ?? '') }}" class="lp-input" placeholder="Host's full name">
            </div>
            <div>
                <label class="lp-label">Title / Designation</label>
                <input type="text" name="host_title" value="{{ old('host_title', $landingPage->host_title ?? '') }}" class="lp-input" placeholder="e.g. CEO, Hiring Expert">
            </div>
            <div class="md:col-span-2">
                <label class="lp-label">Bio</label>
                <textarea name="host_bio" rows="4" class="lp-input" placeholder="Short bio of the presenter...">{{ old('host_bio', $landingPage->host_bio ?? '') }}</textarea>
            </div>
            <div>
                <label class="lp-label">Photo</label>
                <input type="file" name="host_photo" accept="image/*" class="lp-file">
                @if(!empty($landingPage->host_photo_path))
                    <img src="{{ Storage::url($landingPage->host_photo_path) }}" class="mt-2 h-16 w-16 rounded-full object-cover">
                @endif
            </div>
        </div>
    </div>

    {{-- ── SECTION: FAQs ────────────────────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-white">FAQs</h2>
            <button type="button" onclick="addRow('faqs-list', faqRowTpl)" class="lp-add-btn">+ Add FAQ</button>
        </div>
        <div id="faqs-list" class="space-y-3">
            @php $faqs = old('faq_question') ? array_map(fn($q,$a)=>['question'=>$q,'answer'=>$a], old('faq_question',[]), old('faq_answer',[])) : ($landingPage->faqs ?? []) @endphp
            @forelse($faqs as $item)
            <div class="lp-repeater-row flex-col !items-start gap-2">
                <div class="flex w-full items-start gap-2">
                    <input type="text" name="faq_question[]" value="{{ $item['question'] ?? '' }}" class="lp-input flex-1" placeholder="Question">
                    <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn mt-0">✕</button>
                </div>
                <textarea name="faq_answer[]" rows="2" class="lp-input w-full" placeholder="Answer">{{ $item['answer'] ?? '' }}</textarea>
            </div>
            @empty
            <div class="lp-repeater-row flex-col !items-start gap-2">
                <div class="flex w-full items-start gap-2">
                    <input type="text" name="faq_question[]" class="lp-input flex-1" placeholder="Question">
                    <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn mt-0">✕</button>
                </div>
                <textarea name="faq_answer[]" rows="2" class="lp-input w-full" placeholder="Answer"></textarea>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── SECTION: Registration Form ───────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5">Registration Form Fields</h2>
        @php
            $ff = $landingPage->form_fields ?? ['name'=>true,'email'=>true,'phone'=>true,'city'=>false];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['name'=>'Name','email'=>'Email','phone'=>'Phone','city'=>'City'] as $key => $label)
            <label class="flex items-center gap-3 p-3 bg-white/5 border border-white/20 rounded-xl cursor-pointer hover:bg-white/10 transition">
                <input type="checkbox" name="field_{{ $key }}" value="1" {{ old('field_'.$key, ($ff[$key] ?? false) ? '1' : '') ? 'checked' : '' }} class="w-4 h-4 accent-indigo-500">
                <span class="text-white text-sm font-medium">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ── SECTION: Footer Disclaimer ──────────────────────────────────────── --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-5">Footer Disclaimer</h2>
        <textarea name="footer_disclaimer" rows="3" class="lp-input" placeholder="Legal disclaimer or footer text...">{{ old('footer_disclaimer', $landingPage->footer_disclaimer ?? '') }}</textarea>
    </div>

</div>

{{-- JS for dynamic rows --}}
<script>
const learningRowTpl = `
<div class="lp-repeater-row">
    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="text" name="learning_title[]" class="lp-input" placeholder="Point title">
        <input type="text" name="learning_description[]" class="lp-input" placeholder="Short description">
    </div>
    <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
</div>`;

const qualRowTpl = `
<div class="lp-repeater-row">
    <input type="text" name="qualification_text[]" class="lp-input flex-1" placeholder="e.g. HR professionals wanting to scale hiring">
    <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
</div>`;

const benefitRowTpl = `
<div class="lp-repeater-row">
    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
        <input type="text" name="benefit_title[]" class="lp-input" placeholder="Benefit title">
        <input type="text" name="benefit_description[]" class="lp-input" placeholder="Brief description">
    </div>
    <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn">✕</button>
</div>`;

const faqRowTpl = `
<div class="lp-repeater-row flex-col !items-start gap-2">
    <div class="flex w-full items-start gap-2">
        <input type="text" name="faq_question[]" class="lp-input flex-1" placeholder="Question">
        <button type="button" onclick="this.closest('.lp-repeater-row').remove()" class="lp-remove-btn mt-0">✕</button>
    </div>
    <textarea name="faq_answer[]" rows="2" class="lp-input w-full" placeholder="Answer"></textarea>
</div>`;

function addRow(containerId, tpl) {
    const el = document.createElement('div');
    el.innerHTML = tpl.trim();
    document.getElementById(containerId).appendChild(el.firstChild);
}

// Auto-slug from title
const titleInput = document.querySelector('input[name="title"]');
const slugInput  = document.getElementById('slug_input');
if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
        if (!slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });
    slugInput.addEventListener('input', function () {
        this.dataset.manual = '1';
    });
}
</script>

<style>
.lp-label  { @apply block text-sm font-medium text-blue-200 mb-1; }
.lp-input  { @apply w-full px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm; }
.lp-file   { @apply block w-full text-sm text-blue-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:cursor-pointer file:text-sm hover:file:bg-indigo-500; }
.lp-error  { @apply mt-1 text-xs text-rose-400; }
.lp-add-btn { @apply px-3 py-1.5 bg-indigo-600/70 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition; }
.lp-remove-btn { @apply flex-shrink-0 mt-2 w-8 h-8 flex items-center justify-center bg-rose-600/50 hover:bg-rose-600 text-white rounded-lg text-xs transition; }
.lp-repeater-row { @apply flex items-center gap-3 p-3 bg-white/5 border border-white/10 rounded-xl; }
</style>

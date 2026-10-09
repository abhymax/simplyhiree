{{--
    Shared email logo. Pulls the same image used on the offer-letter
    letterhead, so branding is changed in one place (Offer Letters →
    Branding) and every email follows.

    An <img> is used rather than styled text because Outlook ignores much of
    a <style> block, which previously left the wordmark invisible.
--}}
@php
    $emailLogoPath = \App\Models\OfferLetterSetting::current()->logo_path ?? null;
    $emailLogoUrl = $emailLogoPath
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($emailLogoPath)
            ? rtrim(config('app.url'), '/') . '/storage/' . ltrim($emailLogoPath, '/')
            : null;
@endphp
@if($emailLogoUrl)
    <img src="{{ $emailLogoUrl }}" alt="SimplyHiree" width="134" height="50"
         style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;height:auto;max-width:134px;">
@else
    {{-- Fallback if no logo has been uploaded: dark text, legible on the light header --}}
    <div style="font-size:28px;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">Simply<span style="color:#0ea5e9;">Hiree</span></div>
@endif

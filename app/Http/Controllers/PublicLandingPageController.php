<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingPageRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PublicLandingPageController extends Controller
{
    public function show(string $slug)
    {
        $page = LandingPage::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $seatsLeft = $page->seats_left;
        return view('public.landing_page', compact('page', 'seatsLeft'));
    }

    public function register(Request $request, string $slug)
    {
        $page = LandingPage::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $fields = $page->form_fields ?? ['name' => true, 'email' => true, 'phone' => true, 'city' => false];
        $rules = [];
        if (!empty($fields['name']))  $rules['name']  = 'required|string|max:255';
        if (!empty($fields['email'])) $rules['email'] = 'required|email|max:255';
        if (!empty($fields['phone'])) $rules['phone'] = 'required|string|max:20';
        if (!empty($fields['city']))  $rules['city']  = 'required|string|max:100';

        $validated = $request->validate($rules);

        // Check seats
        if ($page->seats_total > 0 && $page->seats_left <= 0) {
            return back()->with('error', 'Sorry, all seats are filled. Registration is closed.');
        }

        $registration = LandingPageRegistration::create([
            'landing_page_id' => $page->id,
            'name'            => $validated['name'] ?? null,
            'email'           => $validated['email'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'city'            => $validated['city'] ?? null,
            'ip_address'      => $request->ip(),
        ]);

        // Notify configured admins (comma-separated emails). Failures are
        // swallowed so the user still gets their redirect / success state.
        $this->notifyAdmins($page, $registration);

        // If a redirect URL is set (e.g. a payment link), send the user there.
        if (!empty($page->redirect_url)) {
            return redirect()->away($page->redirect_url);
        }

        return back()->with('registered', true);
    }

    private function notifyAdmins(LandingPage $page, LandingPageRegistration $reg): void
    {
        $recipients = collect(explode(',', (string) $page->notify_emails))
            ->map(fn ($e) => trim($e))
            ->filter(fn ($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if (empty($recipients)) {
            return;
        }

        $subject = '[' . config('app.name', 'SimplyHiree') . '] New Registration: ' . ($page->title ?: $page->slug);
        $lines = [
            'A new registration has been submitted on the landing page "' . ($page->title ?: $page->slug) . '".',
            '',
            'Name      : ' . ($reg->name ?? '—'),
            'Email     : ' . ($reg->email ?? '—'),
            'Phone     : ' . ($reg->phone ?? '—'),
            'City      : ' . ($reg->city ?? '—'),
            'IP        : ' . ($reg->ip_address ?? '—'),
            'Submitted : ' . $reg->created_at->format('d M Y, H:i') . ' IST',
            '',
            'Page URL  : ' . url('/l/' . $page->slug),
            'Admin URL : ' . url('/admin/landing-pages/' . $page->id),
        ];
        $body = implode("\n", $lines);

        try {
            Mail::raw($body, function ($m) use ($recipients, $subject, $reg) {
                $m->to($recipients)->subject($subject);
                if ($reg->email) {
                    $m->replyTo($reg->email, $reg->name ?: null);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Landing page registration mail failed: ' . $e->getMessage(), [
                'page_id' => $reg->landing_page_id,
                'reg_id'  => $reg->id,
            ]);
        }
    }
}

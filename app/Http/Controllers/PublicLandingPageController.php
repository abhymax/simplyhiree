<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\LandingPageRegistration;
use Illuminate\Http\Request;

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

        LandingPageRegistration::create([
            'landing_page_id' => $page->id,
            'name'            => $validated['name'] ?? null,
            'email'           => $validated['email'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'city'            => $validated['city'] ?? null,
            'ip_address'      => $request->ip(),
        ]);

        return back()->with('registered', true);
    }
}

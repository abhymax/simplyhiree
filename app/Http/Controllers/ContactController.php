<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $contactRecipient = (string) config('mail.contact_recipient', config('mail.from.address'));

        if ($contactRecipient === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'Contact email is not configured yet. Please try again shortly.',
                ]);
        }

        try {
            Mail::to($contactRecipient)->send(
                new ContactFormSubmitted(
                    firstName: $validated['first_name'],
                    lastName: $validated['last_name'] ?? '',
                    email: $validated['email'],
                    messageBody: $validated['message'],
                )
            );
        } catch (\Throwable $e) {
            Log::error('Contact form email failed', [
                'email' => $validated['email'],
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'We could not send your message right now. Please try again in a few minutes.',
                ]);
        }

        return redirect()
            ->route('contact')
            ->with('success', 'Your message has been sent successfully. Our team will get back to you shortly.');
    }
}

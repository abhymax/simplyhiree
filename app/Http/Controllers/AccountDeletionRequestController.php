<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountDeletionRequestController extends Controller
{
    public function create(): View
    {
        return view('pages.account-deletion');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'account_type' => ['required', 'in:candidate,client,partner,referral_partner,other'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'confirmation' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        $deletionRequest = AccountDeletionRequest::create([
            'reference' => $this->makeReference(),
            'user_id' => $user?->id,
            'name' => trim($validated['name']),
            'email' => $email,
            'account_type' => $validated['account_type'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending_verification',
            'requested_at' => now(),
        ]);

        try {
            $recipients = collect([
                'simplyhiree1@gmail.com',
                env('SUPPORT_EMAIL'),
            ])->filter(fn ($address) => filter_var($address, FILTER_VALIDATE_EMAIL))
                ->unique()
                ->values()
                ->all();

            Mail::send('emails.account-deletion-request', [
                'deletionRequest' => $deletionRequest,
                'accountFound' => $user !== null,
            ], function ($message) use ($deletionRequest, $recipients) {
                $message->to($recipients)
                    ->subject('Account deletion request '.$deletionRequest->reference);
            });
        } catch (\Throwable $exception) {
            Log::error('Account deletion request notification failed.', [
                'request_id' => $deletionRequest->id,
                'reference' => $deletionRequest->reference,
                'exception' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('account-deletion.create')
            ->with('deletion_request_reference', $deletionRequest->reference);
    }

    private function makeReference(): string
    {
        do {
            $reference = 'SH-DEL-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (AccountDeletionRequest::where('reference', $reference)->exists());

        return $reference;
    }
}

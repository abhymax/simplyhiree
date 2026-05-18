<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function show()
    {
        return view('support.create');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'subject'      => 'required|string|max:200',
            'message'      => 'required|string|max:5000',
            'attachments'  => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:png,jpg,jpeg,gif,webp,pdf|max:5120',
        ]);

        $user = Auth::user();

        $files = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $files[] = [
                    'path' => $file->getRealPath(),
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                ];
            }
        }

        $to = config('mail.from.address', 'simplyhiree1@gmail.com');

        $data = [
            'sender'         => $user,
            'supportSubject' => $validated['subject'],
            'body'           => $validated['message'],
        ];

        try {
            Mail::send('support.email', $data, function ($message) use ($to, $user, $validated, $files) {
                $message->to($to)
                    ->replyTo($user->email, $user->name)
                    ->subject('[Support] ' . $validated['subject']);
                foreach ($files as $f) {
                    $message->attach($f['path'], [
                        'as'   => $f['name'],
                        'mime' => $f['mime'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Could not send your support request right now. Please try again, or email us directly at ' . $to . '.');
        }

        return back()->with('success', 'Your support request has been sent. Our team will get back to you on ' . $user->email . '.');
    }
}

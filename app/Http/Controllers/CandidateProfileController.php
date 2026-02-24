<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\UserProfile;

class CandidateProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        // Ensure profile exists or return empty model
        $profile = $user->profile ?? new UserProfile();

        return view('candidate.profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $expectedCtc = $request->input('expected_ctc');
        if (is_string($expectedCtc)) {
            $request->merge([
                'expected_ctc' => trim(str_replace(',', '', $expectedCtc)),
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'location' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'experience_status' => 'required|in:Fresher,Experienced',
            'current_role' => 'nullable|string|max:255',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:100',
            'skills' => 'required|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->save();

            // Handle Resume Upload
            $resumePath = $user->profile?->resume_path;
            if ($request->hasFile('resume')) {
                // Delete old resume if exists
                if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                    Storage::disk('public')->delete($resumePath);
                }
                $resumePath = $request->file('resume')->store('resumes', 'public');
            }

            // Update or Create Profile
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone_number' => $validated['phone_number'],
                    'location' => $validated['location'],
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'experience_status' => $validated['experience_status'],
                    'current_role' => $validated['current_role'] ?? null,
                    'expected_ctc' => $validated['expected_ctc'] ?? null,
                    'notice_period' => $validated['notice_period'] ?? null,
                    'skills' => $validated['skills'],
                    'resume_path' => $resumePath,
                ]
            );
        });

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}

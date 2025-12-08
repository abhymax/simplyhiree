<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'location' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'experience_status' => 'required|in:Fresher,Experienced',
            'current_role' => 'nullable|string|max:255',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:100',
            'skills' => 'required|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // Max 2MB
        ]);

        // Handle Resume Upload
        $resumePath = $user->profile->resume_path ?? null;
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
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'experience_status' => $validated['experience_status'],
                'current_role' => $validated['current_role'],
                'expected_ctc' => $validated['expected_ctc'],
                'notice_period' => $validated['notice_period'],
                'skills' => $validated['skills'],
                'resume_path' => $resumePath,
            ]
        );

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
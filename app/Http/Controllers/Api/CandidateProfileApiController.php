<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidateProfileApiController extends Controller
{
    public function show(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $profile = $candidate->profile;

        $resumeUrl = null;
        if ($profile?->resume_path) {
            $resumeUrl = url(Storage::disk('public')->url($profile->resume_path));
        }

        return response()->json([
            'data' => [
                'name' => $candidate->name,
                'email' => $candidate->email,
                'phone_number' => $profile?->phone_number,
                'location' => $profile?->location,
                'date_of_birth' => optional($profile?->date_of_birth)->toDateString(),
                'gender' => $profile?->gender,
                'experience_status' => $profile?->experience_status,
                'current_role' => $profile?->current_role,
                'expected_ctc' => $profile?->expected_ctc,
                'notice_period' => $profile?->notice_period,
                'skills' => $profile?->skills,
                'resume_path' => $profile?->resume_path,
                'resume_url' => $resumeUrl,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'location' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'experience_status' => ['required', 'in:Fresher,Experienced'],
            'current_role' => ['nullable', 'string', 'max:255'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'notice_period' => ['nullable', 'string', 'max:100'],
            'skills' => ['required', 'string', 'max:3000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $profile = UserProfile::query()->firstOrNew(['user_id' => $candidate->id]);

        if ($request->hasFile('resume')) {
            if ($profile->resume_path && Storage::disk('public')->exists($profile->resume_path)) {
                Storage::disk('public')->delete($profile->resume_path);
            }

            $profile->resume_path = $request->file('resume')->store('resumes', 'public');
        }

        $profile->phone_number = $validated['phone_number'];
        $profile->location = $validated['location'];
        $profile->date_of_birth = $validated['date_of_birth'] ?? null;
        $profile->gender = $validated['gender'] ?? null;
        $profile->experience_status = $validated['experience_status'];
        $profile->current_role = $validated['current_role'] ?? null;
        $profile->expected_ctc = $validated['expected_ctc'] ?? null;
        $profile->notice_period = $validated['notice_period'] ?? null;
        $profile->skills = $validated['skills'];
        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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

        $isProfileComplete = $profile
            && !empty($candidate->name)
            && !empty($candidate->email)
            && !empty($profile->phone_number)
            && !empty($profile->location)
            && !empty($profile->date_of_birth)
            && !empty($profile->gender)
            && !empty($profile->experience_status)
            && !empty($profile->skills);

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
            'profile_complete' => $isProfileComplete,
        ]);
    }

    public function update(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $expectedCtc = $request->input('expected_ctc');
        if (is_string($expectedCtc)) {
            $request->merge([
                'expected_ctc' => trim(str_replace(',', '', $expectedCtc)),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($candidate->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'location' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'experience_status' => ['required', 'in:Fresher,Experienced'],
            'current_role' => ['nullable', 'string', 'max:255'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'notice_period' => ['nullable', 'string', 'max:100'],
            'skills' => ['required', 'string', 'max:3000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if (!preg_match('/^[6-9][0-9]{9}$/', preg_replace('/\D+/', '', $validated['phone_number']))) {
            throw ValidationException::withMessages([
                'phone_number' => 'Enter a valid 10-digit Indian mobile number.',
            ]);
        }

        $profile = DB::transaction(function () use ($candidate, $request, $validated) {
            $candidate->name = $validated['name'];
            $candidate->email = $validated['email'];
            $candidate->save();

            $profile = UserProfile::query()->firstOrNew(['user_id' => $candidate->id]);

            if ($request->hasFile('resume')) {
                if ($profile->resume_path && Storage::disk('public')->exists($profile->resume_path)) {
                    Storage::disk('public')->delete($profile->resume_path);
                }

                $profile->resume_path = $request->file('resume')->store('resumes', 'public');
            }

            $profile->phone_number = $validated['phone_number'];
            $profile->location = $validated['location'];
            $profile->date_of_birth = $validated['date_of_birth'];
            $profile->gender = $validated['gender'];
            $profile->experience_status = $validated['experience_status'];
            $profile->current_role = $validated['current_role'] ?? null;
            $profile->expected_ctc = $validated['expected_ctc'] ?? null;
            $profile->notice_period = $validated['notice_period'] ?? null;
            $profile->skills = $validated['skills'];
            $profile->save();

            return $profile;
        });

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile_complete' => true,
            'data' => [
                'name' => $candidate->name,
                'email' => $candidate->email,
                'phone_number' => $profile->phone_number,
                'location' => $profile->location,
                'experience_status' => $profile->experience_status,
                'skills' => $profile->skills,
                'resume_path' => $profile->resume_path,
                'resume_url' => $profile->resume_path ? url(Storage::disk('public')->url($profile->resume_path)) : null,
            ],
        ]);
    }
}

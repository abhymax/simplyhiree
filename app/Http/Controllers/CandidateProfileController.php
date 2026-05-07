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
            'preferred_locations' => 'required|string|max:500',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'marital_status' => 'required|string|max:30',
            'qualification_degree' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'experience_status' => 'required|in:Fresher,Experienced',
            'total_experience_years' => 'required|integer|min:0|max:60',
            'total_experience_months' => 'required|integer|min:0|max:11',
            'current_company' => 'required|string|max:255',
            'current_role' => 'required|string|max:255',
            'current_ctc' => 'required|string|max:100',
            'expected_ctc' => 'required|string|max:100',
            'notice_period' => 'required|string|max:100',
            'skills' => 'required|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $preferred = array_values(array_filter(array_map('trim', explode(',', $validated['preferred_locations']))));

        DB::transaction(function () use ($request, $user, $validated, $preferred) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->save();

            $resumePath = $user->profile?->resume_path;
            if ($request->hasFile('resume')) {
                if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                    Storage::disk('public')->delete($resumePath);
                }
                $resumePath = $request->file('resume')->store('resumes', 'public');
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone_number'            => $validated['phone_number'],
                    'location'                => $validated['location'],
                    'preferred_locations'     => $preferred,
                    'date_of_birth'           => $validated['date_of_birth'],
                    'gender'                  => $validated['gender'],
                    'marital_status'          => $validated['marital_status'],
                    'qualification_degree'    => $validated['qualification_degree'],
                    'specialization'          => $validated['specialization'],
                    'experience_status'       => $validated['experience_status'],
                    'total_experience_years'  => $validated['total_experience_years'],
                    'total_experience_months' => $validated['total_experience_months'],
                    'current_company'         => $validated['current_company'],
                    'current_role'            => $validated['current_role'],
                    'current_ctc'             => $validated['current_ctc'],
                    'expected_ctc'            => $validated['expected_ctc'],
                    'notice_period'           => $validated['notice_period'],
                    'skills'                  => $validated['skills'],
                    'resume_path'             => $resumePath,
                ]
            );
        });

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PartnerProfile;

class PartnerProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->partnerProfile ?? new PartnerProfile();
        return view('partner.profile.edit', compact('user', 'profile'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Basic & Social
            'company_type' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'establishment_year' => 'nullable|integer|min:1900|max:'.(date('Y')),
            'profile_picture' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',

            // Preferences & Bio
            'preferred_categories' => 'nullable|string',
            'preferred_locations' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'working_hours' => 'nullable|string|max:100',

            // Financials
            'beneficiary_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|confirmed',
            'account_type' => 'required|string|in:Savings,Current',
            'ifsc_code' => 'required|string|max:20',
            'cancelled_cheque' => 'nullable|image|mimes:jpg,png,jpeg|max:2560',
            'pan_name' => 'required|string|max:255',
            'pan_number' => 'required|string|max:20',
            'pan_card' => 'nullable|image|mimes:jpg,png,jpeg|max:2560',
            'gst_number' => 'required|string|max:50',
            'gst_certificate' => 'nullable|image|mimes:jpg,png,jpeg|max:2560',
        ]);

        $handleUpload = function ($fileInput, $existingPath, $folder) use ($request) {
            if ($request->hasFile($fileInput)) {
                if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
                }
                return $request->file($fileInput)->store($folder, 'public');
            }
            return $existingPath;
        };

        $currentProfile = $user->partnerProfile;

        $profilePicPath = $handleUpload('profile_picture', $currentProfile->profile_picture_path ?? null, 'partner_docs/avatars');
        $chequePath = $handleUpload('cancelled_cheque', $currentProfile->cancelled_cheque_path ?? null, 'partner_docs/cheques');
        $panPath = $handleUpload('pan_card', $currentProfile->pan_card_path ?? null, 'partner_docs/pan');
        $gstPath = $handleUpload('gst_certificate', $currentProfile->gst_certificate_path ?? null, 'partner_docs/gst');

        PartnerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'profile_picture_path' => $profilePicPath,
                'company_type' => $validated['company_type'],
                'website' => $validated['website'],
                'linkedin_url' => $validated['linkedin_url'],
                'facebook_url' => $validated['facebook_url'],
                'twitter_url' => $validated['twitter_url'],
                'instagram_url' => $validated['instagram_url'],
                'establishment_year' => $validated['establishment_year'],
                
                'preferred_categories' => $validated['preferred_categories'],
                'preferred_locations' => $validated['preferred_locations'],
                'bio' => $validated['bio'],
                'address' => $validated['address'],
                'working_hours' => $validated['working_hours'],

                'beneficiary_name' => $validated['beneficiary_name'],
                'account_number' => $validated['account_number'],
                'account_type' => $validated['account_type'],
                'ifsc_code' => $validated['ifsc_code'],
                'cancelled_cheque_path' => $chequePath,
                'pan_name' => $validated['pan_name'],
                'pan_number' => $validated['pan_number'],
                'pan_card_path' => $panPath,
                'gst_number' => $validated['gst_number'],
                'gst_certificate_path' => $gstPath,
            ]
        );

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
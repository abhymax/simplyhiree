<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use Illuminate\Http\Request;

class PartnerProfileController extends Controller
{
    public function show(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $profile = $partner->partnerProfile;

        return response()->json([
            'data' => [
                'name' => $partner->name,
                'email' => $partner->email,
                'company_type' => $profile ? $profile->company_type : null,
                'website' => $profile ? $profile->website : null,
                'linkedin_url' => $profile ? $profile->linkedin_url : null,
                'facebook_url' => $profile ? $profile->facebook_url : null,
                'twitter_url' => $profile ? $profile->twitter_url : null,
                'instagram_url' => $profile ? $profile->instagram_url : null,
                'establishment_year' => $profile ? $profile->establishment_year : null,
                'preferred_categories' => $profile ? $profile->preferred_categories : null,
                'preferred_locations' => $profile ? $profile->preferred_locations : null,
                'bio' => $profile ? $profile->bio : null,
                'address' => $profile ? $profile->address : null,
                'working_hours' => $profile ? $profile->working_hours : null,
                'beneficiary_name' => $profile ? $profile->beneficiary_name : null,
                'account_number' => $profile ? $profile->account_number : null,
                'account_type' => $profile ? $profile->account_type : null,
                'ifsc_code' => $profile ? $profile->ifsc_code : null,
                'pan_name' => $profile ? $profile->pan_name : null,
                'pan_number' => $profile ? $profile->pan_number : null,
                'gst_number' => $profile ? $profile->gst_number : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $validated = $request->validate([
            'company_type' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'establishment_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'preferred_categories' => ['nullable', 'string'],
            'preferred_locations' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'working_hours' => ['nullable', 'string', 'max:100'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_type' => ['nullable', 'string', 'in:Savings,Current'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'pan_name' => ['nullable', 'string', 'max:255'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:50'],
        ]);

        PartnerProfile::updateOrCreate(
            ['user_id' => $partner->id],
            $validated
        );

        return response()->json(['message' => 'Profile updated successfully.']);
    }
}

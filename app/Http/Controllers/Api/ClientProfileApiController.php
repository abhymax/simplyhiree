<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use Illuminate\Http\Request;

class ClientProfileApiController extends Controller
{
    public function show(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $profile = $client->clientProfile;

        return response()->json([
            'data' => [
                'name' => $client->name,
                'email' => $client->email,
                'company_name' => $profile?->company_name ?? $client->name,
                'website' => $profile?->website,
                'industry' => $profile?->industry,
                'company_size' => $profile?->company_size,
                'description' => $profile?->description,
                'contact_person_name' => $profile?->contact_person_name,
                'contact_phone' => $profile?->contact_phone,
                'gst_number' => $profile?->gst_number,
                'address' => $profile?->address,
                'city' => $profile?->city,
                'state' => $profile?->state,
                'pincode' => $profile?->pincode,
                'pan_number' => $profile?->pan_number,
                'tan_number' => $profile?->tan_number,
                'coi_number' => $profile?->coi_number,
                'logo_path' => $profile?->logo_path,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'tan_number' => ['nullable', 'string', 'max:20'],
            'coi_number' => ['nullable', 'string', 'max:50'],
        ]);

        $profileData = $validated;
        unset($profileData['company_name']);

        ClientProfile::query()->updateOrCreate(
            ['user_id' => $client->id],
            array_merge($profileData, ['company_name' => $validated['company_name']])
        );

        $client->update(['name' => $validated['company_name']]);

        return response()->json(['message' => 'Company profile updated successfully.']);
    }
}


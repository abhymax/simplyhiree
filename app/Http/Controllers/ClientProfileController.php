<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ClientProfile;

class ClientProfileController extends Controller
{
    /**
     * Show the client profile form.
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->clientProfile ?? new ClientProfile(['user_id' => $user->id]);

        return view('client.profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the client profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'company_size' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'contact_person_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle File Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($user->clientProfile && $user->clientProfile->logo_path) {
                if (Storage::disk('public')->exists($user->clientProfile->logo_path)) {
                    Storage::disk('public')->delete($user->clientProfile->logo_path);
                }
            }
            $path = $request->file('logo')->store('company_logos', 'public');
            $validated['logo_path'] = $path;
        }

        // Update or Create
        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        // Optional: Update Main User Name to match Company Name if desired
        $user->update(['name' => $validated['company_name']]);

        return redirect()->back()->with('success', 'Company profile updated successfully.');
    }
}
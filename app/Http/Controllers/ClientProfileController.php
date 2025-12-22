<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ClientProfile;
use Illuminate\Validation\Rule; // Import Rule

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
        // Load existing profile to check for existing files
        $profile = $user->clientProfile; 

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
            
            // --- NEW VALIDATIONS ---
            'pan_number' => 'required|string|max:20', // Mandatory
            'pan_file' => [
                // Mandatory only if not already uploaded
                function ($attribute, $value, $fail) use ($profile) {
                    if (empty($profile->pan_file_path) && empty($value)) {
                        $fail('The PAN document is required.');
                    }
                },
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'
            ],
            
            'tan_number' => 'nullable|string|max:20',
            'tan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
            'coi_number' => 'nullable|string|max:50',
            'coi_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Helper function to handle uploads
        $handleUpload = function ($fileInputName, $dbColumnName, $folder) use ($request, $user, &$validated) {
            if ($request->hasFile($fileInputName)) {
                // Delete old file
                if ($user->clientProfile && $user->clientProfile->$dbColumnName) {
                    if (Storage::disk('public')->exists($user->clientProfile->$dbColumnName)) {
                        Storage::disk('public')->delete($user->clientProfile->$dbColumnName);
                    }
                }
                // Store new
                $path = $request->file($fileInputName)->store($folder, 'public');
                $validated[$dbColumnName] = $path;
            }
        };

        // Process Uploads
        $handleUpload('logo', 'logo_path', 'company_logos');
        $handleUpload('pan_file', 'pan_file_path', 'compliance_docs');
        $handleUpload('tan_file', 'tan_file_path', 'compliance_docs');
        $handleUpload('coi_file', 'coi_file_path', 'compliance_docs');

        // Update or Create
        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        // Optional: Update Main User Name
        $user->update(['name' => $validated['company_name']]);

        return redirect()->back()->with('success', 'Company profile and compliance details updated successfully.');
    }
}
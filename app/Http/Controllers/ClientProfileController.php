<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ClientProfile;
use Illuminate\Validation\Rule;

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
        $profile = $user->clientProfile; // Get existing profile for reference

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
            
            // Compliance Validations
            'pan_number' => 'required|string|max:20',
            'pan_file' => [
                function ($attribute, $value, $fail) use ($profile) {
                    if ((!$profile || empty($profile->pan_file_path)) && empty($value)) {
                        $fail('The PAN document is required.');
                    }
                },
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'
            ],
            'tan_number' => 'nullable|string|max:20',
            'tan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'coi_number' => 'nullable|string|max:50',
            'coi_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            // NEW: Other Documents Validation
            'other_docs' => 'nullable|array',
            'other_docs.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max per file
        ]);

        // --- Handle Other Documents (Append Logic) ---
        $currentDocs = $profile ? ($profile->other_docs ?? []) : [];
        $newDocsCount = $request->hasFile('other_docs') ? count($request->file('other_docs')) : 0;

        if (count($currentDocs) + $newDocsCount > 10) {
            return back()->withErrors(['other_docs' => 'You cannot upload more than 10 documents in total. You currently have ' . count($currentDocs) . ' uploaded.']);
        }

        if ($request->hasFile('other_docs')) {
            foreach ($request->file('other_docs') as $file) {
                $path = $file->store('client_other_docs', 'public');
                $currentDocs[] = $path; // Append new path to array
            }
        }
        $validated['other_docs'] = $currentDocs; // Save the updated list

        // --- Handle Standard File Uploads ---
        $handleUpload = function ($fileInputName, $dbColumnName, $folder) use ($request, $user, &$validated) {
            if ($request->hasFile($fileInputName)) {
                if ($user->clientProfile && $user->clientProfile->$dbColumnName) {
                    if (Storage::disk('public')->exists($user->clientProfile->$dbColumnName)) {
                        Storage::disk('public')->delete($user->clientProfile->$dbColumnName);
                    }
                }
                $path = $request->file($fileInputName)->store($folder, 'public');
                $validated[$dbColumnName] = $path;
            }
        };

        $handleUpload('logo', 'logo_path', 'company_logos');
        $handleUpload('pan_file', 'pan_file_path', 'compliance_docs');
        $handleUpload('tan_file', 'tan_file_path', 'compliance_docs');
        $handleUpload('coi_file', 'coi_file_path', 'compliance_docs');

        // Update or Create
        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $user->update(['name' => $validated['company_name']]);

        return redirect()->back()->with('success', 'Company profile updated successfully.');
    }
}
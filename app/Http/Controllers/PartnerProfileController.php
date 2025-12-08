<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PartnerProfile;

class PartnerProfileController extends Controller
{
    /**
     * Show the partner profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->partnerProfile ?? new PartnerProfile();

        return view('partner.profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the partner profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Bank Details
            'beneficiary_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|confirmed', // Checks matching 'account_number_confirmation'
            'account_type' => 'required|string|in:Savings,Current',
            'ifsc_code' => 'required|string|max:20',
            'cancelled_cheque' => 'nullable|image|mimes:jpg,png,jpeg|max:2560', // Max 2.5MB
            
            // PAN Details
            'pan_name' => 'required|string|max:255',
            'pan_number' => 'required|string|max:20',
            'pan_card' => 'nullable|image|mimes:jpg,png,jpeg|max:2560',

            // GST Details
            'gst_number' => 'required|string|max:50',
            'gst_certificate' => 'nullable|image|mimes:jpg,png,jpeg|max:2560',
        ]);

        // Helper function to handle uploads
        $handleUpload = function ($fileInput, $existingPath, $folder) use ($request, $user) {
            if ($request->hasFile($fileInput)) {
                if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                    Storage::disk('public')->delete($existingPath);
                }
                return $request->file($fileInput)->store($folder, 'public');
            }
            return $existingPath;
        };

        // Get current profile data to check for existing files
        $currentProfile = $user->partnerProfile;

        $chequePath = $handleUpload('cancelled_cheque', $currentProfile->cancelled_cheque_path ?? null, 'partner_docs/cheques');
        $panPath = $handleUpload('pan_card', $currentProfile->pan_card_path ?? null, 'partner_docs/pan');
        $gstPath = $handleUpload('gst_certificate', $currentProfile->gst_certificate_path ?? null, 'partner_docs/gst');

        // Update or Create
        PartnerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                // Bank
                'beneficiary_name' => $validated['beneficiary_name'],
                'account_number' => $validated['account_number'],
                'account_type' => $validated['account_type'],
                'ifsc_code' => $validated['ifsc_code'],
                'cancelled_cheque_path' => $chequePath,
                
                // PAN
                'pan_name' => $validated['pan_name'],
                'pan_number' => $validated['pan_number'],
                'pan_card_path' => $panPath,

                // GST
                'gst_number' => $validated['gst_number'],
                'gst_certificate_path' => $gstPath,
            ]
        );

        return redirect()->back()->with('success', 'Account details updated successfully!');
    }
}
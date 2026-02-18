<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientProfileApiController extends Controller
{
    public function show(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $profile = $client->clientProfile;

        $fileUrl = function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            return url(Storage::disk('public')->url($path));
        };

        $otherDocs = collect($profile?->other_docs ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values()
            ->map(fn (string $path) => [
                'path' => $path,
                'url' => $fileUrl($path),
                'name' => basename($path),
            ]);

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
                'logo_url' => $fileUrl($profile?->logo_path),
                'pan_file_path' => $profile?->pan_file_path,
                'pan_file_url' => $fileUrl($profile?->pan_file_path),
                'tan_file_path' => $profile?->tan_file_path,
                'tan_file_url' => $fileUrl($profile?->tan_file_path),
                'coi_file_path' => $profile?->coi_file_path,
                'coi_file_url' => $fileUrl($profile?->coi_file_path),
                'other_docs' => $otherDocs,
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
            'website' => ['nullable', 'string', 'max:255'],
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
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'pan_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'tan_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'coi_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'other_docs' => ['nullable', 'array', 'max:10'],
            'other_docs.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        $profile = ClientProfile::query()->firstOrNew(['user_id' => $client->id]);

        $profile->company_name = $validated['company_name'];
        $profile->website = $validated['website'] ?? null;
        $profile->industry = $validated['industry'] ?? null;
        $profile->company_size = $validated['company_size'] ?? null;
        $profile->description = $validated['description'] ?? null;
        $profile->contact_person_name = $validated['contact_person_name'] ?? null;
        $profile->contact_phone = $validated['contact_phone'] ?? null;
        $profile->gst_number = $validated['gst_number'] ?? null;
        $profile->address = $validated['address'] ?? null;
        $profile->city = $validated['city'] ?? null;
        $profile->state = $validated['state'] ?? null;
        $profile->pincode = $validated['pincode'] ?? null;
        $profile->pan_number = $validated['pan_number'] ?? null;
        $profile->tan_number = $validated['tan_number'] ?? null;
        $profile->coi_number = $validated['coi_number'] ?? null;

        $storeFile = function (string $inputName, string $fieldName, string $folder) use ($request, $profile): void {
            if ($request->hasFile($inputName)) {
                $profile->{$fieldName} = $request->file($inputName)->store($folder, 'public');
            }
        };

        $storeFile('logo', 'logo_path', 'company_logos');
        $storeFile('pan_file', 'pan_file_path', 'compliance_docs');
        $storeFile('tan_file', 'tan_file_path', 'compliance_docs');
        $storeFile('coi_file', 'coi_file_path', 'compliance_docs');

        if ($request->hasFile('other_docs')) {
            $existingDocs = is_array($profile->other_docs) ? $profile->other_docs : [];
            $newDocs = [];
            foreach ($request->file('other_docs') as $doc) {
                $newDocs[] = $doc->store('client_other_docs', 'public');
            }
            $merged = array_values(array_merge($existingDocs, $newDocs));
            $profile->other_docs = array_slice($merged, 0, 10);
        }

        $profile->save();

        $client->update(['name' => $validated['company_name']]);

        return response()->json(['message' => 'Company profile updated successfully.']);
    }
}

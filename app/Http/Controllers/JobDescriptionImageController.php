<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobDescriptionImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Superadmin', 'Manager', 'client']), 403);

        $request->validate([
            'files' => ['required', 'array', 'max:5'],
            'files.*' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $urls = [];
        foreach ($request->file('files', []) as $image) {
            $path = $image->store('job-description-images/'.now()->format('Y/m'), 'public');
            $urls[] = Storage::disk('public')->url($path);
        }

        return response()->json([
            'success' => true,
            'files' => $urls,
            'baseurl' => '',
            'path' => '',
            'error' => 0,
            'msg' => count($urls) === 1 ? 'Image uploaded.' : 'Images uploaded.',
        ]);
    }
}

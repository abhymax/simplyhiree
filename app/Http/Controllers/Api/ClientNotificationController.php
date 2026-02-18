<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientNotificationController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $unreadOnly = (bool) $request->boolean('unread_only', false);
        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        $query = $client->notifications()->latest();
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate($perPage)->appends($request->query());

        $data = $notifications->getCollection()->map(function ($notification) {
            $payload = is_array($notification->data) ? $notification->data : [];

            return [
                'id' => $notification->id,
                'message' => $payload['message'] ?? 'Notification',
                'icon' => $payload['icon'] ?? 'bell',
                'job_id' => $payload['job_id'] ?? null,
                'read_at' => optional($notification->read_at)->toIso8601String(),
                'created_at' => optional($notification->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => (int) $client->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markRead(Request $request, string $notificationId)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $notification = $client->notifications()->where('id', $notificationId)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $client->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}


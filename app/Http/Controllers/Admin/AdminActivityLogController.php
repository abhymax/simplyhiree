<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminActivityLog::query()
            ->with('actor')
            ->latest('occurred_at');

        if ($request->filled('event_key')) {
            $query->where('event_key', 'like', '%' . $request->input('event_key') . '%');
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.activity_logs.index', compact('logs'));
    }
}

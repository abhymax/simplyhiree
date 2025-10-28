<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job; // Import the Job model

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index()
    {
        $client = Auth::user();

        // Fetch all jobs posted by this client, ordered by the newest first.
        $jobs = Job::where('user_id', $client->id)->latest()->get();

        // Pass both the client and their jobs to the view.
        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs
        ]);
    }
}


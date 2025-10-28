<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\User;  // Ensure we include the User model for role assignment
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CandidateController extends Controller
{
    /**
     * Display the candidate registration form.
     */
    public function create()
    {
        return view('candidate.register');
    }

    /**
     * Handle candidate registration.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'mobile_number' => 'required|digits:10',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'name' => 'required|string|max:255', // Name field validation
            'email' => 'required|email|unique:users,email', // Email validation
            'password' => 'required|confirmed|min:8', // Password validation
        ]);

        // Handle resume upload
        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        // Create the user (candidate) in the users table with 'candidate' role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'candidate',  // Assign 'candidate' role
        ]);

        // Create the candidate record
        Candidate::create([
            'user_id' => $user->id, // Store user ID to associate the candidate with the user
            'mobile_number' => $request->mobile_number,
            'resume_path' => $resumePath,
        ]);

        // Log the user (candidate) in
        Auth::login($user);

        // Redirect to the candidate dashboard after registration
        return redirect()->route('candidate.dashboard')->with('success', 'Candidate registered successfully!');
    }

    /**
     * Show the candidate dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ensure the user is authenticated and has the 'candidate' role
        if (Auth::check() && Auth::user()->role === 'candidate') {
            return view('candidate.dashboard');  // Candidate dashboard view
        }

        // Redirect to the homepage if not a candidate
        return redirect('/');
    }
    public function applications()
{
    $applications = Auth::user()->candidate->applications; // Assuming there's a relationship between Candidate and Application

    return view('candidate.applications', compact('applications')); // Make sure you have a view for this
}
}

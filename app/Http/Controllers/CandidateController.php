<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;
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
        $request->validate([
            'mobile_number' => 'required|digits:10',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'candidate',
        ]);

        Candidate::create([
            'user_id' => $user->id,
            'mobile_number' => $request->mobile_number,
            'resume_path' => $resumePath,
        ]);

        Auth::login($user);

        return redirect()->route('candidate.dashboard')->with('success', 'Candidate registered successfully!');
    }

    /**
     * Candidate dashboard.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();

        if (!($user->hasRole('candidate') || ($user->role ?? null) === 'candidate')) {
            return redirect('/');
        }

        $todayInterviews = JobApplication::where('candidate_user_id', $user->id)
            ->whereDate('interview_at', Carbon::today())
            ->count();

        $totalApplications = JobApplication::where('candidate_user_id', $user->id)->count();

        $pendingApplications = JobApplication::where('candidate_user_id', $user->id)
            ->whereIn('status', ['Pending Review', 'Approved', 'Interview Scheduled'])
            ->count();

        return view('candidate.dashboard', [
            'todayInterviews' => $todayInterviews,
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
        ]);
    }

    /**
     * Candidate applications list.
     */
    public function applications()
    {
        $applications = JobApplication::where('candidate_user_id', Auth::id())
            ->with('job')
            ->latest()
            ->paginate(15);

        return view('candidate.applications', compact('applications'));
    }
}
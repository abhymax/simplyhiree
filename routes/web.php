<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\SubAdminController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PartnerProfileController;
use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\ClientProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialController;

/*
|--------------------------------------------------------------------------
| Public & Guest Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('landing');
})->name('home');

// --- STATIC PAGES ---
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show'); 

// --- GOOGLE AUTH ROUTES ---
Route::get('auth/google', [SocialController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [SocialController::class, 'handleGoogleCallback']);
Route::get('auth/google/verify-phone', [SocialController::class, 'showGooglePhoneVerificationForm'])->name('google.phone.verify');
Route::post('auth/google/verify-phone', [SocialController::class, 'completeGooglePhoneVerification'])->name('google.phone.verify.submit');

// Guest-only registration routes
Route::middleware('guest')->group(function () {
    Route::get('/register/partner', [RegisteredUserController::class, 'showPartnerRegistrationForm'])->name('register.partner');
    Route::post('/register/partner', [RegisteredUserController::class, 'registerPartner']);
    Route::get('/register/candidate', [RegisteredUserController::class, 'showCandidateRegistrationForm'])->name('register.candidate');
    Route::post('/register/candidate', [RegisteredUserController::class, 'registerCandidate']);
    Route::get('/register/client', [RegisteredUserController::class, 'showClientRegistrationForm'])->name('register.client');
    Route::post('/register/client', [RegisteredUserController::class, 'registerClient']);
});

// Standard Laravel authentication routes
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'status.check'])->group(function () {
    
    // --- MAIN DASHBOARD REDIRECTOR ---
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if ($user->hasRole('Superadmin') || $user->hasRole('Manager')) {
            return redirect()->route('admin.dashboard');
        } 
        elseif ($user->hasRole('client')) {
            return redirect()->route('client.dashboard');
        } 
        elseif ($user->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        } 
        elseif ($user->hasRole('candidate')) {
            return redirect()->route('candidate.dashboard');
        }
        return redirect('/');
    })->name('dashboard');

    // --- GENERIC AUTH ROUTES ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/apply/{job}', [JobController::class, 'apply'])->middleware('role:candidate')->name('jobs.apply');
    
    // ==========================================
    //        ADMIN PANEL ROUTES (Shared)
    // ==========================================
    Route::middleware(['role:Superadmin|Manager'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

        // --- IMPERSONATION ---
        Route::get('/impersonate/stop', [SubAdminController::class, 'stopImpersonating'])->name('impersonate.stop');
        Route::get('/impersonate/{user}', [SubAdminController::class, 'impersonate'])->name('impersonate.start');


        // --- SUPERADMIN ONLY ACTIONS ---
        Route::middleware(['role:Superadmin'])->group(function() {
            // Manage Sub-Admins
            Route::resource('sub_admins', SubAdminController::class);
            
            // User Management
            Route::get('/users', [AdminController::class, 'listUsers'])->name('users.index');
            Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
            Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status.update');
            Route::patch('/users/{user}/credentials', [AdminController::class, 'updateUserCredentials'])->name('users.credentials.update');
            
            // View Single Application Details
            Route::get('/applications/{application}', [AdminController::class, 'showApplication'])->name('applications.show');
            
            Route::get('/interviews/today', [AdminController::class, 'dailySchedule'])->name('interviews.today');
            Route::delete('/jobs/{job}', [AdminController::class, 'destroyJob'])->name('jobs.destroy'); 
        });

        // --- CLIENT MANAGEMENT ---
        Route::middleware(['role_or_permission:Superadmin|manage_clients'])->group(function() {
            Route::get('/clients', [AdminController::class, 'listClients'])->name('clients.index');
            Route::get('/clients/create', [AdminController::class, 'createClient'])->name('clients.create');
            Route::post('/clients', [AdminController::class, 'storeClient'])->name('clients.store');
            Route::get('/clients/{user}/edit', [AdminController::class, 'editClient'])->name('clients.edit');
            Route::patch('/clients/{user}', [AdminController::class, 'updateClient'])->name('clients.update');
            Route::get('/clients/{user}/show', [AdminController::class, 'showClient'])->name('clients.show');
        });


        // --- PARTNER DATA ---
        Route::middleware(['can:view_partner_data'])->group(function() {
            Route::get('/partners', [AdminController::class, 'listPartners'])->name('partners.index');
            Route::get('/partners/create', [AdminController::class, 'createPartner'])->name('partners.create');
            Route::post('/partners', [AdminController::class, 'storePartner'])->name('partners.store');
            
            // Route Fixed: This handles viewing the partner profile
            Route::get('/partners/{user}/show', [AdminController::class, 'showPartner'])->name('partners.show');
            
            Route::get('/partners/{user}/edit', [AdminController::class, 'editPartner'])->name('partners.edit');
            Route::patch('/partners/{user}', [AdminController::class, 'updatePartner'])->name('partners.update');
        });


        // --- APPLICATION DATA ---
        Route::middleware(['can:view_application_data'])->group(function() {
            Route::get('/applications', [AdminController::class, 'listApplications'])->name('applications.index');
            Route::post('/applications/{application}/approve', [AdminController::class, 'approveApplication'])->name('applications.approve');
            Route::post('/applications/{application}/reject', [AdminController::class, 'rejectApplication'])->name('applications.reject');
        });


        // --- JOB MANAGEMENT ---
        Route::middleware(['can:view_pending_jobs'])->group(function() {
            Route::get('/jobs/pending', [AdminController::class, 'pendingJobs'])->name('jobs.pending');
            Route::get('/jobs/create', [AdminController::class, 'createJob'])->name('jobs.create');
            Route::post('/jobs', [AdminController::class, 'storeJob'])->name('jobs.store');
            Route::get('/jobs/{job}', [AdminController::class, 'showJob'])->name('jobs.show');
            Route::post('/jobs/{job}/approve', [AdminController::class, 'approveJob'])->name('jobs.approve');
            Route::post('/jobs/{job}/reject', [AdminController::class, 'rejectJob'])->name('jobs.reject');
            Route::patch('/jobs/{job}/status', [AdminController::class, 'updateJobStatus'])->name('jobs.status.update'); 
            
            Route::get('/jobs/{job}/manage', [AdminController::class, 'manageJobExclusions'])->name('jobs.manage');
            Route::post('/jobs/{job}/exclusions', [AdminController::class, 'updateJobExclusions'])->name('jobs.exclusions.update');
        });


        // --- BILLING & REPORTS ---
        Route::middleware(['can:view_billing_data'])->group(function() {
            Route::get('/billing', [AdminController::class, 'billingReport'])->name('billing.index');
            Route::patch('/applications/{application}/mark-paid', [AdminController::class, 'markAsPaid'])->name('applications.markPaid');
            
            // MASTER JOB REPORT
            Route::get('/reports/jobs', [AdminController::class, 'jobReport'])->name('reports.jobs');
            Route::get('/reports/jobs/export', [AdminController::class, 'exportJobReport'])->name('reports.jobs.export');
            
            // Drill-down to see applicants for a specific job
            Route::get('/reports/jobs/{job}/applicants', [AdminController::class, 'jobApplicantsReport'])
                ->name('reports.jobs.applicants');
            Route::get('/reports/jobs/{job}/applicants/export', [AdminController::class, 'exportJobApplicantsReport'])
                ->name('reports.jobs.applicants.export');
        });

    });


    // ==========================================
    //         CLIENT (EMPLOYER) ROUTES
    // ==========================================
    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {
        
        Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
        
        // --- Job Management ---
        Route::get('/jobs/create', [ClientController::class, 'createJob'])->name('jobs.create');
        Route::post('/jobs', [ClientController::class, 'storeJob'])->name('jobs.store');
        
        Route::patch('/jobs/{job}/status', [JobController::class, 'updateStatus'])->name('jobs.status.update'); 
        Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy'); 
        Route::get('/jobs/{job}/applicants', [ClientController::class, 'showApplicants'])->name('jobs.applicants');
        
        // Profile Management
        Route::get('/profile/company', [ClientProfileController::class, 'edit'])->name('profile.company');
        Route::patch('/profile/company', [ClientProfileController::class, 'update'])->name('profile.update');
        
        Route::get('/billing', [ClientController::class, 'billing'])->name('billing');
        Route::get('/interviews/today', [ClientController::class, 'dailySchedule'])->name('interviews.today');

        // --- INTERVIEW & HIRING WORKFLOW ---
        
        // 1. New Interview
        Route::get('/applications/{application}/interview/create', [ClientController::class, 'showInterviewForm'])->name('applications.interview.create'); 
        Route::post('/applications/{application}/interview', [ClientController::class, 'scheduleInterview'])->name('applications.interview.store');

        // 2. Edit Existing Interview
        Route::get('/applications/{application}/interview/edit', [ClientController::class, 'editInterviewDetails'])->name('applications.interview.edit');
        Route::put('/applications/{application}/interview', [ClientController::class, 'updateInterviewDetails'])->name('applications.interview.update');
        
        // 3. Status Actions
        Route::post('/applications/{application}/reject', [ClientController::class, 'rejectApplicant'])->name('applications.reject');
        Route::post('/applications/{application}/interview-appeared', [ClientController::class, 'markAsAppeared'])->name('applications.interview.appeared');
        Route::post('/applications/{application}/interview-noshow', [ClientController::class, 'markAsNoShow'])->name('applications.interview.noshow');

        // 4. Selection
        Route::get('/applications/{application}/select', [ClientController::class, 'showSelectForm'])->name('applications.select.show');
        Route::get('/applications/{application}/select/edit', [ClientController::class, 'editSelection'])->name('applications.select.edit');
        Route::post('/applications/{application}/select', [ClientController::class, 'storeSelection'])->name('applications.select.store');
        Route::patch('/applications/{application}/select/update', [ClientController::class, 'updateSelectionDetails'])->name('applications.select.update');
            
        // 5. Final Status (Joined / Not Joined)
        Route::post('/applications/{application}/mark-joined', [ClientController::class, 'markAsJoined'])->name('applications.markJoined');
        Route::post('/applications/{application}/mark-not-joined', [ClientController::class, 'markAsNotJoined'])->name('applications.markNotJoined');

        // 6. Left / Exited
        Route::get('/applications/{application}/left', [ClientController::class, 'showLeftForm'])->name('applications.showLeftForm');
        Route::post('/applications/{application}/left', [ClientController::class, 'markAsLeft'])->name('applications.markLeft');
    });


    // ==========================================
    //          PARTNER (AGENCY) ROUTES
    // ==========================================
    Route::middleware(['role:partner'])->prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerController::class, 'index'])->name('dashboard');
        Route::get('/applications', [PartnerController::class, 'applications'])->name('applications');
        Route::get('/earnings', [PartnerController::class, 'earnings'])->name('earnings');
        
        // Jobs
        Route::get('/jobs', [PartnerController::class, 'jobs'])->name('jobs');
        Route::get('/jobs/{job}', [PartnerController::class, 'showJob'])->name('jobs.show'); 
        Route::get('/jobs/{job}/apply', [PartnerController::class, 'showApplyForm'])->name('jobs.showApplyForm');
        Route::post('/jobs/{job}/submit', [PartnerController::class, 'submitApplication'])->name('jobs.submit');

        // Candidate Management
        Route::get('/candidates/check', [PartnerController::class, 'checkCandidateMobile'])->name('candidates.check'); 
        Route::post('/candidates/check', [PartnerController::class, 'verifyCandidateMobile'])->name('candidates.verify'); 
        Route::get('/candidates/create', [PartnerController::class, 'createCandidate'])->name('candidates.create'); 
        Route::post('/candidates', [PartnerController::class, 'storeCandidate'])->name('candidates.store'); 
        
        Route::get('/candidates', [PartnerController::class, 'listCandidates'])->name('candidates.index');
        Route::get('/candidates/{candidate}/edit', [PartnerController::class, 'editCandidate'])->name('candidates.edit');
        Route::patch('/candidates/{candidate}', [PartnerController::class, 'updateCandidate'])->name('candidates.update');
        
        // Profile
        Route::get('/profile/business', [PartnerProfileController::class, 'edit'])->name('profile.business');
        Route::patch('/profile/business', [PartnerProfileController::class, 'update'])->name('profile.update');
    });
    

    // ==========================================
    //          CANDIDATE ROUTES
    // ==========================================
    Route::middleware('role:candidate')->prefix('candidate')->name('candidate.')->group(function () {
        Route::get('/dashboard', [CandidateController::class, 'index'])->name('dashboard');
        Route::get('/applications', [CandidateController::class, 'applications'])->name('applications');
        
        // Profile
        Route::get('/profile/edit', [CandidateProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile/update', [CandidateProfileController::class, 'update'])->name('profile.update');
    });
});

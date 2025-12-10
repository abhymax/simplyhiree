<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class SubAdminController extends Controller
{
    /**
     * List all Sub-Admins (Managers).
     */
    public function index()
    {
        $managers = User::role('Manager')->with('assignedClients')->latest()->paginate(10);
        return view('admin.sub_admins.index', compact('managers'));
    }

    /**
     * Show form to create a new Manager.
     */
    public function create()
    {
        $permissions = Permission::all()->pluck('name');
        // Fetch all active Clients to assign
        $clients = User::role('client')->where('status', 'active')->get();
        return view('admin.sub_admins.create', compact('permissions', 'clients'));
    }

    /**
     * Store a new Manager.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'permissions' => 'array',
            'clients' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->assignRole('Manager');

        // Sync Granular Permissions
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        // Assign Clients
        if ($request->has('clients')) {
            $user->assignedClients()->sync($request->clients);
        }

        return redirect()->route('admin.sub_admins.index')->with('success', 'Sub-Admin created successfully.');
    }

    /**
     * Show form to edit a Manager.
     */
    public function edit(User $sub_admin)
    {
        // Note: The route param is 'sub_admin' because of Route::resource logic, 
        // but we can type-hint User.
        $user = $sub_admin; 
        
        $permissions = Permission::all()->pluck('name');
        $clients = User::role('client')->where('status', 'active')->get();
        
        $userPermissions = $user->permissions->pluck('name')->toArray();
        $assignedClientIds = $user->assignedClients->pluck('id')->toArray();

        return view('admin.sub_admins.edit', compact('user', 'permissions', 'clients', 'userPermissions', 'assignedClientIds'));
    }

    /**
     * Update an existing Manager.
     */
    public function update(Request $request, User $sub_admin)
    {
        $user = $sub_admin;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'permissions' => 'array',
            'clients' => 'array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncPermissions($request->permissions ?? []);
        $user->assignedClients()->sync($request->clients ?? []);

        return redirect()->route('admin.sub_admins.index')->with('success', 'Sub-Admin updated successfully.');
    }

    // --- IMPERSONATION LOGIC ---

    public function impersonate($userId)
    {
        $userToImpersonate = User::findOrFail($userId);
        $currentUser = Auth::user();

        // 1. Security Check: Permission
        if (!$currentUser->can('impersonate_clients') && !$currentUser->hasRole('Superadmin')) {
            abort(403, 'Unauthorized to impersonate.');
        }

        // 2. Scope Check: Assignment (Superadmin can mimic anyone)
        if (!$currentUser->hasRole('Superadmin') && !$currentUser->assignedClients->contains($userId)) {
            abort(403, 'This client is not assigned to you.');
        }

        // 3. Start Impersonation
        session()->put('impersonator_id', $currentUser->id);
        Auth::login($userToImpersonate);

        return redirect()->route('client.dashboard');
    }

    public function stopImpersonating()
    {
        if (session()->has('impersonator_id')) {
            Auth::loginUsingId(session()->get('impersonator_id'));
            session()->forget('impersonator_id');
            return redirect()->route('admin.dashboard');
        }
        return redirect('/');
    }
}
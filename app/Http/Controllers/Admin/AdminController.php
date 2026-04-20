<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     */
    public function index()
    {
        try {
            $admins = User::whereIn('role', ['admin', 'super_admin'])
                ->orderBy('name')
                ->paginate(20);

            return view('admin.admins.index', compact('admins'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to load admins');
        }
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,super_admin',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_admin',
                'model_type' => User::class,
                'model_id' => $user->id,
                'new_values' => $user->toArray(),
                'ip_address' => $request->ip(),
                'description' => "Created {$user->role} account for {$user->name}",
            ]);

            return redirect()->route('admin.admins.show', ['admin' => $user])
                ->with('success', 'Admin created successfully');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Failed to create admin');
        }
    }

    /**
     * Display the specified admin.
     */
    public function show(User $admin)
    {
        // if (!in_array($admin->role, ['admin', 'super_admin'])) {
        //     abort(404);
        // }

        $admin->loadMissing('auditLogs');

        return view('admin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the admin.
     */
    public function edit(User $admin)
    {
        if (!in_array($admin->role, ['admin', 'super_admin'])) {
            abort(404);
        }

        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            abort(403, 'You cannot edit other super admins.');
        }

        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the admin.
     */
    public function update(Request $request, User $admin)
    {
        if (!in_array($admin->role, ['admin', 'super_admin'])) {
            abort(404);
        }

        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            abort(403, 'You cannot edit other super admins.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'role' => 'required|in:admin,super_admin',
        ]);

        try {
            $oldValues = $admin->toArray();

            $admin->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_admin',
                'model_type' => User::class,
                'model_id' => $admin->id,
                'old_values' => $oldValues,
                'new_values' => $admin->toArray(),
                'ip_address' => $request->ip(),
                'description' => "Updated {$admin->role} account for {$admin->name}",
            ]);

            return redirect()->route('admin.admins.show', ['admin' => $admin])
                ->with('success', 'Admin updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Failed to update admin');
        }
    }

    /**
     * Remove the admin.
     */
    public function destroy(User $admin)
    {
        if (!in_array($admin->role, ['admin', 'super_admin'])) {
            abort(404);
        }

        if ($admin->role === 'super_admin' && $admin->id !== auth()->id()) {
            abort(403, 'You cannot delete other super admins.');
        }

        // Prevent deleting the last super admin
        if ($admin->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last super admin');
        }

        try {
            $oldValues = $admin->toArray();
            $admin->delete();

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_admin',
                'model_type' => User::class,
                'model_id' => $admin->id,
                'old_values' => $oldValues,
                'ip_address' => request()->ip(),
                'description' => "Deleted {$admin->role} account for {$admin->name}",
            ]);

            return redirect()->route('admin.admins.index')
                ->with('success', 'Admin deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to delete admin');
        }
    }
}

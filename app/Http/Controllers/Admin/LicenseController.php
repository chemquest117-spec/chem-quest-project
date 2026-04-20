<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display license status.
     */
    public function index()
    {
        $license = License::latest()->first();

        return view('admin.license.index', compact('license'));
    }

    /**
     * Activate license.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        try {
            $license = License::where('key', $request->key)->first();

            if (!$license) {
                $license = License::create([
                    'key' => $request->key,
                    'is_active' => true,
                    'activated_at' => now(),
                    'activated_by' => auth()->id(),
                ]);
            } else {
                $license->update([
                    'is_active' => true,
                    'activated_at' => now(),
                    'activated_by' => auth()->id(),
                ]);
            }

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'activate_license',
                'model_type' => License::class,
                'model_id' => $license->id,
                'new_values' => ['is_active' => true],
                'ip_address' => $request->ip(),
                'description' => 'Activated license key',
            ]);

            return back()->with('success', 'License activated successfully');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to activate license');
        }
    }

    /**
     * Deactivate license.
     */
    public function deactivate(Request $request)
    {
        try {
            $license = License::where('is_active', true)->first();

            if ($license) {
                $license->update(['is_active' => false]);

                // Log the action
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'deactivate_license',
                    'model_type' => License::class,
                    'model_id' => $license->id,
                    'old_values' => ['is_active' => true],
                    'new_values' => ['is_active' => false],
                    'ip_address' => $request->ip(),
                    'description' => 'Deactivated license key',
                ]);
            }

            return back()->with('success', 'License deactivated successfully');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to deactivate license');
        }
    }
}

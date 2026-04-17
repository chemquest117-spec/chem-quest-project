<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdminNotificationController extends Controller
{
    /**
     * Show the form to send a custom notification.
     */
    public function create()
    {
        $header = __('Send {{ __('navigation.broadcast') }} Notification');

        // Fetch students to populate a dropdown if the admin wants to target specific users
        $students = User::student()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.notifications.create', compact('header', 'students'));
    }

    /**
     * Process and dispatch the notification to selected users.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string|in:all,inactive,specific',
            'user_ids' => 'required_if:target,specific|array',
            'user_ids.*' => 'exists:users,id',
            'title_en' => 'required|string|max:100',
            'title_ar' => 'required|string|max:100',
            'message_en' => 'required|string|max:500',
            'message_ar' => 'required|string|max:500',
            'type' => 'required|string|in:info,success,warning',
        ]);

        $query = User::student()->whereNull('deleted_at');

        if ($validated['target'] === 'specific') {
            $query->whereIn('id', $validated['user_ids']);
        } elseif ($validated['target'] === 'inactive') {
            // Target students who haven't been active in the last 7 days
            $query->where(function ($q) {
                $q->where('last_activity', '<', now()->subDays(7))
                    ->orWhereNull('last_activity');
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            return back()->with('error', __('admin.no_users_found_matching_8e61'));
        }

        // Use Laravel's Notification Facade to chunk and send efficiently via queue
        Notification::send($users, new AdminAnnouncement(
            $validated['title_en'],
            $validated['title_ar'],
            $validated['message_en'],
            $validated['message_ar'],
            $validated['type']
        ));

        return back()->with('success', "Notification dispatched successfully to {$users->count()} student(s).");
    }
}

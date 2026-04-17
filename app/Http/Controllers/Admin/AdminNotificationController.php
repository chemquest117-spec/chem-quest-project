<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AdminNotificationController extends Controller
{
    /**
     * Show the form to send a custom notification.
     */
    public function create()
    {
        $header = __('admin.send_broadcast_title');

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
            'title_en' => [
                'required', 'string', 'max:100',
                function ($attribute, $value, $fail) {
                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
                        $fail(__('validation.english_only', ['attribute' => $attribute]));
                    }
                },
            ],
            'title_ar' => [
                'required', 'string', 'max:100',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
                        $fail(__('validation.arabic_only', ['attribute' => $attribute]));
                    }
                },
            ],
            'message_en' => [
                'required', 'string', 'max:500',
                function ($attribute, $value, $fail) {
                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
                        $fail(__('validation.english_only', ['attribute' => $attribute]));
                    }
                },
            ],
            'message_ar' => [
                'required', 'string', 'max:500',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
                        $fail(__('validation.arabic_only', ['attribute' => $attribute]));
                    }
                },
            ],
            'type' => 'required|string|in:info,success,warning',
        ]);

        $query = User::whereNull('deleted_at'); // Temporarily targeting all users for debugging

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
        error_log('[BROADCAST_DEBUG] Sending to '.$users->count().' users. IDs: '.$users->pluck('id')->implode(','));

        if ($users->isEmpty()) {
            return back()->with('error', __('admin.no_users_found_matching_8e61'));
        }

        // Manually check if the database can even save notifications
        try {
            $testUser = $users->first();
            $testUser->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\AdminAnnouncement',
                'data' => ['message' => 'DB_TEST_STRICT', 'category' => 'announcement'],
            ]);
            error_log('[BROADCAST_DEBUG] Manual DB insert succeeded for user: '.$testUser->id);
        } catch (\Exception $e) {
            error_log('[BROADCAST_DEBUG] Manual DB insert FAILED: '.$e->getMessage());
        }

        // Use Laravel's Notification Facade to chunk and send efficiently via queue
        Notification::send($users, new AdminAnnouncement(
            $validated['title_en'],
            $validated['title_ar'],
            $validated['message_en'],
            $validated['message_ar'],
            $validated['type']
        ));

        return back()->with('success', __('admin.notification_dispatched_successfully', ['count' => $users->count()]));
    }
}

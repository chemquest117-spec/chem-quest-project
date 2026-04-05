<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $notifications = $request->user()->notifications()->paginate(15);

            return view('notifications.index', compact('notifications'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while loading your notifications. Please try again, or contact support if the problem persists.');
        }
    }

    public function markAsRead(Request $request, string $id)
    {
        try {
            $notification = $request->user()->notifications()->findOrFail($id);
            $notification->markAsRead();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', __('messages.success'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while marking the notification as read. Please try again, or contact support if the problem persists.');
        }
    }

    public function markAllRead(Request $request)
    {
        try {
            $request->user()->unreadNotifications->markAsRead();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', __('messages.success'));
        } catch (\Throwable $e) {
            report($e); // Log the error internally (to Sentry/Log)

            return back()
                ->withInput()
                ->with('error', 'We encountered an unexpected error while marking all notifications as read. Please try again, or contact support if the problem persists.');
        }
    }
}

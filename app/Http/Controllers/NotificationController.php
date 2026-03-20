<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
     public function index(Request $request)
     {
          $notifications = $request->user()->notifications()->paginate(15);
          return view('notifications.index', compact('notifications'));
     }

     public function markAsRead(Request $request, string $id)
     {
          $notification = $request->user()->notifications()->findOrFail($id);
          $notification->markAsRead();

          if ($request->expectsJson() || $request->ajax()) {
               return response()->json(['success' => true]);
          }

          return back()->with('success', __('messages.success'));
     }

     public function markAllRead(Request $request)
     {
          $request->user()->unreadNotifications->markAsRead();

          if ($request->expectsJson() || $request->ajax()) {
               return response()->json(['success' => true]);
          }

          return back()->with('success', __('messages.success'));
     }
}

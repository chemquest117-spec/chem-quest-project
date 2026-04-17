<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class DeviceTokenController extends Controller
{
    /**
     * Register or update an FCM device token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
            'platform' => 'required|string|in:web,android,ios',
        ]);

        $user = $request->user();

        $agent = new Agent;
        $agent->setUserAgent($request->userAgent());

        DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'],
                'browser' => $agent->browser().' '.$agent->version($agent->browser()),
                'os' => $agent->platform().' '.$agent->version($agent->platform()),
                'device' => $agent->device(),
                'ip_address' => $request->ip(),
                'last_used_at' => now(),
            ],
        );

        return response()->json(['success' => true], 200);
    }

    /**
     * Remove a device token (e.g., on logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        DeviceToken::where('token', $validated['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true], 200);
    }
}

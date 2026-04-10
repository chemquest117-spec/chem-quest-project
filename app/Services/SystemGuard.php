<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemGuard
{
    private const CACHE_KEY = 'sys_core_health_status';

    private const OFFLINE_CACHE_KEY = 'sys_core_last_success';

    private const CACHE_TTL = 300; // 5 minutes

    private const GRACE_PERIOD = 86400; // 24 hours

    public function isSystemHealthy(): bool
    {
        // 1. Admin Bypass
        $adminEmail = config('services.license.admin_email');
        if (! empty($adminEmail) && auth()->check() && auth()->user()->email === $adminEmail) {
            return true;
        }

        // 2. Missing License Key
        $licenseKey = config('services.license.key');
        if (empty($licenseKey)) {
            return false;
        }

        // 3. Cached Validation
        if (Cache::has(self::CACHE_KEY)) {
            return (bool) Cache::get(self::CACHE_KEY);
        }

        // 4. Remote Server Check
        $serverUrl = config('services.license.server_url', 'https://my-license-server.com/api/check-license');

        try {
            $request = Http::timeout(5);

            // Avoid cURL error 60 on local Windows development environments
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get($serverUrl);

            if ($response->successful()) {
                $status = $response->json('status');
                $isActive = ($status === 'active');

                Cache::put(self::CACHE_KEY, $isActive, self::CACHE_TTL);

                if ($isActive) {
                    Cache::put(self::OFFLINE_CACHE_KEY, now()->timestamp, self::GRACE_PERIOD);
                }

                return $isActive;
            }
        } catch (\Exception $e) {
            Log::warning('SystemGuard: License server check failed. '.$e->getMessage());
        }

        // 5. Offline Grace Period Check
        return $this->verifyOfflineGracePeriod();
    }

    private function verifyOfflineGracePeriod(): bool
    {
        $lastSuccess = Cache::get(self::OFFLINE_CACHE_KEY);

        if (! $lastSuccess) {
            return false;
        }

        return (now()->timestamp - $lastSuccess) <= self::GRACE_PERIOD;
    }
}

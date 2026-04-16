<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class StageSchemaCache
{
    private const VERSION_KEY = 'stages_schema_version';

    public static function version(): int
    {
        $v = Cache::get(self::VERSION_KEY);

        return is_numeric($v) ? (int) $v : 1;
    }

    public static function bump(): int
    {
        // Some stores don't support atomic increment; fall back safely.
        try {
            return (int) Cache::increment(self::VERSION_KEY);
        } catch (\Throwable $e) {
            $v = self::version() + 1;
            Cache::put(self::VERSION_KEY, $v, 86400 * 365);

            return $v;
        }
    }
}

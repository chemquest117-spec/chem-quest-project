<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostgresBoolean implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return (bool) $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * Forced conversion to 'true'/'false' strings for PostgreSQL when PDO emulation is active,
     * as Postgres rejects boolean = 1/0 comparisons. Fallback to standard for other drivers.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return self::asQueryValue($value);
    }

    /**
     * Static helper for manual where clauses in Query Builder.
     */
    public static function asQueryValue(mixed $value): mixed
    {
        $boolValue = (bool) $value;

        try {
            // Dynamically detect the driver in use, because the default connection might
            // have a mapped name instead of 'pgsql', or be strictly overridden.
            $driver = DB::connection()->getDriverName();
            if ($driver === 'pgsql') {
                return $boolValue ? 'true' : 'false';
            }
        } catch (\Throwable $e) {
            // Fallback if DB facade is not yet initialized or config fails
            if (config('database.default') === 'pgsql') {
                return $boolValue ? 'true' : 'false';
            }
        }

        return $boolValue;
    }
}

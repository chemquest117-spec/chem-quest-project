<?php

namespace App\Support;

/**
 * Centralized TTL strategy for the hybrid caching system.
 *
 * Three tiers based on data volatility:
 * - Static:       data that rarely changes (stages, schema)      → 1–6 hours
 * - SemiDynamic:  data that changes occasionally (question IDs)  → 5–30 minutes
 * - Dynamic:      highly volatile data (leaderboard, globals)    → 1–5 minutes
 *
 * Each tier defines:
 * - redisTtl:      how long Redis stores the value
 * - memoryTtl:     how long in-process memory caches the value
 * - staleWindow:   additional seconds a stale value may be served while refreshing
 */
final class CacheTTL
{
    // ── Static data (stages, schema) ────────────────────────────
    public const STATIC_REDIS   = 6 * 3600;   // 6 hours
    public const STATIC_MEMORY  = 600;         // 10 minutes
    public const STATIC_STALE   = 1800;        // 30 minutes stale window

    // ── Semi-dynamic data (question IDs, stage counts) ─────────
    public const SEMI_REDIS     = 30 * 60;     // 30 minutes
    public const SEMI_MEMORY    = 300;         // 5 minutes
    public const SEMI_STALE     = 600;         // 10 minutes stale window

    // ── Highly dynamic / global data (leaderboard) ─────────────
    public const DYNAMIC_REDIS  = 5 * 60;      // 5 minutes
    public const DYNAMIC_MEMORY = 60;          // 1 minute
    public const DYNAMIC_STALE  = 120;         // 2 minutes stale window

    // ── Per-user memory-only data (dashboard stats) ────────────
    public const USER_MEMORY    = 1800;        // 30 minutes (memory-only, no Redis)

    /**
     * Prevent instantiation.
     */
    private function __construct() {}
}

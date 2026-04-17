# 🧪 ChemTrack — Chemistry Quiz Learning Platform

A progressive chemistry quiz platform for high school students built with **Laravel 12**, **TailwindCSS**, and **Alpine.js**.

Students progress through 5 chemistry stages, take timed quizzes, earn points and stars, and compete on a leaderboard. Teachers manage stages, questions, and monitor student progress through an admin panel.

---

## 📁 Project Structure

```
ChemTrack/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminDashboardController.php    # Admin overview stats
│   │   │   │   ├── AdminQuestionController.php     # Question CRUD
│   │   │   │   ├── AdminStageController.php        # Stage CRUD
│   │   │   │   └── AdminStudentController.php      # Student progress view
│   │   │   ├── Auth/                               # Breeze auth controllers
│   │   │   ├── DashboardController.php             # Student dashboard
│   │   │   ├── LeaderboardController.php           # Top students ranking
│   │   │   ├── NotificationController.php          # Mark notifications read
│   │   │   ├── QuizController.php                  # Quiz start/show/submit/result
│   │   │   ├── StageController.php                 # Stage list & detail
│   │   │   └── StudyPlannerController.php          # Study planner management
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php                 # Admin route protection
│   │       └── FlushRequestCache.php              # Per-request cache isolation
│   ├── Jobs/
│   │   └── RefreshCacheJob.php                     # SWR background cache refresh
│   ├── Models/
│   │   ├── AttemptAnswer.php                       # Individual question response
│   │   ├── Question.php                            # MCQ with randomized scope
│   │   ├── Stage.php                               # Stage with unlock logic
│   │   ├── StageAttempt.php                        # Quiz attempt record
│   │   └── User.php                                # Extended with gamification
│   ├── Notifications/
│   │   └── StageCompleted.php                      # Database notification
│   └── Support/
│       ├── CacheTTL.php                            # Centralized TTL constants
│       ├── MemoryCache.php                         # Request + in-process memory cache
│       ├── StageSchemaCache.php                    # Stage schema versioning
│       └── TwoLayerCache.php                       # Hybrid cache: Memory → Redis → DB
├── bootstrap/
│   └── app.php                                     # Middleware + cache registered
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php       # + is_admin, points, stars
│   │   ├── 2025_01_01_000010_create_stages_table.php      # Stages
│   │   ├── 2025_01_01_000020_create_questions_table.php   # Questions
│   │   ├── 2025_01_01_000030_create_stage_attempts_table.php  # Attempts
│   │   ├── 2025_01_01_000040_create_attempt_answers_table.php # Answers
│   │   └── 2026_..._create_notifications_table.php        # Notifications
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── QuestionSeeder.php                      # 50 chemistry questions
│       ├── StageSeeder.php                         # 5 progressive stages
│       └── UserSeeder.php                          # Admin + Student accounts
├── resources/views/
│   ├── admin/
│   │   ├── dashboard.blade.php                     # Admin stats overview
│   │   ├── questions/
│   │   │   ├── create.blade.php                    # Add question form
│   │   │   ├── edit.blade.php                      # Edit question form
│   │   │   └── index.blade.php                     # Question list per stage
│   │   ├── stages/
│   │   │   ├── create.blade.php                    # Create stage form
│   │   │   ├── edit.blade.php                      # Edit stage form
│   │   │   └── index.blade.php                     # Stage management list
│   │   └── students/
│   │       └── index.blade.php                     # Student progress matrix
│   ├── auth/                                       # Breeze auth views
│   ├── dashboard.blade.php                         # Student dashboard
│   ├── layouts/
│   │   ├── app.blade.php                           # Main layout (dark theme)
│   │   ├── guest.blade.php                         # Auth layout
│   │   └── navigation.blade.php                    # Nav with notification bell
│   ├── leaderboard/
│   │   └── index.blade.php                         # Top students ranking
│   ├── quiz/
│   │   ├── result.blade.php                        # Score + answer review
│   │   └── show.blade.php                          # Quiz with Alpine.js timer
│   ├── stages/
│   │   ├── index.blade.php                         # Visual roadmap
│   │   └── show.blade.php                          # Stage detail + start quiz
│   └── welcome.blade.php                           # Landing page
├── tests/
│   └── Unit/
│       ├── CacheTTLTest.php                        # TTL hierarchy validation
│       ├── MemoryCacheTest.php                     # Request + memory cache tests
│       └── TwoLayerCacheTest.php                   # Hybrid cache + SWR tests
├── routes/
│   ├── auth.php                                    # Breeze auth routes
│   └── web.php                                     # All app routes
├── .env                                            # SQLite config (ready to run)
├── composer.json
└── package.json
```

---

## 🚀 Quick Start

```bash
# 1. Navigate to project directory
cd e:\Projects\Learning_Project

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Run migrations and seed demo data
php artisan migrate:fresh --seed

# 6. Start Laravel server
php artisan serve

# 7. Start Vite dev server (new terminal)
npm run dev

# 8. Open http://127.0.0.1:8000
```

> **Note:** The app uses SQLite by default — no MySQL setup needed.
> To switch to MySQL, update `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`.

---

## 🔑 Demo Credentials

| Role                | Email                 | Password |
| ------------------- | --------------------- | -------- |
| **Admin (Teacher)** | admin@chemtrack.com   | password |
| **Student**         | student@chemtrack.com | password |

---

## 📊 Database Schema

### Relationships
```
User ──────── hasMany ──────→ StageAttempt
Stage ─────── hasMany ──────→ Question
Stage ─────── hasMany ──────→ StageAttempt
StageAttempt ─ belongsTo ───→ User
StageAttempt ─ belongsTo ───→ Stage
StageAttempt ─ hasMany ─────→ AttemptAnswer
AttemptAnswer ─ belongsTo ──→ Question
```

### Tables
| Table               | Key Fields                                                                                      |
| ------------------- | ----------------------------------------------------------------------------------------------- |
| **users**           | name, email, password, is_admin, total_points, stars                                            |
| **stages**          | title, description, order, time_limit_minutes, passing_percentage, points_reward                |
| **questions**       | stage_id, question_text, option_a/b/c/d, correct_answer, difficulty                             |
| **stage_attempts**  | user_id, stage_id, score, total_questions, passed, time_spent_seconds, started_at, completed_at |
| **attempt_answers** | stage_attempt_id, question_id, selected_answer, is_correct                                      |
| **notifications**   | Laravel built-in notifications table                                                            |

---

## 🔒 Stage Unlock Logic

```
Stage 1 (Atomic Structure)    → Always unlocked
Stage 2 (Chemical Bonding)    → Unlocked when Stage 1 passed (≥75%)
Stage 3 (Reactions & Equations)→ Unlocked when Stage 2 passed
Stage 4 (Acids, Bases & pH)   → Unlocked when Stage 3 passed
Stage 5 (Organic Chemistry)   → Unlocked when Stage 4 passed
```

Implemented in `app/Models/Stage.php` → `isUnlockedFor(User $user)`

---

## 🌍 Globalization & Localization (100% Bilingual Support)

The platform achieves **Zero Hardcoded Strings**, fully localizing every user-facing interaction into **English (EN)** and **Arabic (AR)**, featuring deep RTL (Right-to-Left) UI support.

- **Automated Routing & Preferences**: Language detection dynamically swaps system context (`app()->getLocale()`) driven by user preference or URL parameters.
- **Zero Hardcoded Controller/Service Logic**: Alert messages, validations, and custom exceptions organically route through Laravel's `__()` helper dynamically (e.g., `__('admin.student_deleted', ['name' => $user->name])`).
- **Comprehensive View Localization**: Every Blade interface, from Breeze authentication to complex `Alpine.js` planner components and dashboard SVG rendering, draws from isolated domain arrays.
- **Dynamic Localized Push Notifications**: FCM (Firebase Cloud Messaging) background jobs proactively fetch the target student's preferred locale (`$notifiable->locale`) when broadcasting motivational pushes or scheduled daily reminders.
- **Nested Domain Structuring**: Translation files efficiently scope huge language maps (`lang/en/planner.php`, `lang/en/admin.php`, `lang/ar/notifications.php`), preventing key pollution.

---

## 📝 Diverse Question Formats

To ensure comprehensive testing, the platform supports varied question types:
- **Multiple Choice Questions (MCQ)**: Configurable with dynamic answer shuffling.
- **Short & Long Essay Questions**: Advanced keyword-based grading algorithm allows text-based answers and mathematical formulas instead of just multiple choice.
- **Image Support**: Questions can now confidently host image uploads from Admins, enabling visual diagrams, complex equations, and chemical structure tests.

---

## 👨‍🏫 Advanced Admin Analytics & Management

The admin panel offers deep insights and administrative controls:
- **Detailed Student Profiles**: View performance over time, success rates by stage, and total time spent learning.
- **Moderation Tools**: Safely soft-delete accounts, temporarily ban misbehaving users, and trigger instant password resets.
- **Analytics Optimized**: Heavily aggregated DB queries ensure dashboard stat calculations scale performantly even with thousands of student attempts.

---

## 🌐 SEO & Social Sharing

Fully optimized for external discovery and link sharing:
- **Rich Meta Tags**: Implementation of dynamic Open Graph and Twitter Card markup across stage, quiz, and result pages.
- **Social Previews**: Generates branded 1200x630 social fallback images ensuring visual consistency on platforms like Twitter and WhatsApp.
- **SEO Ready**: Responsive page-specific titles, compelling meta descriptions, and clean semantic structures.

---

## 📅 Weekly Flexible Study Planner

A dedicated weekly planning system that allows students to customize their learning schedule.

- **Manual Assignment**: Students can assign specific "Study" and "Test" days for each of the 5 main stages.
- **Live Progress Syncing**: Completing a stage quiz automatically marks the corresponding "Test" day as completed in the planner.
- **Visual Schedule**: Clear weekly view with status indicators (Planned, Completed, Passed).
- **Auto-Initialization**: New accounts are automatically pre-configured with a default 5-week study track.
- **Implementation**: Powered by `WeeklyStudyPlan` and `WeeklyStudyPlanDay` models with a dedicated `ProgressSyncService`.

---

## ⏱ Timer System (Alpine.js)

The quiz page uses an Alpine.js countdown timer that:
- Starts when quiz page loads
- Shows countdown in `MM:SS` format
- Changes color: green → amber → red (< 60s)
- Has an animated time progress bar
- **Auto-submits** the quiz form when time reaches zero

```javascript
function quizTimer(totalSeconds) {
    return {
        remaining: totalSeconds,
        total: totalSeconds,
        init() {
            this.interval = setInterval(() => {
                this.remaining--;
                if (this.remaining <= 0) {
                    clearInterval(this.interval);
                    document.getElementById('quiz-form').submit();
                }
            }, 1000);
        },
        get display() {
            const m = Math.floor(this.remaining / 60);
            const s = this.remaining % 60;
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        get timePercent() {
            return Math.max(0, (this.remaining / this.total) * 100);
        }
    };
}
```

---

## 🏅 Gamification Rules

| Event                   | Points    | Stars    | Notification            |
| ----------------------- | --------- | -------- | ----------------------- |
| Pass stage (first time) | +100      | +1       | "🎉 You passed {stage}!" |
| Pass stage (retry)      | +50       | —        | "Great job retrying!"   |
| Perfect score (100%)    | +50 bonus | +1 bonus | "⭐ Perfect score!"      |
| Fail stage              | —         | —        | "Keep trying!"          |
| Next stage unlocked     | —         | —        | "🔓 Stage unlocked!"     |

---

## 🛣 Routes

### Student Routes (auth required)
| Method | URI                        | Controller                  | Description       |
| ------ | -------------------------- | --------------------------- | ----------------- |
| GET    | /dashboard                 | DashboardController@index   | Student dashboard |
| GET    | /stages                    | StageController@index       | Stage roadmap     |
| GET    | /stages/{stage}            | StageController@show        | Stage detail      |
| POST   | /stages/{stage}/quiz/start | QuizController@start        | Start quiz        |
| GET    | /quiz/{attempt}            | QuizController@show         | Quiz page + timer |
| POST   | /quiz/{attempt}/submit     | QuizController@submit       | Submit answers    |
| GET    | /quiz/{attempt}/result     | QuizController@result       | View results      |
| GET    | /leaderboard               | LeaderboardController@index | Top students      |

### Admin Routes (/admin, admin middleware)
| Method   | URI                             | Controller                     | Description      |
| -------- | ------------------------------- | ------------------------------ | ---------------- |
| GET      | /admin/dashboard                | AdminDashboardController@index | Admin overview   |
| Resource | /admin/stages                   | AdminStageController           | Stage CRUD       |
| Resource | /admin/stages/{stage}/questions | AdminQuestionController        | Question CRUD    |
| GET      | /admin/students                 | AdminStudentController@index   | Student progress |

---

## 📚 Seeded Content

### 5 Chemistry Stages
1. **Atomic Structure** — 10 questions, 10 min, +100 pts
2. **Chemical Bonding** — 10 questions, 12 min, +120 pts
3. **Reactions & Equations** — 10 questions, 15 min, +140 pts
4. **Acids, Bases & pH** — 10 questions, 12 min, +150 pts
5. **Organic Chemistry** — 10 questions, 15 min, +200 pts

### 50 Real Chemistry Questions
Mixed difficulty (easy/medium/hard) covering:
- Atomic number, electron configuration, isotopes
- Ionic/covalent/metallic bonds, electronegativity
- Balancing equations, reaction types, stoichiometry
- pH scale, neutralization, buffers
- Hydrocarbons, functional groups, IUPAC naming

---

## 🎨 UI Design

- **Dark gradient theme** (slate-900 → purple-900)
- **Glassmorphism** cards with backdrop-blur
- **Reorganized Navigation**: Main academic links (Stages, Planners) are grouped under a single **Dashboard Dropdown** for a cleaner desktop experience.
- **Quick-Action "Learning Hub"**: A horizontal quick-links bar on the dashboad for immediate access to core tools.
- **Standardized Iconography**: Powered by a centralized `x-icon` component using **Heroicons 2.0** outlines.
- **Responsive** design (mobile-first)
- **Animated** progress bars and transitions
- **Notification bell** with unread count badge

### Security & Performance
- **N+1 Query Prevention**: Strict lazy-loading prevention enabled during testing to guarantee massive performance loops aren't accidentally committed.
- **XSS Protection**: Secure sanitization enabled on essay and quiz generation inputs.
- **Login Rate Limiting**: Enforces strict throttling on failed Breeze authentication attempts.
- **SystemGuard Defense**: Secure middleware licensing and application health checking proxy.
- **PgBouncer Compatibility**: Fully hardened for PostgreSQL connection pooling (Supabase) via emulated prepares and strict type handling.
- **Robust Exception Handling**: Controller-wide `try-catch` architecture ensuring safe error logging and user feedback without swallowing essential Laravel `ValidationException` and `HttpException` lifecycle events.

---

## ⚡ Production-Grade Hybrid Caching System

ChemTrack uses a **hybrid cache** tuned for **single-instance** hosting (e.g. Render free tier) and **low Redis command volume** (e.g. Upstash ~500K commands/month). The goal is: **minimal Postgres load**, **very few Redis round-trips**, and **no thundering herd** when shared cache entries expire.

### End-to-end lookup order

Every global (non–per-user) value resolved through `TwoLayerCache` follows this pipeline:

1. **Request micro-cache** — `MemoryCache::$requestCache` (same HTTP request only; deduplicates repeated lookups).
2. **Process memory cache** — `MemoryCache` static store (same PHP worker across requests until TTL expires).
3. **Redis** — Laravel `Cache` store (shared; optional SWR metadata wrapper).
4. **Database** — callback runs only on a full miss, behind a **stampede lock** when the store supports it.

```
┌──────────────────────────────────────────────────────────────────────┐
│  Request Cache (static array, per-request)                          │
│  └─ Zero-cost dedup — same key, same request = instant return       │
├──────────────────────────────────────────────────────────────────────┤
│  Memory Cache (static array, per-process)                           │
│  └─ Survives across requests on the same PHP worker                 │
├──────────────────────────────────────────────────────────────────────┤
│  Redis Cache (shared, with SWR metadata)                            │
│  └─ Stale-While-Revalidate: serves expired data while refreshing   │
├──────────────────────────────────────────────────────────────────────┤
│  Database (source of truth, behind stampede lock)                    │
│  └─ Atomic lock prevents thundering herd on cache miss              │
└──────────────────────────────────────────────────────────────────────┘
```

### Implementation map (source files)

| File | Role |
| ---- | ---- |
| [`app/Support/MemoryCache.php`](app/Support/MemoryCache.php) | Request micro-cache + per-process TTL store; `flushRequestCache()` per HTTP request |
| [`app/Support/TwoLayerCache.php`](app/Support/TwoLayerCache.php) | Request → memory → Redis → DB; stampede lock; optional SWR wrapper; observability logs |
| [`app/Support/CacheTTL.php`](app/Support/CacheTTL.php) | Normalized Redis / memory / stale-window constants (static / semi / dynamic / user-only) |
| [`app/Support/StageSchemaCache.php`](app/Support/StageSchemaCache.php) | Schema version bump so `all_stages:v{n}` and related keys rotate without many Redis deletes |
| [`app/Jobs/RefreshCacheJob.php`](app/Jobs/RefreshCacheJob.php) | Optional queued refresh for **serializable** rebuild callbacks (not Closures) |
| [`app/Http/Middleware/FlushRequestCache.php`](app/Http/Middleware/FlushRequestCache.php) | Prepends web stack — clears `MemoryCache::$requestCache` each request (`bootstrap/app.php`) |

### Example: global key with full hybrid + SWR + stampede protection

```php
use App\Support\CacheTTL;
use App\Support\TwoLayerCache;

$students = TwoLayerCache::remember(
    'leaderboard_data',
    CacheTTL::DYNAMIC_REDIS,    // fresh window in Redis
    CacheTTL::DYNAMIC_MEMORY, // hot path: repeat requests hit memory, not Redis
    fn () => User::student()
        ->orderByDesc('total_points')
        ->orderByDesc('stars')
        ->take(50)
        ->get(),
    CacheTTL::DYNAMIC_STALE,    // serve stale briefly; refresh after response (Closure → terminating)
);
```

With **`$staleWindow = 0`**, behavior is **hard TTL** (no `expires_at` / `stale_until` wrapper) — backward compatible for keys that should disappear from Redis exactly when expired.

### Redis command budget (500K / month tiers)

On a **memory hit** or **request hit**, Redis is **not** contacted. Typical costs when a key is **cold** at the Redis layer:

- At least **one** `GET` (read-through).
- On miss: **lock** acquisition/release (extra commands on Redis) + **one** `SET`/`PUT` to store the new value.
- SWR extends the stored TTL to `redisTtl + staleWindow` so you avoid churn from ultra-short keys.

**Design goal**: long Redis TTLs for static/semi-static data, short **memory** TTLs to bound RAM, and **per-user** data **only** in `MemoryCache` so leaderboard/stages do not share Redis quota with every student.

### Request-level micro-caching (same-request deduplication)

- **Implementation**: `app/Support/MemoryCache.php` — `get()` checks `MemoryCache::$requestCache` first, then the TTL-backed process store. Successful memory hits are **promoted** into the request cache for the rest of the request.
- **Isolation**: `app/Http/Middleware/FlushRequestCache.php` calls `MemoryCache::flushRequestCache()` at the **start** of each web request (registered in `bootstrap/app.php`), so request-scoped data never leaks between requests on the same worker.

```php
// Per-user aggregates: memory-only (no Redis commands)
MemoryCache::remember('user_'.$user->id.'_dashboard_stats', CacheTTL::USER_MEMORY, function () {
    return /* single aggregate query */;
});
```

### Cache stampede (thundering herd) protection

On a **full miss** (nothing usable in memory or Redis), `TwoLayerCache` rebuilds via `computeWithStampedeProtection()`:

- Uses `Cache::lock("{$key}:lock", 10)->block(3, $callback)` when the cache store implements `LockProvider` (Redis does).
- **One** request recomputes; others wait up to **3 seconds**, then fall back to running the callback if the lock cannot be acquired (logged as `cache_lock_timeout`).

This applies to **all** expensive keys that go through `TwoLayerCache::remember()` (e.g. leaderboard, `all_stages`, question ID lists).

### Stale-while-revalidate (SWR)

Optional **fifth argument** to `TwoLayerCache::remember($key, $redisTtl, $memoryTtl, $callback, $staleWindow)`:

- When `$staleWindow > 0`, Redis stores a **wrapped** payload:

  | Field | Meaning |
  | ----- | ------- |
  | `value` | The cached payload (e.g. Eloquent collection) |
  | `expires_at` | Unix timestamp — end of **fresh** window |
  | `stale_until` | Unix timestamp — end of **stale** window (may serve stale, refresh in background) |

- **Fresh** (`now < expires_at`): return `value` immediately.
- **Stale but acceptable** (`expires_at <= now < stale_until`): return `value` immediately, log `cache_stale`, trigger **background** refresh (non-blocking for the user).
- **Too stale** (`now >= stale_until`): treat as expired; drop from memory and rebuild (with stampede lock).

**Background refresh**:

- **Closures** cannot be queued; refresh runs in `app()->terminating()` after the response is sent (see `TwoLayerCache::triggerBackgroundRefresh()`).
- **Serializable callables** can use `app/Jobs/RefreshCacheJob.php` when you wire them that way.
- In-process dedup: `RefreshCacheJob::isDispatched()` avoids flooding refreshes for the same key.

Pass **`$staleWindow = 0`** (default) for **hard TTL** — no SWR wrapper (backward compatible).

### Normalized TTL strategy (`CacheTTL`)

All tier lengths live in `app/Support/CacheTTL.php` — **no scattered magic numbers**. Policy matches the product intent:

| Tier | Volatility | Redis (`*_REDIS`) | Memory (`*_MEMORY`) | Stale (`*_STALE`) | Typical keys |
| ---- | ---------- | ----------------- | ------------------- | ----------------- | ------------ |
| **Static** | Rarely changes (stages / schema) | 6 hours | 10 min | 30 min | `all_stages:v{n}` |
| **Semi-dynamic** | Occasional (question IDs, counts) | 30 min | 5 min | 10 min | `stage_{id}_question_ids:v{n}`, `all_stages_with_count:v{n}` |
| **Dynamic / global** | Changes often (leaderboard) | 5 min | 1 min | 2 min | `leaderboard_data`, `leaderboard_json_data` |
| **Per-user** | Per student | *none* | 30 min (`USER_MEMORY`) | — | `user_{id}_dashboard_*` (memory-only by design) |

**Rules of thumb**:

- Avoid **short Redis TTLs** on hot keys — they multiply `GET`/`SET` commands.
- **Per-user** data stays **out of Redis** to respect strict quotas and keep semantics simple on one instance.

### Schema versioning (invalidation without Redis key spam)

Stage/question mutations bump `StageSchemaCache` so keys like `all_stages:v{n}` rotate — old entries expire naturally instead of many `forget()` calls.

### Observability (logging)

Structured logs help you see **which layer** served data and when Redis/DB were touched:

| Event | Level | Payload |
| ----- | ----- | ------- |
| Request cache hit | `debug` | `cache_hit`, `layer` => `request`, `key` |
| Memory cache hit | `debug` | `cache_hit`, `layer` => `memory`, `key` |
| Redis hit | `info` | `cache_hit`, `layer` => `redis`, `key` |
| Full miss (DB rebuild) | `info` | `cache_miss`, `layer` => `database`, `key` |
| Serving stale (SWR) | `info` | `cache_stale`, `layer` => `swr`, `key` |
| Background refresh | `debug` | `cache_refresh`, `key` |
| Lock wait failed / timeout | `warning` | `cache_lock_timeout`, `key` |

In **production**, set `LOG_LEVEL=info` to keep request/memory hits quiet while still seeing Redis hits, DB misses, and SWR events. Use `debug` temporarily when tuning cache efficiency or estimating Redis command volume.

### Single-instance resilience (Render)

- **Process memory** is cleared when the dyno restarts or the PHP worker recycles; **Redis** (if configured) survives and repopulates memory on first hit.
- **Request cache** is always empty at the start of each request — safe after sleep/wake.

### Tests

Unit coverage lives in:

- `tests/Unit/MemoryCacheTest.php` — request dedup, TTL, flush, forget
- `tests/Unit/TwoLayerCacheTest.php` — hybrid flow, SWR, stampede behavior (with fakes)
- `tests/Unit/CacheTTLTest.php` — TTL tier consistency

Run: `./vendor/bin/pest tests/Unit/MemoryCacheTest.php tests/Unit/TwoLayerCacheTest.php tests/Unit/CacheTTLTest.php`

Together these files define **38** focused unit tests (request dedup, TTL tiers, SWR, stampede locking, and backward compatibility). Broader HTTP-level baselines live under `tests/Feature/Performance/` (query counts / timing for dashboard, quiz, reminders, weekly planner).

### Key design decisions (constraints preserved)

- **Hybrid flow preserved**: request → memory → Redis → DB, with stampede lock on DB rebuild.
- **Redis usage minimized**: memory fronting + long Redis TTLs on static/semi-static data; no per-user Redis cache for dashboard fragments.
- **SWR is opt-in** via `staleWindow`; default `0` keeps previous “hard expiry” behavior.
- **Closures** refresh after response via `terminating()`; queue job path exists for serializable rebuilders.

---

## 🛡️ Anti-Cheat Security System

The platform includes a robust, multi-layered anti-cheat system to ensure quiz integrity:

- **Window Departure Tracking**: 
    - Uses the **Page Visibility API** to detect when a student leaves the quiz tab or minimizes the browser.
    - **Three-Strike System**: Students receive on-screen warnings for the first two violations. On the third violation, the quiz is **automatically submitted** immediately to prevent external searching.
- **Input & Interaction Blocking**:
    - **Copy/Paste/Cut Disabled**: Prevents students from copying questions to search engines or pasting AI-generated answers.
    - **Right-Click (Context Menu) Disabled**: Blocks browser inspection and "Search Google for..." shortcuts.
- **UI Hardening**:
    - **Text Selection Disabled**: Question text is non-selectable (`select-none`) across all devices to prevent quick copying.
    - **Mobile Protection**: Disables "Pull-to-Refresh" and long-press callouts on iOS/Android to prevent accidental or intentional reloads and lookups.
- **Real-Time Enforcement**: Integrated with a custom **Global Toast System** that provides immediate feedback on violations with persistent 10-second warnings.
- **Prevention Loopholes**: Securely bypasses browser "Leave site?" prompts during automatic submissions to ensure enforcement cannot be canceled by the student.

---

## ⚙️ Production Stability (PostgreSQL)

Hardened for high-availability production environments using **Supabase** and **PgBouncer**:

- **Transaction Mode Compatibility**: Configured with `PDO::ATTR_EMULATE_PREPARES` and disabled server-side prepared statements to support PgBouncer connection pooling.
- **Strict Data Casting**: Implements a custom `PostgresBoolean` cast to resolve boolean vs. integer comparison errors (`boolean = 1`) common in strict PostgreSQL environments.
- **Schema Resilience**: Finalized migrations to ensure all long-form answer columns (`text`) support complex scientific expressions and essay responses without truncation.

---

## 🧪 Testing & Quality Assurance

Automated CI architecture powered by **Pest PHP** yielding > 95% total code coverage.

- **Feature Tests**: Validates full authorization flow, Admin CRUD, gamification boundaries, soft deletes.
- **Quiz Flow Tests**: Examines concurrent attempt blockers, auto-saving logic, countdown timers, and accurate score calculations.
- **Unit/Service Tests**: Checks complex gamification equations via `AIQuestionService` mock endpoints (`Http::fake`).
- **Architecture Tests**: Ensures `dd()`, `dump()`, and `ray()` never make it to production environments.

Run the test suite seamlessly:
```bash
./vendor/bin/pest
```

---

## 🚀 CI/CD & Deployment

- **Unified Mega-Pipeline (`pipeline.yml`)**: A strictly prioritized GitHub Actions pipeline:
  1. **Code Formatting**: Auto-runs Laravel Pint and commits style fixes back to the branch.
  2. **Security & Quality**: Checks for Composer dependency vulnerabilities.
  3. **Application Testing**: Runs highly parallelized Pest PHP test suites.
  4. **Docker Deploy**: If all tests pass on `main`, it builds a multi-stage Docker image, pushes to GHCR, triggers Render deployment, and sends Telegram alert notifications on success or failure.
- **Dependabot**: Configured to autonomously monitor and propose weekly updates across Composer, NPM, and GitHub Actions dependencies.

---

## Tech Stack

| Layer         | Technology                                  |
| ------------- | ------------------------------------------- |
| Backend       | Laravel 12.54.1                             |
| Frontend      | Blade + TailwindCSS + Alpine.js             |
| Globalization | Full EN/AR Localization + RTL               |
| Database      | SQLite / PostgreSQL (Supabase)              |
| Caching       | Hybrid 4-Layer (Request → Memory → Redis → DB) |
| Auth          | Laravel Breeze                              |
| Testing       | Pest PHP v3.8.6                             |
| Icons         | Centralized Heroicons 2.0 (SVG)             |
| Security      | Anti-Cheat 2.0 (Visibility API)             |
| Notifications | Global Toast System (Alpine.js)             |
| CI/CD         | GitHub Actions + Docker Multi-stage         |

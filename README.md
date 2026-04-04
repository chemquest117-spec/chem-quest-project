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
│   │   │   └── StageController.php                 # Stage list & detail
│   │   └── Middleware/
│   │       └── AdminMiddleware.php                 # Admin route protection
│   ├── Models/
│   │   ├── AttemptAnswer.php                       # Individual question response
│   │   ├── Question.php                            # MCQ with randomized scope
│   │   ├── Stage.php                               # Stage with unlock logic
│   │   ├── StageAttempt.php                        # Quiz attempt record
│   │   └── User.php                                # Extended with gamification
│   └── Notifications/
│       └── StageCompleted.php                      # Database notification
├── bootstrap/
│   └── app.php                                     # Admin middleware registered
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

## 🌍 Globalization & Localization (Bilingual support)

The platform is fully localized into **English (EN)** and **Arabic (AR)** with high-quality translations and **RTL (Right-to-Left)** support.

- **Automated Routing**: Language detection and switching via URL or user preference.
- **Localized UI**: Every interface from authentication to complex quiz results is fully translated.
- **RTL Support**: The layout dynamically adjusts for Arabic users, ensuring a natural reading and interaction flow.
- **Translation Keys**: Implemented using Laravel's `__()` helper and modular language files (`lang/en`, `lang/ar`).

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

- **GitHub Actions (`laravel.yml`)**: Automated workflows run Pest logic and Node asset builds on every code push to track regression.
- **Multi-stage Dockerfile**: Ready for robust PaaS deployment, cleanly chaining `Node.js 18` Vite builds directly into `PHP 8.2 Apache` images for minimal payload size. Included extensions like `bcmath`.

---

## Tech Stack

| Layer         | Technology                      |
| ------------- | ------------------------------- |
| Backend       | Laravel 12.54.1                 |
| Frontend      | Blade + TailwindCSS + Alpine.js |
| Globalization | Full EN/AR Localization + RTL   |
| Database      | SQLite / PostgreSQL (Supabase)  |
| Auth          | Laravel Breeze                  |
| Testing       | Pest PHP v3.8.6                 |
| Icons         | Centralized Heroicons 2.0 (SVG) |
| Security      | Anti-Cheat 2.0 (Visibility API) |
| Notifications | Global Toast System (Alpine.js) |
| CI/CD         | GitHub Actions + Docker Multi-stage |

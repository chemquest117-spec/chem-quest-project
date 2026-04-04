<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Force-fixing character(1) issues in Postgres by using raw SQL with explicit casts.
     */
    public function up(): void
    {
        if (config('database.default') === 'pgsql') {
            // Force change to TEXT for postgres
            DB::statement('ALTER TABLE attempt_answers ALTER COLUMN selected_answer TYPE TEXT USING selected_answer::text');
            DB::statement('ALTER TABLE questions ALTER COLUMN correct_answer TYPE TEXT USING correct_answer::text');
        } else {
            // For other drivers (sqlite/mysql), try standard change()
            Schema::table('attempt_answers', function (Blueprint $table) {
                $table->text('selected_answer')->nullable()->change();
            });

            Schema::table('questions', function (Blueprint $table) {
                $table->text('correct_answer')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ... reverse logic omitted as this is a fix migration, falling back to char(1) if ever needed
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->char('selected_answer', 1)->change();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->char('correct_answer', 1)->change();
        });
    }
};

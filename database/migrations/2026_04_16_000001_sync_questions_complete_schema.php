<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure questions table supports complete-question schema.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('questions', 'expected_answers')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->json('expected_answers')->nullable()->after('expected_answer_ar');
            });
        }

        if (Schema::hasColumn('questions', 'type')) {
            // Convert any legacy essay records.
            DB::table('questions')->where('type', 'essay')->update(['type' => 'complete']);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type::text = ANY (ARRAY['mcq'::text, 'complete'::text]))");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('questions', 'type')) {
            DB::table('questions')->where('type', 'complete')->update(['type' => 'essay']);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type::text = ANY (ARRAY['mcq'::text, 'essay'::text]))");
        }

        if (Schema::hasColumn('questions', 'expected_answers')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('expected_answers');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refactor essay questions to "complete" (fill-in-the-blank numeric).
     *
     * - Add expected_answers (JSON) for multi-blank support: [{"value": -2, "tolerance": 0}]
     * - Convert existing essay rows to complete type.
     * - Update the type column to support 'complete' instead of 'essay'.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('questions', 'expected_answers')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->json('expected_answers')->nullable()->after('expected_answer_ar');
            });
        }

        // For PostgreSQL, use a temporary permissive check during data conversion.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type IS NULL OR type::text = ANY (ARRAY['mcq'::text, 'essay'::text, 'complete'::text]))");
        }

        // Migrate existing essay questions: extract numeric values and convert to complete
        DB::table('questions')->where('type', 'essay')->chunkById(100, function ($questions) {
            foreach ($questions as $question) {
                $answers = null;
                if ($question->expected_answer !== null) {
                    // Try to extract all numeric values from the expected_answer text
                    $text = str_replace(['−', '–', '—'], '-', $question->expected_answer);
                    if (preg_match_all('/[+-]?\d+(?:\.\d+)?/', $text, $matches)) {
                        $answers = array_map(function ($val) {
                            return ['value' => (float) $val, 'tolerance' => 0];
                        }, $matches[0]);
                    }
                }

                DB::table('questions')->where('id', $question->id)->update([
                    'type' => 'complete',
                    'expected_answers' => $answers ? json_encode($answers) : null,
                ]);
            }
        });

        // Normalize any unexpected/null type values before final strict constraint.
        DB::table('questions')
            ->whereNull('type')
            ->orWhereNotIn('type', ['mcq', 'complete'])
            ->update(['type' => 'mcq']);

        // Tighten PostgreSQL check constraint to final allowed values.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type::text = ANY (ARRAY['mcq'::text, 'complete'::text]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For PostgreSQL, restore legacy check BEFORE data conversion.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions DROP CONSTRAINT IF EXISTS questions_type_check');
            DB::statement("ALTER TABLE questions ADD CONSTRAINT questions_type_check CHECK (type::text = ANY (ARRAY['mcq'::text, 'essay'::text]))");
        }

        // Convert complete questions back to essay
        DB::table('questions')->where('type', 'complete')->update(['type' => 'essay']);

        if (Schema::hasColumn('questions', 'expected_answers')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('expected_answers');
            });
        }
    }
};

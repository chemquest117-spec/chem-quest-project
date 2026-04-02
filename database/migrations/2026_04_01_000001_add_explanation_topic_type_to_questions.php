<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add explanation, topic, type, and expected_answer columns to questions table.
     * These support the new LO-based question structure with explanations shown after submission.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('type', ['mcq', 'essay'])->default('mcq')->after('question_text_ar');
            $table->string('topic')->nullable()->after('difficulty_ar');
            $table->string('topic_ar')->nullable()->after('topic');
            $table->text('explanation')->nullable()->after('topic_ar');
            $table->text('explanation_ar')->nullable()->after('explanation');
            $table->text('expected_answer')->nullable()->after('explanation_ar');
            $table->text('expected_answer_ar')->nullable()->after('expected_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'topic',
                'topic_ar',
                'explanation',
                'explanation_ar',
                'expected_answer',
                'expected_answer_ar',
            ]);
        });
    }
};

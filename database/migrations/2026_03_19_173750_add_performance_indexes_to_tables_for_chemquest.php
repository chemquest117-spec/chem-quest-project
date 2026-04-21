<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('total_points');
            $table->index('stars');
        });

        Schema::table('stage_attempts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('stage_id');
            $table->index('passed');
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->index('stage_attempt_id');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropIndex(['stage_attempt_id']);
            $table->dropIndex(['question_id']);
        });

        Schema::table('stage_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['passed']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['total_points']);
            $table->dropIndex(['stars']);
        });
    }
};

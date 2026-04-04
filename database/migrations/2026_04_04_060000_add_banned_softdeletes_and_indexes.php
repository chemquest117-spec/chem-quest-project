<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing composite indexes, is_banned field, and soft deletes.
     */
    public function up(): void
    {
        // Add is_banned to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('is_admin');
            $table->softDeletes();
            $table->index('last_activity');
        });

        // Add soft deletes and missing indexes to stages
        Schema::table('stages', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to questions
        Schema::table('questions', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('stage_id');
        });

        // Add composite index for common query pattern
        Schema::table('stage_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'stage_id', 'passed'], 'sa_user_stage_passed_idx');
            $table->index('completed_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_banned');
            $table->dropSoftDeletes();
            $table->dropIndex(['last_activity']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['stage_id']);
        });

        Schema::table('stage_attempts', function (Blueprint $table) {
            $table->dropIndex('sa_user_stage_passed_idx');
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['created_at']);
        });
    }
};

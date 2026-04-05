<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_study_plan_days', function (Blueprint $table) {
            $table->string('title', 255)->nullable()->after('action_type');
            $table->time('start_time')->nullable()->after('title');
            $table->time('end_time')->nullable()->after('start_time');
            $table->text('notes')->nullable()->after('end_time');
            $table->string('color', 20)->default('indigo')->after('notes');
        });

        // Drop the unique constraint that limits one study/test per plan
        // We now allow multiple events per plan per day
        Schema::table('weekly_study_plan_days', function (Blueprint $table) {
            $table->dropUnique(['weekly_study_plan_id', 'action_type']);
        });
    }

    public function down(): void
    {
        Schema::table('weekly_study_plan_days', function (Blueprint $table) {
            $table->dropColumn(['title', 'start_time', 'end_time', 'notes', 'color']);
            $table->unique(['weekly_study_plan_id', 'action_type']);
        });
    }
};

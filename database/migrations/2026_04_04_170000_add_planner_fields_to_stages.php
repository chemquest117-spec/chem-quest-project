<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->unsignedInteger('marks_weight')->default(0)->after('points_reward');
            $table->unsignedInteger('estimated_study_minutes')->default(60)->after('marks_weight');
            $table->unsignedTinyInteger('importance_score')->default(5)->after('estimated_study_minutes');
            $table->unsignedTinyInteger('recommended_week')->nullable()->after('importance_score');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn([
                'marks_weight',
                'estimated_study_minutes',
                'importance_score',
                'recommended_week',
            ]);
        });
    }
};

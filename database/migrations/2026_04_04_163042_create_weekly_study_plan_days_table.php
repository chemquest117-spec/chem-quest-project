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
        Schema::create('weekly_study_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_study_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('day_name', ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri']);
            $table->enum('action_type', ['study', 'test']);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Allow only one 'study' or 'test' marker per plan
            $table->unique(['weekly_study_plan_id', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_study_plan_days');
    }
};

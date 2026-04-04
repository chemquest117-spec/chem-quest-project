<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->unsignedInteger('estimated_minutes')->default(60);
            $table->unsignedInteger('marks_weight')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('auto_rescheduled')->default(false);
            $table->unsignedTinyInteger('reschedule_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['study_plan_id', 'scheduled_date']);
            $table->index(['study_plan_id', 'is_completed']);
            $table->index('stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plan_items');
    }
};

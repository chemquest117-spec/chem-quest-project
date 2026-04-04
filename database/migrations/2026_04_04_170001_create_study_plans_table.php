<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('exam_date');
            $table->date('start_date');
            $table->json('preferred_days');
            $table->decimal('hours_per_day', 3, 1)->default(2.0);
            $table->enum('pace', ['light', 'medium', 'intensive'])->default('medium');
            $table->unsignedTinyInteger('total_progress')->default(0);
            $table->enum('status', ['active', 'completed', 'paused', 'expired'])->default('active');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_plans');
    }
};

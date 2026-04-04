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
        Schema::create('weekly_study_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number');
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->timestamps();

            // A user can only have one plan per week/stage?
            // In this manual design, usually we just map Week Number to a single Stage uniquely for a user.
            $table->unique(['user_id', 'week_number']);
            $table->unique(['user_id', 'stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_study_plans');
    }
};

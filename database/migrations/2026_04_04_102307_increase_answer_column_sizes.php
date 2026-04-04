<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('correct_answer')->nullable()->change();
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->text('selected_answer')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->char('correct_answer', 1)->change();
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->char('selected_answer', 1)->change();
        });
    }
};

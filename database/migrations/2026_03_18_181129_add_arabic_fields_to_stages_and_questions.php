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
        Schema::table('stages', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('title');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->text('question_text_ar')->nullable()->after('question_text');
            $table->string('option_a_ar')->nullable()->after('option_a');
            $table->string('option_b_ar')->nullable()->after('option_b');
            $table->string('option_c_ar')->nullable()->after('option_c');
            $table->string('option_d_ar')->nullable()->after('option_d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages_and_questions', function (Blueprint $table) {
            //
        });
    }
};

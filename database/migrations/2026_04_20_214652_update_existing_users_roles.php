<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
        DB::table('users')->where('is_admin', false)->update(['role' => 'student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->string('browser', 100)->nullable()->after('platform');
            $table->string('os', 100)->nullable()->after('browser');
            $table->string('device', 100)->nullable()->after('os');
            $table->string('ip_address', 45)->nullable()->after('device');
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropColumn(['browser', 'os', 'device', 'ip_address']);
        });
    }
};

<?php

use App\Models\DeviceToken;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$token = DeviceToken::first();
if (! $token) {
    echo "ERROR: No device token found in the database. Please open the app in a browser to register one.\n";
    exit;
}

echo 'Testing FCM v1 for token: '.substr($token->token, 0, 20)."...\n";

try {
    $service = app(PushNotificationService::class);
    $result = $service->sendToToken($token->token, 'ChemTrack Test', 'Your real-time notifications are back!');

    if ($result) {
        echo "SUCCESS: Notification sent successfully via FCM v1!\n";
    } else {
        echo "FAILED: Notification failed. Check storage/logs/laravel.log for details.\n";
    }
} catch (Exception $e) {
    echo 'EXCEPTION: '.$e->getMessage()."\n";
}

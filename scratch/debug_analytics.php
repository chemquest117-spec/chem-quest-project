<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\AdminAnalyticsController;
use Illuminate\Contracts\Console\Kernel;

$controller = new AdminAnalyticsController;
$rm = new ReflectionMethod($controller, 'buildAnalyticsPayload');
$rm->setAccessible(true);

try {
    $result = $rm->invoke($controller);
    print_r($result);
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}

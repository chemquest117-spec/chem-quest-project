<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;

$url = env('LICENSE_SERVER_URL');
echo "URL: $url\n";

try {
    $r = Http::timeout(5)->get($url);
    echo 'Status: '.$r->status()."\n";
    echo 'JSON: '.json_encode($r->json())."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

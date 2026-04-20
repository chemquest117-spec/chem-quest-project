<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\AttemptAnswer;
use App\Models\Stage;
use App\Models\StageAttempt;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

echo 'Total Students: '.User::student()->count().PHP_EOL;
echo 'Total Stage Attempts: '.StageAttempt::count().PHP_EOL;
echo 'Total Attempt Answers: '.AttemptAnswer::count().PHP_EOL;

$stageAggregates = StageAttempt::whereNotNull('completed_at')
    ->selectRaw('stage_id, count(*) as total_attempts')
    ->groupBy('stage_id')
    ->get();
echo 'Stage Aggregates Count: '.$stageAggregates->count().PHP_EOL;

$problematicQuestions = AttemptAnswer::select('question_id')
    ->groupBy('question_id')
    ->havingRaw('count(*) >= 2')
    ->get();
echo 'Problematic Questions Count: '.$problematicQuestions->count().PHP_EOL;

$stages = Stage::count();
echo 'Total Stages: '.$stages.PHP_EOL;

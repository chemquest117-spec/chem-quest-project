<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('keeps arabic columns reversible across rollback and migrate', function () {
    expect(Schema::hasColumns('stages', ['title_ar', 'description_ar']))->toBeTrue();
    expect(Schema::hasColumns('questions', [
        'question_text_ar',
        'option_a_ar',
        'option_b_ar',
        'option_c_ar',
        'option_d_ar',
        'correct_answer_ar',
        'difficulty_ar',
    ]))->toBeTrue();

    Artisan::call('migrate:rollback', [
        '--path' => 'database/migrations/2026_03_20_181129_add_arabic_fields_to_stages_and_questions.php',
        '--force' => true,
    ]);

    expect(Schema::hasColumns('questions', ['correct_answer_ar', 'difficulty_ar']))->toBeFalse();
    expect(Schema::hasColumns('stages', ['title_ar', 'description_ar']))->toBeTrue();

    Artisan::call('migrate:rollback', [
        '--path' => 'database/migrations/2026_03_18_181129_add_arabic_fields_to_stages_and_questions.php',
        '--force' => true,
    ]);

    expect(Schema::hasColumns('stages', ['title_ar', 'description_ar']))->toBeFalse();
    expect(Schema::hasColumns('questions', [
        'question_text_ar',
        'option_a_ar',
        'option_b_ar',
        'option_c_ar',
        'option_d_ar',
    ]))->toBeFalse();

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_03_18_181129_add_arabic_fields_to_stages_and_questions.php',
        '--force' => true,
    ]);
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_03_20_181129_add_arabic_fields_to_stages_and_questions.php',
        '--force' => true,
    ]);

    expect(Schema::hasColumns('stages', ['title_ar', 'description_ar']))->toBeTrue();
    expect(Schema::hasColumns('questions', [
        'question_text_ar',
        'option_a_ar',
        'option_b_ar',
        'option_c_ar',
        'option_d_ar',
        'correct_answer_ar',
        'difficulty_ar',
    ]))->toBeTrue();
});

it('enforces expected foreign keys for subscriptions hierarchy', function () {
    $driver = DB::getDriverName();

    if ($driver === 'sqlite') {
        $subscriptionFks = DB::select("PRAGMA foreign_key_list('subscriptions')");
        $itemFks = DB::select("PRAGMA foreign_key_list('subscription_items')");

        expect(collect($subscriptionFks)->pluck('table')->all())->toContain('users');
        expect(collect($itemFks)->pluck('table')->all())->toContain('subscriptions');

        return;
    }

    $subscriptionConstraint = DB::table('information_schema.table_constraints')
        ->where('table_name', 'subscriptions')
        ->where('constraint_type', 'FOREIGN KEY')
        ->exists();

    $itemConstraint = DB::table('information_schema.table_constraints')
        ->where('table_name', 'subscription_items')
        ->where('constraint_type', 'FOREIGN KEY')
        ->exists();

    expect($subscriptionConstraint)->toBeTrue();
    expect($itemConstraint)->toBeTrue();
});

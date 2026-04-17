<?php

use App\Models\DeviceToken;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use App\Notifications\MotivationalNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Http;

// ── MotivationalNotification Class ─────────────────────────────────

it('maps category to correct notification type', function () {
    $cases = [
        'success' => 'success',
        'failure' => 'warning',
        'streak' => 'success',
        'comeback' => 'info',
        'level_up' => 'success',
        'reminder' => 'info',
    ];

    foreach ($cases as $category => $expectedType) {
        $notification = new MotivationalNotification(
            category: $category,
            messageEn: 'Test EN',
            messageAr: 'Test AR',
        );

        $data = $notification->toArray(User::factory()->make());

        expect($data['type'])->toBe($expectedType, "Category '{$category}' should map to type '{$expectedType}'");
        expect($data['category'])->toBe($category);
    }
});

it('includes both en and ar messages in database payload', function () {
    $notification = new MotivationalNotification(
        category: 'success',
        messageEn: 'Great job!',
        messageAr: 'عمل رائع!',
    );

    $data = $notification->toArray(User::factory()->make());

    expect($data['message_en'])->toBe('Great job!');
    expect($data['message_ar'])->toBe('عمل رائع!');
    expect($data['message'])->toBe('Great job!');
});

it('generates correct FCM payload', function () {
    $notification = new MotivationalNotification(
        category: 'success',
        messageEn: 'Great job!',
        messageAr: 'عمل رائع!',
        metadata: ['score' => 9],
    );

    $user = User::factory()->make();
    $fcm = $notification->toFcm($user);

    expect($fcm)->toHaveKeys(['title', 'body', 'data']);
    expect($fcm['data']['category'])->toBe('success');
    expect($fcm['body'])->toBe('Great job!');
});

it('uses database channel by default when no device tokens', function () {
    $user = User::factory()->create();

    $notification = new MotivationalNotification(
        category: 'success',
        messageEn: 'Test',
        messageAr: 'اختبار',
    );

    $channels = $notification->via($user);

    expect($channels)->toBe(['database']);
});

it('includes FCM channel when user has device tokens', function () {
    $user = User::factory()->create();
    DeviceToken::create([
        'user_id' => $user->id,
        'token' => 'test-fcm-token-12345',
        'platform' => 'android',
    ]);

    $notification = new MotivationalNotification(
        category: 'success',
        messageEn: 'Test',
        messageAr: 'اختبار',
    );

    $channels = $notification->via($user);

    expect($channels)->toContain('database');
    expect($channels)->toContain(FcmChannel::class);
});

// ── PushNotificationService ────────────────────────────────────────

it('returns 0 when FCM is disabled', function () {
    config(['services.fcm.enabled' => false]);

    $service = new PushNotificationService;
    $user = User::factory()->create();

    expect($service->sendToUser($user, 'Title', 'Body'))->toBe(0);
});

it('returns 0 when user has no device tokens', function () {
    config(['services.fcm.enabled' => true, 'services.fcm.server_key' => 'test-key']);

    $service = new PushNotificationService;
    $user = User::factory()->create();

    expect($service->sendToUser($user, 'Title', 'Body'))->toBe(0);
});

it('sends push notification to device token via HTTP', function () {
    config(['services.fcm.enabled' => true, 'services.fcm.server_key' => 'test-key']);

    Http::fake([
        'fcm.googleapis.com/*' => Http::response(['success' => 1, 'failure' => 0], 200),
    ]);

    $user = User::factory()->create();
    DeviceToken::create([
        'user_id' => $user->id,
        'token' => 'test-device-token-abc',
        'platform' => 'android',
    ]);

    $service = new PushNotificationService;
    $sent = $service->sendToUser($user, 'Test Title', 'Test Body', ['key' => 'value']);

    expect($sent)->toBe(1);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'fcm.googleapis.com')
            && $request['to'] === 'test-device-token-abc'
            && $request['notification']['title'] === 'Test Title';
    });
});

// ── DeviceToken Registration ───────────────────────────────────────

it('registers a device token for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('device-token.store'), [
            'token' => 'fcm-token-12345',
            'platform' => 'android',
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('device_tokens', [
        'user_id' => $user->id,
        'token' => 'fcm-token-12345',
        'platform' => 'android',
    ]);
});

it('prevents duplicate device tokens', function () {
    $user = User::factory()->create();

    // Register same token twice
    $this->actingAs($user)->postJson(route('device-token.store'), [
        'token' => 'duplicate-token',
        'platform' => 'web',
    ]);
    $this->actingAs($user)->postJson(route('device-token.store'), [
        'token' => 'duplicate-token',
        'platform' => 'web',
    ]);

    expect(DeviceToken::where('token', 'duplicate-token')->count())->toBe(1);
});

it('removes a device token on logout', function () {
    $user = User::factory()->create();
    DeviceToken::create([
        'user_id' => $user->id,
        'token' => 'token-to-remove',
        'platform' => 'ios',
    ]);

    $this->actingAs($user)
        ->deleteJson(route('device-token.destroy'), [
            'token' => 'token-to-remove',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('device_tokens', [
        'token' => 'token-to-remove',
    ]);
});

it('validates device token registration input', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('device-token.store'), [
            'token' => '',
            'platform' => 'invalid',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['token', 'platform']);
});

<?php

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    config([
        'app.url' => 'https://baptist.cloud',
        'services.stripe.member_price' => 'price_member',
        'services.stripe.author_price' => 'price_author',
    ]);
});

function apiToken(User $user, string $device = 'iPhone'): string
{
    return $user->createToken($device)->plainTextToken;
}

function apiSubscribe(User $user, string $price): void
{
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => $price,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function signIn(array $overrides = [], string $ip = '127.0.0.1'): TestResponse
{
    return test()->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/api/v1/auth/token', array_merge([
        'email' => 'pastor@example.com',
        'password' => 'password',
        'device_name' => 'iPhone',
    ], $overrides));
}

test('a verified free user signs in, calls /me, signs out, and the token then stops working (AC-1, AC-5, AC-6, AC-7)', function () {
    $user = User::factory()->create(['email' => 'pastor@example.com', 'avatar' => 'me.jpg']);

    $response = signIn()->assertCreated()->assertJsonPath('user', [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'avatar_url' => 'https://baptist.cloud/storage/avatars/me.jpg',
        'plan' => 'free',
        'has_full_access' => false,
    ]);

    $token = $response->json('token');
    expect($user->tokens()->sole()->name)->toBe('iPhone')
        ->and($user->tokens()->sole()->token)->not->toBe($token)
        ->and($response->json('user'))->not->toHaveKey('email');

    $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertExactJson($response->json('user'));

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->deleteJson('/api/v1/auth/token')->assertNoContent();

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Please sign in again.', 'code' => 'unauthenticated']);
});

test('signing in twice makes two tokens, and signing out one leaves the other working (AC-1, AC-7)', function () {
    User::factory()->create(['email' => 'pastor@example.com']);

    $first = signIn()->json('token');
    $second = signIn()->json('token');
    expect(PersonalAccessToken::count())->toBe(2);

    $this->withToken($first)->deleteJson('/api/v1/auth/token')->assertNoContent();

    $this->app['auth']->forgetGuards();
    $this->withToken($second)->getJson('/api/v1/me')->assertOk();
    expect(PersonalAccessToken::count())->toBe(1);
});

test('the default avatar is an absolute url and members and admins get their plan (AC-5)', function (array $attributes, ?string $price, string $plan) {
    $user = User::factory()->create($attributes);
    if ($price) {
        apiSubscribe($user, $price);
    }

    $this->withToken(apiToken($user))->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('avatar_url', 'https://baptist.cloud/default-avatar.png')
        ->assertJsonPath('plan', $plan)
        ->assertJsonPath('has_full_access', true);
})->with([
    'member' => [[], 'price_member', 'member'],
    'author' => [[], 'price_author', 'author'],
    'admin' => [['is_admin' => true], null, 'admin'],
]);

test('a wrong email and a wrong password get the same 422 and create no token (AC-2)', function () {
    User::factory()->create(['email' => 'pastor@example.com']);

    $wrongEmail = signIn(['email' => 'nobody@example.com'])->assertUnprocessable();
    $wrongPassword = signIn(['password' => 'nope'])->assertUnprocessable();

    expect($wrongEmail->json())->toBe($wrongPassword->json())->toBe([
        'message' => 'These credentials do not match our records.',
        'code' => 'invalid_credentials',
        'errors' => ['email' => ['These credentials do not match our records.']],
    ])->and(PersonalAccessToken::count())->toBe(0);
});

test('missing or too long fields get validation_failed (AC-1, AC-2)', function () {
    signIn(['device_name' => ''])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'validation_failed')
        ->assertJsonPath('message', 'The given data was invalid.')
        ->assertJsonValidationErrors('device_name');

    signIn(['device_name' => str_repeat('a', 101)])->assertJsonValidationErrors('device_name');
});

test('an unverified account gets email_unverified only after the right password (AC-3)', function () {
    User::factory()->unverified()->create(['email' => 'pastor@example.com']);

    signIn()->assertForbidden()->assertExactJson([
        'message' => 'Please verify your email address using the link we sent you.',
        'code' => 'email_unverified',
    ]);
    signIn(['password' => 'nope'])->assertUnprocessable()->assertJsonPath('code', 'invalid_credentials');

    expect(PersonalAccessToken::count())->toBe(0);
});

test('the 6th failure in a minute for one email and IP is throttled and logged (AC-4)', function () {
    Log::spy();
    User::factory()->create(['email' => 'pastor@example.com']);

    foreach (range(1, 5) as $i) {
        signIn(['password' => 'nope'])->assertUnprocessable();
    }

    signIn()->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'Too many sign in attempts. Please try again later.', 'code' => 'too_many_attempts']);

    Log::shouldHaveReceived('warning')->with('API sign in throttled', ['ip' => '127.0.0.1', 'throttle' => 'email_ip']);
});

test('the 21st attempt from one IP across emails is throttled (AC-4)', function () {
    Log::spy();

    foreach (range(1, 20) as $i) {
        signIn(['email' => "person{$i}@example.com"])->assertUnprocessable();
    }

    signIn(['email' => 'person21@example.com'])->assertTooManyRequests();
    Log::shouldHaveReceived('warning')->with('API sign in throttled', ['ip' => '127.0.0.1', 'throttle' => 'ip']);
});

test('the 11th failure for one email across IPs is throttled (AC-4)', function () {
    Log::spy();

    foreach (range(1, 10) as $i) {
        signIn(['password' => 'nope'], "10.0.0.{$i}")->assertUnprocessable();
    }

    signIn([], '10.0.0.11')->assertTooManyRequests();
    Log::shouldHaveReceived('warning')->with('API sign in throttled', ['ip' => '10.0.0.11', 'throttle' => 'email']);
});

test('bad requests and unverified sign ins add no failures, and a success clears them (AC-4)', function () {
    $user = User::factory()->unverified()->create(['email' => 'pastor@example.com']);

    foreach (range(1, 6) as $i) {
        signIn(['device_name' => ''])->assertUnprocessable()->assertJsonPath('code', 'validation_failed');
        signIn()->assertForbidden();
    }

    $user->markEmailAsVerified();
    foreach (range(1, 4) as $i) {
        signIn(['password' => 'nope'])->assertUnprocessable();
    }
    signIn()->assertCreated();

    foreach (range(1, 5) as $i) {
        signIn(['password' => 'nope'])->assertUnprocessable();
    }
    signIn()->assertTooManyRequests();
});

test('a missing or bad token gets JSON 401 with no redirect, whatever the Accept header (AC-6, AC-13)', function () {
    $this->get('/api/v1/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Please sign in again.', 'code' => 'unauthenticated']);

    $this->withToken('1|not-a-real-token')->get('/api/v1/me')->assertUnauthorized();
});

test('a token idle for more than 90 days stops working, one used within 90 days keeps going (AC-8)', function () {
    $user = User::factory()->create();
    $idle = apiToken($user, 'old phone');
    $active = apiToken($user, 'new phone');

    $this->travel(89)->days();
    $this->withToken($active)->getJson('/api/v1/me')->assertOk();
    expect($user->tokens()->where('name', 'new phone')->sole()->last_used_at->isToday())->toBeTrue();

    $this->travel(2)->days();
    $this->app['auth']->forgetGuards();
    $this->withToken($idle)->getJson('/api/v1/me')->assertUnauthorized();

    $this->app['auth']->forgetGuards();
    $this->withToken($active)->getJson('/api/v1/me')->assertOk();
});

test('the daily prune deletes only idle tokens (AC-8)', function () {
    $user = User::factory()->create();
    apiToken($user, 'old phone');
    $this->travel(60)->days();
    apiToken($user, 'new phone');
    $this->travel(31)->days();

    $this->artisan('schedule:test', ['--name' => 'prune-idle-api-tokens'])->assertSuccessful();

    expect($user->tokens()->pluck('name')->all())->toBe(['new phone']);
});

test('changing the password removes every API token (AC-9)', function () {
    $user = User::factory()->create();
    apiToken($user);
    apiToken($user, 'iPad');

    $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasNoErrors();

    expect($user->tokens()->count())->toBe(0);
});

test('resetting the password by email removes every API token (AC-9)', function () {
    $user = User::factory()->create();
    apiToken($user);

    $this->post('/reset-password', [
        'token' => Password::createToken($user),
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasNoErrors();

    expect($user->tokens()->count())->toBe(0);
});

test('deleting the account removes every API token (AC-9)', function () {
    $user = User::factory()->create();
    apiToken($user);

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

    expect(PersonalAccessToken::count())->toBe(0);
});

test('a token whose user is no longer verified gets email_unverified, but can still sign out (AC-7, AC-10)', function () {
    $user = User::factory()->create();
    $token = apiToken($user);
    $user->forceFill(['email_verified_at' => null])->save();

    $this->withToken($token)->getJson('/api/v1/me')->assertForbidden()->assertJsonPath('code', 'email_unverified');

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->deleteJson('/api/v1/auth/token')->assertNoContent();
    expect($user->tokens()->count())->toBe(0);
});

test('full.access refuses free users with plan_required and lets members and admins through (AC-11)', function () {
    Route::middleware(['auth:sanctum', 'full.access'])->get('/api/v1/test-full-access', fn () => ['ok' => true]);

    $free = User::factory()->create();
    $member = User::factory()->create();
    apiSubscribe($member, 'price_member');
    $admin = User::factory()->create(['is_admin' => true]);

    $this->withToken(apiToken($free))->getJson('/api/v1/test-full-access')
        ->assertForbidden()
        ->assertExactJson(['message' => 'A Member or Author plan is needed for this.', 'code' => 'plan_required']);

    foreach ([$member, $admin] as $user) {
        $this->app['auth']->forgetGuards();
        $this->withToken(apiToken($user))->getJson('/api/v1/test-full-access')->assertOk();
    }
});

test('the 121st request in a minute gets too_many_requests (AC-12)', function () {
    $token = apiToken(User::factory()->create());

    foreach (range(1, 120) as $i) {
        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
    }

    $this->withToken($token)->getJson('/api/v1/me')
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'Too many requests. Please slow down.', 'code' => 'too_many_requests']);
});

test('unknown routes, wrong methods, and crashes return the fixed JSON shapes with no detail (AC-13)', function () {
    config(['app.debug' => true]);
    Route::get('/api/v1/test-boom', fn () => throw new RuntimeException('secret detail'));

    $this->get('/api/v1/nope')->assertNotFound()->assertExactJson(['message' => 'Not found.', 'code' => 'not_found']);

    $this->put('/api/v1/me')->assertStatus(405)->assertHeader('Allow')->assertJsonPath('code', 'not_found');

    $boom = $this->get('/api/v1/test-boom')->assertStatus(500)
        ->assertExactJson(['message' => 'Something went wrong on our side.', 'code' => 'server_error']);
    expect($boom->getContent())->not->toContain('secret detail');
});

test('the API sets no cookies and ignores a website session (AC-14)', function () {
    $user = User::factory()->create(['email' => 'pastor@example.com']);

    signIn()->assertCreated()->assertHeaderMissing('Set-Cookie');

    $this->actingAs($user, 'web')->getJson('/api/v1/me')->assertUnauthorized();
});

test('a token idle for exactly 90 days still works, and one second more stops it (AC-8)', function () {
    $this->freezeSecond();
    $token = apiToken(User::factory()->create());

    $this->travel(90)->days();
    $this->withToken($token)->getJson('/api/v1/me')->assertOk();

    // That request moved last_used_at to now, so start the clock again from here.
    $this->travel(90)->days();
    $this->travel(1)->seconds();
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
});

test('the prune keeps a token by its last use, not its age, and keeps one idle exactly 90 days (AC-8)', function () {
    $this->freezeSecond();
    $user = User::factory()->create();
    $oldButUsed = $user->createToken('old but used')->accessToken;
    $user->createToken('never used');
    $this->travel(10)->days();
    $edge = $user->createToken('edge')->accessToken;

    $this->travel(90)->days();
    $oldButUsed->forceFill(['last_used_at' => now()->subDays(10)])->save();
    $edge->forceFill(['last_used_at' => now()->subDays(90)])->save();

    $this->artisan('schedule:test', ['--name' => 'prune-idle-api-tokens'])->assertSuccessful();

    expect($user->tokens()->orderBy('id')->pluck('name')->all())->toBe(['old but used', 'edge']);
});

test('changing the email letter case does not dodge the sign in throttle (AC-4)', function () {
    User::factory()->create(['email' => 'pastor@example.com']);

    foreach (['pastor@example.com', 'PASTOR@example.com', 'Pastor@Example.com', 'pastor@EXAMPLE.COM', 'PaStOr@example.com'] as $email) {
        signIn(['email' => $email, 'password' => 'nope'])->assertUnprocessable();
    }

    signIn(['email' => 'PASTOR@EXAMPLE.COM'])->assertTooManyRequests()->assertJsonPath('code', 'too_many_attempts');
});

test('when two limits block at once, Retry-After is the longest wait and both are logged (AC-4)', function () {
    Log::spy();

    foreach (['10.0.0.1', '10.0.0.2'] as $ip) {
        foreach (range(1, 5) as $i) {
            signIn(['password' => 'nope'], $ip)->assertUnprocessable();
        }
    }

    $response = signIn([], '10.0.0.1')->assertTooManyRequests();

    expect((int) $response->headers->get('Retry-After'))->toBeGreaterThan(60)->toBeLessThanOrEqual(900);
    Log::shouldHaveReceived('warning')->with('API sign in throttled', ['ip' => '10.0.0.1', 'throttle' => 'email_ip']);
    Log::shouldHaveReceived('warning')->with('API sign in throttled', ['ip' => '10.0.0.1', 'throttle' => 'email']);
});

test('blocked sign ins add no hits, so they never use up the per IP limit (AC-4)', function () {
    foreach (range(1, 5) as $i) {
        signIn(['password' => 'nope'])->assertUnprocessable();
    }
    foreach (range(1, 15) as $i) {
        signIn(['password' => 'nope'])->assertTooManyRequests();
    }

    // Only the 5 real attempts count toward the IP limit of 20, so 15 more emails still get through.
    foreach (range(1, 15) as $i) {
        signIn(['email' => "other{$i}@example.com"])->assertUnprocessable()->assertJsonPath('code', 'invalid_credentials');
    }
});

test('a missing or badly formed email or password gets validation_failed and creates no token (AC-2)', function (array $overrides, string $field) {
    User::factory()->create(['email' => 'pastor@example.com']);

    signIn($overrides)
        ->assertUnprocessable()
        ->assertJsonPath('code', 'validation_failed')
        ->assertJsonValidationErrors($field);

    expect(PersonalAccessToken::count())->toBe(0);
})->with([
    'no email' => [['email' => ''], 'email'],
    'not an email' => [['email' => 'pastor'], 'email'],
    'no password' => [['password' => ''], 'password'],
]);

test('a token with a real id but the wrong secret, or a non bearer header, is refused (AC-6)', function (callable $header) {
    $user = User::factory()->create();
    $token = apiToken($user);

    $this->withHeaders(['Authorization' => $header($token)])->getJson('/api/v1/me')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Please sign in again.', 'code' => 'unauthenticated']);
})->with([
    'wrong secret' => [fn (string $token) => 'Bearer '.explode('|', $token)[0].'|'.str_repeat('x', 40)],
    'basic auth' => [fn (string $token) => 'Basic '.base64_encode('pastor@example.com:password')],
    'token without Bearer' => [fn (string $token) => $token],
]);

test('the per user limit is shared across devices but not across users (AC-12)', function () {
    $user = User::factory()->create();
    $phone = apiToken($user, 'iPhone');
    $tablet = apiToken($user, 'iPad');
    $otherUser = apiToken(User::factory()->create());

    foreach (range(1, 60) as $i) {
        $this->withToken($phone)->getJson('/api/v1/me')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($tablet)->getJson('/api/v1/me')->assertOk();
        $this->app['auth']->forgetGuards();
    }

    $this->withToken($tablet)->getJson('/api/v1/me')->assertTooManyRequests()->assertJsonPath('code', 'too_many_requests');

    $this->app['auth']->forgetGuards();
    $this->withToken($otherUser)->getJson('/api/v1/me')->assertOk();
});

test('requests refused with 403 do not count toward the per user limit (AC-12)', function () {
    $user = User::factory()->create();
    $token = apiToken($user);
    $user->forceFill(['email_verified_at' => null])->save();

    foreach (range(1, 125) as $i) {
        $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
    }

    $user->markEmailAsVerified();
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/api/v1/me')->assertOk();
});

test('plan_required refusals do not count toward the per user limit either (AC-11, AC-12)', function () {
    // The `api` group matters: real routes get it from routes/api.php, and it changes how Laravel sorts middleware.
    Route::middleware(['api', 'auth:sanctum', 'api.verified', 'full.access', 'throttle:api-user'])
        ->get('/api/v1/test-plan-limit', fn () => ['ok' => true]);
    $token = apiToken(User::factory()->create());

    foreach (range(1, 125) as $i) {
        $this->withToken($token)->getJson('/api/v1/test-plan-limit')->assertForbidden()->assertJsonPath('code', 'plan_required');
    }
});

test('other errors under /api/ keep their status with the fixed codes (AC-13)', function (Closure $action, int $status, string $code, string $message) {
    config(['app.debug' => true]);
    Route::get('/api/v1/test-error', $action);

    $this->get('/api/v1/test-error', ['Accept' => 'text/html'])
        ->assertStatus($status)
        ->assertExactJson(['message' => $message, 'code' => $code]);
})->with([
    'missing model' => [fn () => throw new ModelNotFoundException, 404, 'not_found', 'Not found.'],
    'other 4xx' => [fn () => abort(418, 'secret teapot'), 418, 'http_error', 'The request could not be handled.'],
    'forbidden' => [fn () => abort(403, 'secret reason'), 403, 'http_error', 'The request could not be handled.'],
    'service unavailable' => [fn () => abort(503, 'secret outage'), 503, 'server_error', 'Something went wrong on our side.'],
]);

test('website errors outside /api/ are left as normal pages (AC-13)', function () {
    $response = $this->get('/this-page-does-not-exist')->assertNotFound();

    expect($response->headers->get('Content-Type'))->toContain('text/html')
        ->and($response->getContent())->not->toContain('"code":"not_found"');
});

test('a password change and an account deletion leave other users\' tokens alone (AC-9)', function () {
    $user = User::factory()->create();
    apiToken($user);
    $bystander = User::factory()->create();
    $bystanderToken = apiToken($bystander);

    $this->actingAs($user)->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasNoErrors();
    $this->actingAs($user)->delete('/profile', ['password' => 'new-password-123'])->assertRedirect('/');

    expect($bystander->tokens()->count())->toBe(1);

    $this->app['auth']->forgetGuards();
    $this->withToken($bystanderToken)->getJson('/api/v1/me')->assertOk();
});

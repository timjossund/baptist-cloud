<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Http;
use RyanChandler\LaravelCloudflareTurnstile\TurnstileClient;

const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

beforeEach(function () {
    $this->app->detectEnvironment(fn () => 'production');
    // CSRF checks are only skipped automatically in the testing environment.
    $this->withoutMiddleware(ValidateCsrfToken::class);

    config([
        'services.turnstile.key' => TurnstileClient::SITEKEY_ALWAYS_PASSES_VISIBLE,
        'services.turnstile.secret' => TurnstileClient::SECRET_KEY_ALWAYS_PASSES,
    ]);
});

function fakeSiteverify(bool $success): void
{
    Http::fake([
        SITEVERIFY_URL => Http::response([
            'success' => $success,
            'error-codes' => $success ? [] : ['invalid-input-response'],
        ]),
    ]);
}

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

test('login and register pages render the widget and explicit-render script in production', function (string $path) {
    $response = $this->get($path);

    $response->assertOk();
    $response->assertSee('class="cf-turnstile"', false);
    $response->assertSee('data-sitekey="'.TurnstileClient::SITEKEY_ALWAYS_PASSES_VISIBLE.'"', false);
    $response->assertSee('turnstile/v0/api.js?render=explicit&onload=renderTurnstileWidgets', false);
    $response->assertSee("addEventListener('livewire:navigated', window.renderTurnstileWidgets)", false);
})->with(['/login', '/register']);

test('login and register pages omit Turnstile outside production', function (string $path) {
    $this->app->detectEnvironment(fn () => 'testing');

    $response = $this->get($path);

    $response->assertOk();
    $response->assertDontSee('cf-turnstile', false);
    $response->assertDontSee('challenges.cloudflare.com', false);
})->with(['/login', '/register']);

test('users can log in with a passing turnstile token', function () {
    fakeSiteverify(true);
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => TurnstileClient::RESPONSE_DUMMY_TOKEN,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home-page', absolute: false));
});

test('login is rejected when turnstile verification fails', function () {
    fakeSiteverify(false);
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'cf-turnstile-response' => 'bad-token',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
});

test('login is rejected without a turnstile token and cloudflare is not called', function () {
    Http::fake();
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
    Http::assertNothingSent();
});

test('users can register with a passing turnstile token', function () {
    fakeSiteverify(true);

    $response = $this->post('/register', registrationPayload([
        'cf-turnstile-response' => TurnstileClient::RESPONSE_DUMMY_TOKEN,
    ]));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    $response->assertRedirect(route('home-page', absolute: false));
});

test('registration is rejected when turnstile verification fails', function () {
    fakeSiteverify(false);

    $response = $this->post('/register', registrationPayload([
        'cf-turnstile-response' => 'bad-token',
    ]));

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    $response->assertSessionHasErrors('cf-turnstile-response');
});

test('registration is rejected without a turnstile token and cloudflare is not called', function () {
    Http::fake();

    $response = $this->post('/register', registrationPayload());

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    $response->assertSessionHasErrors('cf-turnstile-response');
    Http::assertNothingSent();
});

test('login and registration reject a non-string turnstile token without calling cloudflare', function (string $path, array $payload) {
    Http::fake();

    $response = $this->post($path, array_merge($payload, [
        'cf-turnstile-response' => ['not-a-string'],
    ]));

    $this->assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
    Http::assertNothingSent();
})->with([
    'login' => ['/login', ['email' => 'test@example.com', 'password' => 'password']],
    'register' => ['/register', registrationPayload()],
]);

<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Notifications\ConfirmPayment;
use Laravel\Cashier\Payment;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\PaymentIntent;

/**
 * Insert a subscription row shaped like the ones Spark wrote.
 */
function giveSubscription(User $user, array $overrides = []): void
{
    DB::table('subscriptions')->insert(array_merge([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => 'price_author_monthly',
        'quantity' => 1,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

// covers: AC-4, AC-6
test('existing spark subscribers stay subscribed and can run ads', function () {
    $user = User::factory()->create();
    giveSubscription($user);

    expect($user->subscribed())->toBeTrue()
        ->and($user->canRunAds())->toBeTrue();
});

test('a canceled subscription past its end date is not subscribed', function () {
    $user = User::factory()->create();
    giveSubscription($user, ['stripe_status' => 'canceled', 'ends_at' => now()->subDay()]);

    expect($user->subscribed())->toBeFalse()
        ->and($user->canRunAds())->toBeFalse();
});

test('the stripe webhook sits outside the web middleware group and accepts unknown events', function () {
    config(['cashier.webhook.secret' => null]);

    // CSRF is skipped while tests run, so check the route's middleware directly.
    expect(Route::getRoutes()->getByName('cashier.webhook')->gatherMiddleware())->not->toContain('web');

    $this->postJson('/stripe/webhook', ['type' => 'unknown.event', 'data' => ['object' => []]])
        ->assertOk();
});

test('guests are sent to login from billing', function () {
    $this->get('/billing')->assertRedirect('/login');
});

// covers: AC-1
test('unsubscribed users see the member and author plans', function () {
    $this->actingAs(User::factory()->create())
        ->get('/billing')
        ->assertOk()
        ->assertSeeInOrder(['Member', '$15/month', 'Author', '$25/month'])
        ->assertSee('name="plan" value="member"', false)
        ->assertSee('name="plan" value="author"', false)
        ->assertDontSee('$20/month');
});

// covers: AC-2
test('subscribing without a configured price shows an error', function (string $plan) {
    config(['services.stripe.member_price' => null, 'services.stripe.author_price' => null]);

    $this->actingAs(User::factory()->create())
        ->from('/billing')
        ->post('/billing/subscribe', ['plan' => $plan])
        ->assertRedirect('/billing')
        ->assertSessionHas('error', 'Billing is unavailable right now. Please try again.');
})->with(['member', 'author']);

// covers: AC-2
test('an unknown or missing plan fails validation and starts no checkout', function (?string $plan) {
    config(['services.stripe.member_price' => 'price_member', 'services.stripe.author_price' => 'price_author']);

    $this->actingAs(User::factory()->create())
        ->from('/billing')
        ->post('/billing/subscribe', ['plan' => $plan])
        ->assertRedirect('/billing')
        ->assertSessionHasErrors('plan');
})->with(['gold', null]);

// covers: AC-2
test('subscribed users who try to subscribe again go back to billing', function () {
    $user = User::factory()->create();
    giveSubscription($user);

    $this->actingAs($user)
        ->post('/billing/subscribe', ['plan' => 'member'])
        ->assertRedirect('/billing');
});

test('the checkout success page thanks the user', function () {
    $this->actingAs(User::factory()->create())
        ->get('/billing/success')
        ->assertRedirect('/')
        ->assertSessionHas('success');
});

// covers: AC-4, AC-15
test('the admin users table labels subscribers as authors', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $subscriber = User::factory()->create(['name' => 'Paying Subscriber']);
    giveSubscription($subscriber);
    User::factory()->create(['name' => 'Free Reader']);

    $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

    expect($html)->toMatch('/Paying Subscriber.*?Author/s')
        ->and($html)->toMatch('/Free Reader.*?Free/s')
        ->and($html)->not->toMatch('/Free Reader((?!<\/tr>).)*Author/s');
});

test('payment confirmation emails are on by default', function () {
    expect(config('cashier.payment_notification'))->toBe(ConfirmPayment::class)
        ->and(config('cashier.path'))->toBe('stripe');
});

test('the payment confirmation email links to the confirm page', function () {
    $intent = PaymentIntent::constructFrom(['id' => 'pi_test123', 'amount' => 2000, 'currency' => 'usd']);

    $mail = (new ConfirmPayment(new Payment($intent)))->toMail(User::factory()->create());

    expect($mail->subject)->toBe('Confirm Payment')
        ->and($mail->actionUrl)->toBe(url('/stripe/payment/pi_test123'))
        ->and($mail->greeting)->toContain('20.00');
});

/**
 * Stand in for Stripe's HTTP layer: records each request and answers with a canned object.
 */
function fakeStripe(): object
{
    $client = new class implements ClientInterface
    {
        public array $requests = [];

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
        {
            $path = parse_url($absUrl, PHP_URL_PATH);
            $this->requests[] = ['method' => $method, 'path' => $path, 'params' => $params];

            $body = match (true) {
                str_ends_with($path, '/customers') => ['id' => 'cus_fake', 'object' => 'customer'],
                str_ends_with($path, '/checkout/sessions') => ['id' => 'cs_test_fake', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.test/cs_test_fake'],
                str_ends_with($path, '/billing_portal/sessions') => ['id' => 'bps_fake', 'object' => 'billing_portal.session', 'url' => 'https://billing.stripe.test/session'],
                default => ['id' => 'obj_fake', 'object' => 'unknown'],
            };

            return [json_encode($body), 200, []];
        }

        public function paths(): array
        {
            return array_column($this->requests, 'path');
        }

        public function checkoutPrice(): ?string
        {
            $checkout = collect($this->requests)->first(fn ($r) => str_ends_with($r['path'], '/checkout/sessions'));

            return $checkout['params']['line_items'][0]['price'] ?? null;
        }
    };

    ApiRequestor::setHttpClient($client);

    return $client;
}

afterEach(function () {
    ApiRequestor::setHttpClient(null);
});

// covers: AC-2
test('subscribing to a plan opens checkout with that plan\'s configured price', function (string $plan, string $price) {
    config(['services.stripe.member_price' => 'price_member', 'services.stripe.author_price' => 'price_author', 'cashier.secret' => 'sk_test_fake']);
    $stripe = fakeStripe();

    $this->actingAs(User::factory()->create())
        ->post('/billing/subscribe', ['plan' => $plan])
        ->assertRedirect('https://checkout.stripe.test/cs_test_fake');

    expect($stripe->checkoutPrice())->toBe($price);
})->with([
    'member' => ['member', 'price_member'],
    'author' => ['author', 'price_author'],
]);

// covers: AC-2
test('the checkout price always comes from config, never from the request', function () {
    config(['services.stripe.member_price' => 'price_member', 'services.stripe.author_price' => 'price_author', 'cashier.secret' => 'sk_test_fake']);
    $stripe = fakeStripe();

    $this->actingAs(User::factory()->create())
        ->post('/billing/subscribe', ['plan' => 'member', 'price' => 'price_free_forever']);

    expect($stripe->checkoutPrice())->toBe('price_member');
});

// covers: AC-2
test('an invalid plan or an existing subscriber never reaches stripe', function () {
    config(['services.stripe.member_price' => 'price_member', 'cashier.secret' => 'sk_test_fake']);
    $stripe = fakeStripe();
    $subscriber = User::factory()->create();
    giveSubscription($subscriber, ['stripe_price' => 'price_member']);

    $this->actingAs(User::factory()->create())->post('/billing/subscribe', ['plan' => 'gold']);
    $this->actingAs($subscriber)->post('/billing/subscribe', ['plan' => 'author'])->assertRedirect('/billing');

    expect($stripe->requests)->toBe([])
        ->and($subscriber->subscriptions()->count())->toBe(1);
});

// covers: AC-2
test('a stripe error during checkout returns to billing with a friendly message', function () {
    config(['services.stripe.member_price' => 'price_member', 'cashier.secret' => 'sk_test_fake']);
    ApiRequestor::setHttpClient(new class implements ClientInterface
    {
        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
        {
            return [json_encode(['error' => ['message' => 'No such price', 'type' => 'invalid_request_error']]), 400, []];
        }
    });

    $this->actingAs(User::factory()->create())
        ->post('/billing/subscribe', ['plan' => 'member'])
        ->assertRedirect('/billing')
        ->assertSessionHas('error', 'Billing is unavailable right now. Please try again.');
});

// covers: AC-3
test('subscribers opening billing are sent to the stripe portal', function (string $price) {
    config(['services.stripe.member_price' => 'price_member', 'services.stripe.author_legacy_prices' => 'price_author_monthly', 'cashier.secret' => 'sk_test_fake']);
    $stripe = fakeStripe();
    $user = User::factory()->create(['stripe_id' => 'cus_existing']);
    giveSubscription($user, ['stripe_price' => $price]);

    $this->actingAs($user)->get('/billing')->assertRedirect('https://billing.stripe.test/session');

    expect($stripe->requests[0]['params']['customer'])->toBe('cus_existing');
})->with(['member' => 'price_member', 'legacy author' => 'price_author_monthly']);

// covers: AC-3
test('a canceled subscriber still in their paid period goes to the portal to resume', function () {
    config(['cashier.secret' => 'sk_test_fake']);
    fakeStripe();
    $user = User::factory()->create(['stripe_id' => 'cus_existing']);
    giveSubscription($user, ['stripe_status' => 'canceled', 'ends_at' => now()->addDays(10)]);

    $this->actingAs($user)->get('/billing')->assertRedirect('https://billing.stripe.test/session');
});

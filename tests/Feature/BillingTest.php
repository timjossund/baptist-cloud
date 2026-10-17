<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Notifications\ConfirmPayment;
use Laravel\Cashier\Payment;
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

test('the stripe webhook accepts posts without a csrf token', function () {
    config(['cashier.webhook.secret' => null]);

    $this->postJson('/stripe/webhook', ['type' => 'unknown.event', 'data' => ['object' => []]])
        ->assertOk();
});

test('guests are sent to login from billing', function () {
    $this->get('/billing')->assertRedirect('/login');
});

test('unsubscribed users see the author plan', function () {
    $this->actingAs(User::factory()->create())
        ->get('/billing')
        ->assertOk()
        ->assertSee('$20/month')
        ->assertSee('Subscribe');
});

test('subscribing without a configured price shows an error', function () {
    config(['services.stripe.author_price' => null]);

    $this->actingAs(User::factory()->create())
        ->post('/billing/subscribe')
        ->assertRedirect('/billing')
        ->assertSessionHas('error', 'Billing is unavailable right now. Please try again.');
});

test('subscribed users who try to subscribe again go back to billing', function () {
    $user = User::factory()->create();
    giveSubscription($user);

    $this->actingAs($user)
        ->post('/billing/subscribe')
        ->assertRedirect('/billing');
});

test('the checkout success page thanks the user', function () {
    $this->actingAs(User::factory()->create())
        ->get('/billing/success')
        ->assertRedirect('/')
        ->assertSessionHas('success');
});

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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;

class BillingController extends Controller
{
    private const UNAVAILABLE = 'Billing is unavailable right now. Please try again.';

    /**
     * Each plan a user can pick, mapped to its price config key.
     */
    private const PLAN_PRICES = [
        'member' => 'services.stripe.member_price',
        'author' => 'services.stripe.author_price',
    ];

    /**
     * Subscribers manage billing in the Stripe portal; everyone else sees the plan.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user->subscribed()) {
            return view('billing');
        }

        try {
            return $user->redirectToBillingPortal(route('home-page'));
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->route('home-page')->with('error', self::UNAVAILABLE);
        }
    }

    public function subscribe(Request $request)
    {
        $user = auth()->user();

        if ($user->subscribed()) {
            return redirect()->route('billing');
        }

        $data = $request->validate([
            'plan' => ['required', 'in:'.implode(',', array_keys(self::PLAN_PRICES))],
        ]);

        $price = trim((string) config(self::PLAN_PRICES[$data['plan']]));

        if (! $price) {
            return redirect()->route('billing')->with('error', self::UNAVAILABLE);
        }

        try {
            return $user->newSubscription('default', $price)->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing'),
            ]);
        } catch (ApiErrorException $e) {
            report($e);

            return redirect()->route('billing')->with('error', self::UNAVAILABLE);
        }
    }

    public function success()
    {
        return redirect()->route('home-page')->with('success', 'Thanks for subscribing! Your plan will be ready in a moment.');
    }
}

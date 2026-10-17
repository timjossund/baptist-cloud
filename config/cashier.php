<?php

use Laravel\Cashier\Notifications\ConfirmPayment;

// Only the keys the app overrides; Cashier merges in the rest of its defaults.
return [

    // Email subscribers when a renewal needs extra confirmation (3D Secure),
    // as Spark did. Set CASHIER_PAYMENT_NOTIFICATION to override.
    'payment_notification' => env('CASHIER_PAYMENT_NOTIFICATION') ?: ConfirmPayment::class,

];

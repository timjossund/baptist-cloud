<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\Rule;

/**
 * Wraps the package's Turnstile rule so a siteverify outage fails validation instead of throwing.
 */
class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var \RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile $rule */
        $rule = Rule::turnstile();

        try {
            $passes = $rule->passes($attribute, $value);
        } catch (RequestException|ConnectionException $e) {
            report($e);
            $fail(__('cloudflare-turnstile::errors.internal-error'));

            return;
        }

        if ($passes) {
            return;
        }

        $messages = (array) $rule->message();

        if ($messages === []) {
            $fail(__('cloudflare-turnstile::errors.unexpected'));

            return;
        }

        foreach ($messages as $message) {
            $fail($message);
        }
    }
}

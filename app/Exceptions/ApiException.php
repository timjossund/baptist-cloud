<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * An API error with a stable code the phone app can react to (spec 0002, AC-13).
 */
class ApiException extends HttpException
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<string, array<int, string>>|null  $errors
     */
    public function __construct(
        int $status,
        public readonly string $errorCode,
        string $message,
        array $headers = [],
        public readonly ?array $errors = null,
    ) {
        parent::__construct($status, $message, null, $headers);
    }

    public static function invalidCredentials(): self
    {
        $message = 'These credentials do not match our records.';

        return new self(422, 'invalid_credentials', $message, errors: ['email' => [$message]]);
    }

    public static function emailUnverified(): self
    {
        return new self(403, 'email_unverified', 'Please verify your email address using the link we sent you.');
    }

    public static function planRequired(): self
    {
        return new self(403, 'plan_required', 'A Member or Author plan is needed for this.');
    }

    public static function tooManyAttempts(int $retryAfter): self
    {
        return new self(429, 'too_many_attempts', 'Too many sign in attempts. Please try again later.', ['Retry-After' => (string) $retryAfter]);
    }

    /**
     * Map any exception thrown under /api/ to one of the fixed codes and messages.
     * Never carries exception detail into the response; that stays in the log.
     */
    public static function from(Throwable $e): self
    {
        return match (true) {
            $e instanceof self => $e,
            $e instanceof ValidationException => new self(422, 'validation_failed', 'The given data was invalid.', errors: $e->errors()),
            $e instanceof AuthenticationException => new self(401, 'unauthenticated', 'Please sign in again.'),
            $e instanceof ThrottleRequestsException => new self(429, 'too_many_requests', 'Too many requests. Please slow down.', $e->getHeaders()),
            $e instanceof NotFoundHttpException => new self(404, 'not_found', 'Not found.', $e->getHeaders()),
            $e instanceof MethodNotAllowedHttpException => new self(405, 'not_found', 'Not found.', $e->getHeaders()),
            $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500 => new self($e->getStatusCode(), 'http_error', 'The request could not be handled.', $e->getHeaders()),
            $e instanceof HttpExceptionInterface => new self($e->getStatusCode(), 'server_error', 'Something went wrong on our side.', $e->getHeaders()),
            default => new self(500, 'server_error', 'Something went wrong on our side.'),
        };
    }

    public function render(): JsonResponse
    {
        $body = ['message' => $this->getMessage(), 'code' => $this->errorCode];

        if ($this->errors !== null) {
            $body['errors'] = $this->errors;
        }

        return new JsonResponse($body, $this->getStatusCode(), $this->getHeaders());
    }
}

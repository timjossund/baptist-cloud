<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The API version of `verified`: a JSON 403 instead of a redirect (spec 0002, AC-10).
 */
class EnsureApiEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasVerifiedEmail()) {
            throw ApiException::emailUnverified();
        }

        return $next($request);
    }
}

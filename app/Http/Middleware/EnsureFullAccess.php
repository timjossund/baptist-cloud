<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses free users on content routes with `plan_required` (spec 0002, AC-11).
 */
class EnsureFullAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasFullAccess()) {
            throw ApiException::planRequired();
        }

        return $next($request);
    }
}

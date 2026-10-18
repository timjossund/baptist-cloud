<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTokenRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * Checked against an unknown email, so a wrong email takes as long as a wrong password.
     */
    private const DUMMY_HASH = '$2y$12$c0kQ6iVU/LkEeEUqY7w6kOqbyDbz9alei/ZHCZw3xTzan6n9L5VuC';

    /**
     * Trade an email and password for a device token (spec 0002, AC-1 to AC-4).
     */
    public function store(StoreTokenRequest $request): JsonResponse
    {
        $email = Str::lower($request->string('email')->toString());
        $ip = (string) $request->ip();

        // name => [key, max attempts, decay seconds]
        $limits = [
            'email_ip' => ['api-login:'.$email.'|'.$ip, 5, 60],
            'ip' => ['api-login-ip:'.$ip, 20, 60],
            'email' => ['api-login-email:'.$email, 10, 900],
        ];

        $blocked = array_filter($limits, fn (array $limit) => RateLimiter::tooManyAttempts($limit[0], $limit[1]));

        if ($blocked !== []) {
            foreach (array_keys($blocked) as $name) {
                Log::warning('API sign in throttled', ['ip' => $ip, 'throttle' => $name]);
            }

            throw ApiException::tooManyAttempts(max(array_map(fn (array $limit) => RateLimiter::availableIn($limit[0]), $blocked)));
        }

        RateLimiter::hit($limits['ip'][0], $limits['ip'][2]);

        $provider = Auth::getProvider();
        $user = $provider->retrieveByCredentials(['email' => $request->string('email')->toString()]);
        $credentials = ['password' => $request->string('password')->toString()];

        if ($user instanceof User) {
            $valid = $provider->validateCredentials($user, $credentials);
        } else {
            Hash::check($credentials['password'], self::DUMMY_HASH);
            $valid = false;
        }

        if (! $valid || ! $user instanceof User) {
            RateLimiter::hit($limits['email_ip'][0], $limits['email_ip'][2]);
            RateLimiter::hit($limits['email'][0], $limits['email'][2]);

            throw ApiException::invalidCredentials();
        }

        if (! $user->hasVerifiedEmail()) {
            throw ApiException::emailUnverified();
        }

        RateLimiter::clear($limits['email_ip'][0]);
        RateLimiter::clear($limits['email'][0]);

        return response()->json([
            'token' => $user->createToken($request->string('device_name')->toString())->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Sign out on this device only (spec 0002, AC-7).
     */
    public function destroy(Request $request): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->noContent();
    }
}

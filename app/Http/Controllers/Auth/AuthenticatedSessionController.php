<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use DerekCodes\TurnstileLaravel\TurnstileLaravel;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $turnstile = new TurnstileLaravel;
        $response = $turnstile->validate($request->get('cf-turnstile-response'));

        if (get_data($response, 'status', 0) == 1) {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home-page', absolute: false));
        } else {
            return redirect()->back()->withErrors(['turnstile' => 'Turnstile validation failed.']);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/home');
    }
}

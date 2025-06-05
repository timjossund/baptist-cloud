<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
    //         'bio' => ['nullable', 'string', 'max:255'],
    //         'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $filename = $request->username . "-profile-pic.jpg";

    //     $manager = new ImageManager(new Driver());
    //     $image = $manager->read($request->avatar);
    //     $imgNew = $image->cover(80, 80)->toJpeg();
    //     Storage::disk('public')->put("avatars/".$filename, $imgNew);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'username' => $request->username,
    //         'bio' => $request->bio,
    //         'avatar' => $filename,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     event(new Registered($user));

    //     //dd($user->avatar);

    //     Auth::login($user);

    //     return redirect(route('home-page', absolute: false));
    // }
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cf-turnstile-response' => ['required', Rule::turnstile()],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'bio' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png'], // Removed SVG
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $filename = 'default-avatar.jpg';

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            try {
                $filename = $request->username . "-profile-pic.jpg";
                $manager = new ImageManager(new Driver());
                $image = $manager->read($request->file('avatar')->getRealPath());
                $imgNew = $image->cover(80, 80)->toJpeg();
                Storage::disk('public')->put("avatars/".$filename, $imgNew);
            } catch (\Exception $e) {
                \Log::error('Avatar processing failed: ' . $e->getMessage());
                // Optionally, redirect back with an error message
                return redirect()->back()->withErrors(['avatar' => 'Failed to process avatar image.']);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'bio' => $request->bio,
            'avatar' => $filename,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home-page'));
    }
}

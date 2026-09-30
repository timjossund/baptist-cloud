<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // Store the old avatar filename before any updates
        $oldAvatarPath = $user->getRawOriginal('avatar'); // This gets the actual filename without the accessor transformation

        $filename = null;

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            try {
                $filename = $data['username'].'-'.Str::random(4).'-profile-pic.jpg';
                $manager = new ImageManager(new Driver);
                $image = $manager->read($request->file('avatar')->getRealPath());
                $imgNew = $image->cover(80, 80)->toJpeg();
                Storage::disk('public')->put('avatars/'.$filename, $imgNew);
                // Delete the old avatar if it exists and is not the default
                if ($oldAvatarPath && $oldAvatarPath !== 'default-avatar.jpg') {
                    Storage::disk('public')->delete('avatars/'.$oldAvatarPath);
                }
            } catch (\Exception $e) {
                \Log::error('Avatar processing failed: '.$e->getMessage());

                // Optionally, redirect back with an error message
                return redirect()->back()->withErrors(['avatar' => 'Failed to process avatar image.'])->with('error', 'Avatar processing failed');
            }
        }

        // Delete old avatar if it exists and is not the default

        $user->fill($data);

        if ($filename != null) {
            $user->avatar = $filename;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profile Updated');
    }

    public function showFollowers()
    {
        $followers = auth()->user()->followers()->cursorPaginate(20);

        return view('follower-list', ['user' => auth()->user(), 'followers' => $followers]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $userAvatarPath = $user->getRawOriginal('avatar');

        if ($userAvatarPath && $userAvatarPath !== 'default-avatar.jpg') {
            Storage::disk('public')->delete('avatars/'.$userAvatarPath);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Account Deleted');
    }
}

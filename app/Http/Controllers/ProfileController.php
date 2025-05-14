<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
//        if ($request->avatar != null) {
//
//            $filename = $user->id . "-" . uniqid() . ".jpg";
//
//            $manager = new ImageManager(new Driver());
//            $image = $manager->read($data['avatar']);
//            $imgNew = $image->cover(80, 80)->toJpeg();
//            Storage::disk('public')->put("avatars/".$filename, $imgNew);
//            $user->avatar = $filename;
//
//        } else {
//            $user->avatar = $oldAvatarPath;
//        }

        if ($request->file('avatar') == null) {
            $data['avatar'] = $user->getRawOriginal('avatar');
        } else {
            $oldAvatarPath = $user->getRawOriginal('avatar');
            $avatarUrl = "avatar" . uniqid() . ".jpg";

            $manager = new ImageManager(new Driver());
            $avatar = $manager->read($data['avatar']);
            $imgNew = $avatar->cover(1200, 400)->toJpeg();
            Storage::disk('public')->put("avatars/".$avatarUrl, $imgNew);
            Storage::disk('public')->delete("avatars/".$oldAvatarPath);

            $data['image'] = $avatarUrl;
        }


        // Delete old avatar if it exists and is not the default
        if ($oldAvatarPath && $oldAvatarPath !== 'default-avatar.png') {
            Storage::disk('public')->delete("avatars/" . $oldAvatarPath);
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'profile-updated');
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

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Account Deleted');
    }
}

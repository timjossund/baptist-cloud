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

        $user->fill($data);

//        dd($user->avatar);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $filename = $user->username . "-profile-pic.jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($data['avatar']);
        $imgNew = $image->cover(80, 80)->toJpeg();
        Storage::disk('public')->put("avatars/".$filename, $imgNew);

//        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
//            Storage::disk('public')->delete($user->avatar);
//        }

        $user->avatar = $filename;

        $user->save();


//        if ($oldAvatar != '/default-avatar.jpg') {
//            Storage::disk('public')->delete(str_replace("/storage/", "", $oldAvatar));
//        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

//    public function userAvatar(U)
//    {
//        $user = $request->user();
//    }

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

        return Redirect::to('/');
    }
}

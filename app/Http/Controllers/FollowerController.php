<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;


class FollowerController extends Controller
{
    /**
     * Toggle the follow/unfollow state for the authenticated user.
     *
     * @param User $user The user to be followed or unfollowed.
     * @return JsonResponse The response with the updated followers count.
     */
    public function followUnfollow(User $user)
    {
        // Toggle follow/unfollow status for the authenticated user
        $user->followers()->toggle(auth()->user());

        // Return the updated followers count as a JSON response
        return response()->json(['followersCount' => $user->followers()->count()]);
    }
}

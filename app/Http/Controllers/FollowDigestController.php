<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class FollowDigestController extends Controller
{
    public function unsubscribe(User $user): View
    {
        $user->forceFill(['notify_follows' => false])->save();

        return view('follow-digest-unsubscribed');
    }
}

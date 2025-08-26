<?php

namespace App\Policies;

use App\Models\BcAd;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function edit_ad(): bool
    {
        return auth()->user()->is_admin;
    }
}

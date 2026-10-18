<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Laravel\Sanctum\PersonalAccessToken;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('model:prune')->daily();

// Sanctum's sanctum:prune-expired ignores last_used_at, so prune idle tokens here (spec 0002, AC-8).
Schedule::call(function () {
    PersonalAccessToken::whereRaw('coalesce(last_used_at, created_at) < ?', [now()->subDays(User::API_TOKEN_IDLE_DAYS)])->delete();
})->name('prune-idle-api-tokens')->daily();

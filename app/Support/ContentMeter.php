<?php

namespace App\Support;

use App\Models\ContentView;
use App\Models\Post;
use App\Models\Sermon;
use App\Models\User;

/**
 * Counts free views of articles and sermons per user per month.
 */
class ContentMeter
{
    public const LIMITS = [
        'post' => 5,
        'sermon' => 2,
    ];

    public function check(User $user, Post|Sermon $item): MeterResult
    {
        $now = now(config('app.timezone'));
        $period = $now->copy()->startOfMonth()->toDateString();
        $resetsOn = $now->copy()->addMonthNoOverflow()->startOfMonth();

        if ($user->hasFullAccess() || (int) $item->user_id === $user->id) {
            return new MeterResult(true, null, $resetsOn);
        }

        $kind = $item instanceof Post ? 'post' : 'sermon';
        $limit = self::LIMITS[$kind];
        $views = ContentView::where('user_id', $user->id)->where('period', $period)->where('kind', $kind);

        if ((clone $views)->where('item_id', $item->id)->exists()) {
            return new MeterResult(true, max(0, $limit - $views->count()), $resetsOn);
        }

        $used = $views->count();

        if ($used >= $limit) {
            return new MeterResult(false, 0, $resetsOn);
        }

        ContentView::insertOrIgnore([
            'user_id' => $user->id,
            'kind' => $kind,
            'item_id' => $item->id,
            'period' => $period,
            'created_at' => now(),
        ]);

        return new MeterResult(true, $limit - ($used + 1), $resetsOn);
    }
}

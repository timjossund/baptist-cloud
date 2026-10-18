<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One free view of a post or sermon by a user in a month.
 */
class ContentView extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'kind',
        'item_id',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Views older than 3 months no longer count toward anything.
     */
    public function prunable(): Builder
    {
        return static::where('period', '<', now()->subMonthsNoOverflow(3)->startOfMonth()->toDateString());
    }
}

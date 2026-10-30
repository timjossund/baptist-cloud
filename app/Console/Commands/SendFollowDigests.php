<?php

namespace App\Console\Commands;

use App\Models\FollowDigestSend;
use App\Models\Post;
use App\Models\Sermon;
use App\Models\User;
use App\Notifications\FollowDigestNotification;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendFollowDigests extends Command
{
    public const TIMEZONE = 'America/Chicago';

    protected $signature = 'follows:send-digests';

    protected $description = 'Email followers a digest of articles and sermons published yesterday';

    public function handle(): int
    {
        [$start, $end, $digestDate] = $this->window();

        $posts = Post::query()
            ->with('user')
            ->whereNotNull('published_at')
            ->where('first_published_at', '>=', $start)
            ->where('first_published_at', '<', $end)
            ->get();

        $sermons = Sermon::query()
            ->with('user')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->get();

        if ($posts->isEmpty() && $sermons->isEmpty()) {
            $this->info('Nothing new to send.');

            return self::SUCCESS;
        }

        $authorIds = $posts->pluck('user_id')
            ->merge($sermons->pluck('user_id'))
            ->unique()
            ->values();

        $followedIdsByFollower = DB::table('followers')
            ->whereIn('user_id', $authorIds)
            ->get(['follower_id', 'user_id'])
            ->groupBy('follower_id')
            ->map(fn (Collection $edges) => $edges->pluck('user_id'));

        $followers = User::query()
            ->whereIn('id', $followedIdsByFollower->keys())
            ->where('notify_follows', true)
            ->whereNotNull('email_verified_at')
            ->get();

        $sent = 0;

        foreach ($followers as $follower) {
            $followedIds = $followedIdsByFollower->get($follower->id, collect());
            $theirPosts = $this->withoutOwn($posts, $followedIds, $follower->id);
            $theirSermons = $this->withoutOwn($sermons, $followedIds, $follower->id);

            if ($theirPosts->isEmpty() && $theirSermons->isEmpty()) {
                continue;
            }

            $recorded = FollowDigestSend::query()->insertOrIgnore([
                'user_id' => $follower->id,
                'digest_date' => $digestDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($recorded === 0) {
                continue;
            }

            try {
                $follower->notify(new FollowDigestNotification($theirPosts, $theirSermons));
            } catch (Throwable $exception) {
                FollowDigestSend::query()
                    ->where('user_id', $follower->id)
                    ->where('digest_date', $digestDate)
                    ->delete();

                throw $exception;
            }

            $sent++;
        }

        $this->info("Queued {$sent} digest ".str('email')->plural($sent).'.');

        return self::SUCCESS;
    }

    /**
     * Yesterday in America/Chicago, as a UTC range and a calendar date.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string}
     */
    private function window(): array
    {
        $now = now(self::TIMEZONE);

        return [
            $now->copy()->subDay()->startOfDay()->utc(),
            $now->copy()->startOfDay()->utc(),
            $now->copy()->subDay()->toDateString(),
        ];
    }

    /**
     * @template T of Post|Sermon
     *
     * @param  EloquentCollection<int, T>  $items
     * @param  Collection<int, int>  $followedIds
     * @return EloquentCollection<int, T>
     */
    private function withoutOwn(EloquentCollection $items, Collection $followedIds, int $followerId): EloquentCollection
    {
        return $items
            ->whereIn('user_id', $followedIds->all())
            ->where('user_id', '!=', $followerId)
            ->values();
    }
}

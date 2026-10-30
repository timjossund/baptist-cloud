<?php

use App\Console\Commands\SendFollowDigests;
use App\Models\Category;
use App\Models\FollowDigestSend;
use App\Models\Post;
use App\Models\Sermon;
use App\Models\User;
use App\Notifications\FollowDigestNotification;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-12 07:05:00', SendFollowDigests::TIMEZONE));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('a follower receives yesterday articles and sermons from people they follow', function () {
    Notification::fake();

    $author = User::factory()->create(['name' => 'Ada Pastor']);
    $follower = User::factory()->create();
    $follower->following()->attach($author);

    $post = publishArticle($author, 'Grace Upon Grace', yesterdayNoon());
    $sermon = publishSermon($author, 'The Door', yesterdayNoon());

    $this->artisan('follows:send-digests')->assertSuccessful();

    Notification::assertSentTo($follower, FollowDigestNotification::class, function ($notification) use ($follower, $post, $sermon, $author) {
        $html = (string) $notification->toMail($follower)->render();

        expect($html)->toContain('Grace Upon Grace')
            ->toContain('The Door')
            ->toContain('Ada Pastor')
            ->toContain(route('single-post', ['username' => $author->username, 'post' => $post->slug]))
            ->toContain(route('sermons.show', $sermon))
            ->toContain('Stop these emails');

        return $notification->posts->contains('id', $post->id)
            && $notification->sermons->contains('id', $sermon->id);
    });
});

test('drafts edits and the authors own publish stay out of the digest', function () {
    Notification::fake();

    $author = User::factory()->create();
    $follower = User::factory()->create();
    $optedOut = User::factory()->create(['notify_follows' => false]);
    $unverified = User::factory()->unverified()->create();

    $follower->following()->attach($author);
    $optedOut->following()->attach($author);
    $unverified->following()->attach($author);
    $author->following()->attach($author);

    $live = publishArticle($author, 'Live Yesterday', yesterdayNoon());
    publishArticle($author, 'Still A Draft', null);
    $older = publishArticle($author, 'Already Live', Carbon::parse('2026-10-09 12:00:00', SendFollowDigests::TIMEZONE));
    $older->update(['published_at' => yesterdayNoon()]);
    publishSermon($author, 'Uploaded Today', Carbon::parse('2026-10-12 06:00:00', SendFollowDigests::TIMEZONE));

    $this->artisan('follows:send-digests')->assertSuccessful();

    Notification::assertSentTo($follower, FollowDigestNotification::class, function ($notification) use ($live) {
        return $notification->posts->count() === 1
            && $notification->posts->contains('id', $live->id)
            && $notification->sermons->isEmpty();
    });

    Notification::assertNotSentTo($author, FollowDigestNotification::class);
    Notification::assertNotSentTo($optedOut, FollowDigestNotification::class);
    Notification::assertNotSentTo($unverified, FollowDigestNotification::class);

    expect($older->fresh()->first_published_at->toDateString())->toBe('2026-10-09');
});

test('an article returned to draft stays out of the digest', function () {
    Notification::fake();

    $author = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author);

    $post = publishArticle($author, 'Withdrawn Article', yesterdayNoon());
    $post->update(['published_at' => null]);

    expect($post->fresh()->first_published_at)->not->toBeNull();

    $this->artisan('follows:send-digests')->assertSuccessful();

    Notification::assertNothingSent();
    expect(FollowDigestSend::count())->toBe(0);
});

test('an empty day sends no email', function () {
    Notification::fake();

    $author = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author);

    $this->artisan('follows:send-digests')->assertSuccessful();

    Notification::assertNothingSent();
    expect(FollowDigestSend::count())->toBe(0);
});

test('running the digest twice does not send a second email', function () {
    Notification::fake();

    $author = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author);
    publishArticle($author, 'Once Is Enough', yesterdayNoon());

    $this->artisan('follows:send-digests')->assertSuccessful();
    $this->artisan('follows:send-digests')->assertSuccessful();

    Notification::assertSentToTimes($follower, FollowDigestNotification::class, 1);
    expect(FollowDigestSend::count())->toBe(1);
});

test('a failed queue dispatch can be retried without duplicating the digest', function () {
    $author = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author);
    publishArticle($author, 'Retry This Digest', yesterdayNoon());

    $attempts = 0;
    $queuedJob = null;

    Bus::shouldReceive('dispatch')
        ->twice()
        ->with(Mockery::type(SendQueuedNotifications::class))
        ->andReturnUsing(function ($job) use (&$attempts, &$queuedJob) {
            if (++$attempts === 1) {
                throw new RuntimeException('Queue unavailable');
            }

            $queuedJob = $job;
        });

    expect(fn () => Artisan::call('follows:send-digests'))
        ->toThrow(RuntimeException::class, 'Queue unavailable');

    expect(FollowDigestSend::count())->toBe(0);

    $this->artisan('follows:send-digests')->assertSuccessful();
    $this->artisan('follows:send-digests')->assertSuccessful();

    expect($attempts)->toBe(2)
        ->and($queuedJob)->toBeInstanceOf(SendQueuedNotifications::class)
        ->and($queuedJob->notification)->toBeInstanceOf(FollowDigestNotification::class)
        ->and($queuedJob->notifiables->contains('id', $follower->id))->toBeTrue();
    expect(FollowDigestSend::count())->toBe(1);
});

test('queued digests respect the current email preference at delivery', function (bool $notifyFollows) {
    Bus::fake();

    $author = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author);
    publishArticle($author, 'Waiting In The Queue', yesterdayNoon());

    $this->artisan('follows:send-digests')->assertSuccessful();

    $job = Bus::dispatched(SendQueuedNotifications::class)->sole();

    // Update the database without refreshing the recipient already held by the job.
    User::whereKey($follower->id)->update(['notify_follows' => $notifyFollows]);

    $channel = $this->mock(MailChannel::class);
    if ($notifyFollows) {
        $channel->shouldReceive('send')->once()->withArgs(
            fn ($recipient, $notification) => $recipient->id === $follower->id
                && $notification instanceof FollowDigestNotification
        );
    } else {
        $channel->shouldNotReceive('send');
    }

    $job->handle(app(ChannelManager::class));
})->with([true, false]);

test('first publish time is kept when an article is published again', function () {
    $author = User::factory()->create();
    $category = Category::create(['title' => 'Doctrine']);

    $post = Post::create([
        'user_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Draft Title',
        'slug' => 'draft-title',
        'content' => 'Not yet.',
        'image' => 'draft.jpg',
    ]);

    expect($post->first_published_at)->toBeNull();

    $post->update(['published_at' => yesterdayNoon()]);
    $first = $post->fresh()->first_published_at;

    $post->update(['published_at' => now()]);

    expect($post->fresh()->first_published_at->equalTo($first))->toBeTrue();
});

test('profile can turn the follow digest off and on', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Email me when people I follow publish');

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'notify_follows' => '0',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($user->fresh()->notify_follows)->toBeFalse();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'notify_follows' => '1',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->notify_follows)->toBeTrue();
});

test('a signed unsubscribe link turns the digest off', function () {
    $user = User::factory()->create();
    $url = URL::signedRoute('follow-digest.unsubscribe', $user);

    $this->get($url)
        ->assertOk()
        ->assertSee('Daily emails are off');

    expect($user->fresh()->notify_follows)->toBeFalse();
});

test('a tampered unsubscribe link does not change the preference', function () {
    $user = User::factory()->create();
    $url = URL::signedRoute('follow-digest.unsubscribe', $user);

    $this->get($url.'broken')->assertForbidden();

    expect($user->fresh()->notify_follows)->toBeTrue();
});

test('the follow digest is scheduled for 7am central', function () {
    $this->artisan('schedule:list')->assertSuccessful();

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command ?? '', 'follows:send-digests'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 7 * * *')
        ->and($event->timezone)->toBe(SendFollowDigests::TIMEZONE);
});

function yesterdayNoon(): Carbon
{
    return Carbon::parse('2026-10-11 12:00:00', SendFollowDigests::TIMEZONE);
}

function publishArticle(User $author, string $title, ?Carbon $publishedAt): Post
{
    $category = Category::firstOrCreate(['title' => 'Doctrine']);

    return Post::create([
        'user_id' => $author->id,
        'category_id' => $category->id,
        'title' => $title,
        'slug' => str($title)->slug().'-'.str()->random(4),
        'content' => 'Article body',
        'image' => 'article.jpg',
        'published_at' => $publishedAt,
    ]);
}

function publishSermon(User $author, string $title, Carbon $createdAt): Sermon
{
    $sermon = Sermon::create([
        'user_id' => $author->id,
        'title' => $title,
        'slug' => str($title)->slug().'-'.str()->random(4),
        'description' => 'Sermon notes',
        'audio_url' => 'https://example.com/sermon.mp3',
    ]);

    $sermon->created_at = $createdAt;
    $sermon->save();

    return $sermon;
}

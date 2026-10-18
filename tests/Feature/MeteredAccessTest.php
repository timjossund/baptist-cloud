<?php

use App\Models\BcAd;
use App\Models\Category;
use App\Models\ContentView;
use App\Models\Post;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config([
        'services.stripe.member_price' => 'price_member',
        'services.stripe.author_price' => 'price_author',
        'services.stripe.author_legacy_prices' => 'price_author_legacy, ',
    ]);

    Storage::fake('sermons');
    Storage::disk('sermons')->buildTemporaryUrlsUsing(fn ($path) => 'https://signed.test/'.$path);
});

function meteredPost(?User $owner = null, array $attributes = []): Post
{
    $owner ??= User::factory()->create();
    $category = Category::first() ?? Category::create(['title' => 'General']);

    return Post::create(array_merge([
        'title' => 'Metered post',
        'slug' => 'metered-'.uniqid(),
        'content' => "First block\n\nSecond block\n\nThird block\n\nFourth block secret ending",
        'category_id' => $category->id,
        'user_id' => $owner->id,
        'published_at' => now(),
    ], $attributes));
}

function meteredSermon(?User $owner = null, array $attributes = []): Sermon
{
    $owner ??= User::factory()->create();

    return Sermon::create(array_merge([
        'title' => 'Metered sermon',
        'slug' => 'metered-sermon-'.uniqid(),
        'description' => 'A sermon description',
        'audio_url' => 'https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/talk-'.uniqid().'.mp3',
        'video_url' => '',
        'user_id' => $owner->id,
    ], $attributes));
}

function planSubscription(User $user, string $price, array $overrides = []): void
{
    DB::table('subscriptions')->insert(array_merge([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => $price,
        'quantity' => 1,
        'ends_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

function postUrl(Post $post): string
{
    return '/@'.$post->user->username.'/'.$post->slug;
}

// covers: AC-7, AC-9, AC-13
test('a free user reads 5 articles, hits the paywall on the 6th, and can reopen the 1st', function () {
    $reader = User::factory()->create();
    $posts = collect(range(1, 6))->map(fn () => meteredPost());

    foreach ($posts->take(5) as $post) {
        $this->actingAs($reader)->get(postUrl($post))->assertOk()->assertSee('secret ending');
    }

    $this->actingAs($reader)->get(postUrl($posts[5]))
        ->assertOk()
        ->assertSee('Third block')
        ->assertDontSee('Fourth block')
        ->assertDontSee('secret ending')
        ->assertSee('$15/month')
        ->assertSee('Your free articles reset on '.now()->addMonthNoOverflow()->startOfMonth()->format('F j'));

    $this->actingAs($reader)->get(postUrl($posts[0]))->assertSee('secret ending');

    expect(ContentView::where('user_id', $reader->id)->count())->toBe(5);
});

// covers: AC-9
test('a short post still shows the paywall, with only its first half', function () {
    $reader = User::factory()->create();
    collect(range(1, 5))->each(fn () => $this->actingAs($reader)->get(postUrl(meteredPost())));

    $this->actingAs($reader)->get(postUrl(meteredPost(null, ['content' => "Grace opens\n\nthe closing lines"])))
        ->assertSee('Grace opens')
        ->assertDontSee('closing lines')
        ->assertSee('See the Member plan');
});

// covers: AC-9, AC-13
test('a post written as one long paragraph shows at most 120 words on the paywall', function () {
    $reader = User::factory()->create();
    collect(range(1, 5))->each(fn () => $this->actingAs($reader)->get(postUrl(meteredPost())));
    $words = collect(range(1, 300))->map(fn ($i) => "word$i")->implode(' ');

    $html = $this->actingAs($reader)->get(postUrl(meteredPost(null, ['content' => $words])))
        ->assertOk()
        ->assertSee('See the Member plan')
        ->getContent();

    expect($html)->toContain('word120')
        ->and($html)->not->toContain('word121')
        ->and($html)->not->toContain('word300');
});

// covers: AC-9, AC-13
test('the paywall never shows the whole post, even when it fits in 3 blocks', function (string $content, string $shown, string $hidden) {
    $reader = User::factory()->create();
    collect(range(1, 5))->each(fn () => $this->actingAs($reader)->get(postUrl(meteredPost())));

    $html = $this->actingAs($reader)->get(postUrl(meteredPost(null, ['content' => $content])))->getContent();

    expect($html)->toContain('See the Member plan')
        ->and($html)->not->toContain($hidden);

    if ($shown !== '') {
        expect($html)->toContain($shown);
    }
})->with([
    'three short blocks' => ["Alpha one\n\nBravo two\n\nCharlie secretend", 'Alpha one', 'secretend'],
    'single line breaks only' => ["Alpha one\nBravo two\nCharlie three\nDelta secretend", 'Alpha one', 'secretend'],
    'one word' => ['Secretword', '', 'Secretword'],
]);

// covers: AC-11
test('the near limit note shows on the 4th and 5th article and on the 1st sermon', function () {
    $reader = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $this->actingAs($reader)->get(postUrl(meteredPost()))->assertDontSee('free article');
    }

    $this->actingAs($reader)->get(postUrl(meteredPost()))->assertSee('1 free article left this month');
    $this->actingAs($reader)->get(postUrl(meteredPost()))->assertSee('That was your last free article this month');

    $this->actingAs($reader)->get('/sermons/'.meteredSermon()->id)->assertSee('1 free sermon left this month');
    $this->actingAs($reader)->get('/sermons/'.meteredSermon()->id)->assertSee('That was your last free sermon this month');
});

// covers: AC-8, AC-12, AC-13
test('a free user hears 2 sermons, then the 3rd is paywalled with no audio, and articles are untouched', function () {
    $reader = User::factory()->create();

    foreach (range(1, 2) as $i) {
        $sermon = meteredSermon();
        $this->actingAs($reader)->get('/sermons/'.$sermon->id)
            ->assertSee('<audio', false)
            ->assertSee('https://signed.test/'.$sermon->audioPath(), false);
    }

    $locked = meteredSermon();
    $html = $this->actingAs($reader)->get('/sermons/'.$locked->id)
        ->assertOk()
        ->assertSee('A sermon description')
        ->assertSee('Your free sermons reset on')
        ->getContent();

    expect($html)->not->toContain('<audio')
        ->and($html)->not->toContain($locked->audioPath())
        ->and(ContentView::where('kind', 'post')->count())->toBe(0)
        ->and(ContentView::where('kind', 'sermon')->count())->toBe(2);
});

// covers: AC-12
test('a sermon with no audio shows no player', function () {
    $this->actingAs(User::factory()->create(['is_author' => true]))
        ->get('/sermons/'.meteredSermon(null, ['audio_url' => ''])->id)
        ->assertDontSee('<audio', false);
});

// covers: AC-5, AC-10
test('members, authors, admins, and is_author users never record views or see the paywall', function (string $kind) {
    $user = match ($kind) {
        'admin' => User::factory()->create(['is_admin' => true]),
        'is_author' => User::factory()->create(['is_author' => true]),
        default => User::factory()->create(),
    };

    if (in_array($kind, ['member', 'author', 'legacy'])) {
        planSubscription($user, ['member' => 'price_member', 'author' => 'price_author', 'legacy' => 'price_author_legacy'][$kind]);
    }

    foreach (range(1, 10) as $i) {
        $this->actingAs($user)->get(postUrl(meteredPost()))->assertSee('secret ending')->assertDontSee('free article');
    }

    expect(ContentView::count())->toBe(0)
        ->and($user->fresh()->hasFullAccess())->toBeTrue();
})->with(['member', 'author', 'legacy', 'admin', 'is_author']);

// covers: AC-10
test('owners read their own work without using free views', function () {
    $author = User::factory()->create();
    $post = meteredPost($author);
    $sermon = meteredSermon($author);

    foreach (range(1, 10) as $i) {
        $this->actingAs($author)->get(postUrl($post))->assertSee('secret ending');
        $this->actingAs($author)->get('/sermons/'.$sermon->id)->assertSee('<audio', false);
    }

    expect(ContentView::count())->toBe(0);
});

// covers: AC-16
test('drafts stay hidden from others and record no views for owners', function () {
    $author = User::factory()->create();
    $draft = meteredPost($author, ['published_at' => null]);

    $this->actingAs(User::factory()->create())->get(postUrl($draft))->assertNotFound();
    $this->actingAs($author)->get(postUrl($draft))->assertOk();

    expect(ContentView::count())->toBe(0);
});

// covers: AC-4, AC-6
test('the member price does not unlock author ads, a legacy price does', function () {
    $member = User::factory()->create();
    planSubscription($member, 'price_member');
    $legacy = User::factory()->create();
    planSubscription($legacy, 'price_author_legacy');

    expect($member->canRunAds())->toBeFalse()
        ->and($member->planLabel())->toBe('Member')
        ->and($legacy->canRunAds())->toBeTrue()
        ->and($legacy->planLabel())->toBe('Author');
});

// covers: AC-6
test('the post form hides ad fields from members', function () {
    $member = User::factory()->create();
    planSubscription($member, 'price_member');

    $this->actingAs($member)->get('/post/create-post')->assertOk()->assertDontSee('Ad Heading');
});

// covers: AC-5
test('past due keeps full access, canceled past its end does not', function () {
    $pastDue = User::factory()->create();
    planSubscription($pastDue, 'price_member', ['stripe_status' => 'past_due']);
    $ended = User::factory()->create();
    planSubscription($ended, 'price_member', ['stripe_status' => 'canceled', 'ends_at' => now()->subDay()]);
    $grace = User::factory()->create();
    planSubscription($grace, 'price_member', ['stripe_status' => 'canceled', 'ends_at' => now()->addDays(3)]);
    $incomplete = User::factory()->create();
    planSubscription($incomplete, 'price_member', ['stripe_status' => 'incomplete']);

    expect($pastDue->hasFullAccess())->toBeTrue()
        ->and($ended->hasFullAccess())->toBeFalse()
        ->and($grace->hasFullAccess())->toBeTrue()
        ->and($incomplete->hasFullAccess())->toBeFalse();
});

// covers: AC-4
test('an unknown price counts as author and logs a warning', function () {
    Log::spy();
    $user = User::factory()->create();
    planSubscription($user, 'price_unknown');

    expect($user->planTier())->toBe('author');

    Log::shouldHaveReceived('warning')->once()->withArgs(fn ($message, $context) => $context['price'] === 'price_unknown');
});

// covers: AC-4, AC-5
test('an unset member price never matches an empty subscription price', function () {
    config(['services.stripe.member_price' => null]);
    $user = User::factory()->create();
    planSubscription($user, '');

    expect($user->planTier())->toBe('author');
});

// covers: AC-14
test('views older than 3 months are pruned and recent ones stay', function () {
    $user = User::factory()->create();
    $old = now()->subMonthsNoOverflow(4)->startOfMonth()->toDateString();
    $recent = now()->subMonthNoOverflow()->startOfMonth()->toDateString();

    ContentView::insert([
        ['user_id' => $user->id, 'kind' => 'post', 'item_id' => 1, 'period' => $old, 'created_at' => now()],
        ['user_id' => $user->id, 'kind' => 'post', 'item_id' => 2, 'period' => $recent, 'created_at' => now()],
    ]);

    $this->artisan('model:prune', ['--model' => [ContentView::class]])->assertSuccessful();

    expect(ContentView::pluck('item_id')->all())->toBe([2]);
});

// covers: AC-12
test('the sermon feed has no audio player and records no views', function () {
    $sermon = meteredSermon();

    $html = $this->actingAs(User::factory()->create())->get('/sermons/feed')
        ->assertOk()
        ->assertSee(route('sermons.show', $sermon))
        ->getContent();

    expect($html)->not->toContain('<audio')
        ->and($html)->not->toContain($sermon->audio_url)
        ->and(ContentView::count())->toBe(0);
});

// covers: AC-15
test('the admin table labels each plan', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create(['name' => 'Joan Reader']);
    planSubscription($member, 'price_member');

    $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

    expect($html)->toMatch('/Joan Reader((?!<\/tr>).)*Member/s')
        ->and($html)->not->toMatch('/Joan Reader((?!<\/tr>).)*Author/s');
});

// covers: AC-9
test('the article paywall keeps the title, author, featured image, and site ads', function () {
    BcAd::forceCreate(['title' => 'Seminary Open House', 'description' => 'Visit us', 'link' => 'https://example.com', 'int' => 1]);
    $reader = User::factory()->create();
    collect(range(1, 5))->each(fn () => $this->actingAs($reader)->get(postUrl(meteredPost())));
    $author = User::factory()->create(['name' => 'Pastor Elena Marsh']);
    $locked = meteredPost($author, ['title' => 'On Grace Alone', 'image' => 'grace-cover.jpg']);

    $this->actingAs($reader)->get(postUrl($locked))
        ->assertOk()
        ->assertSee('On Grace Alone')
        ->assertSee('Pastor Elena Marsh')
        ->assertSee('grace-cover.jpg')
        ->assertSee('Seminary Open House')
        ->assertSee('See the Member plan')
        ->assertDontSee('secret ending');
});

// covers: AC-9
test('the sermon paywall shows the cover, title, author, and a member subscribe box', function () {
    $reader = User::factory()->create();
    collect(range(1, 2))->each(fn () => $this->actingAs($reader)->get('/sermons/'.meteredSermon()->id));
    $author = User::factory()->create(['name' => 'Pastor Elena Marsh']);
    $locked = meteredSermon($author, ['title' => 'The Prodigal Returns', 'image_url' => 'https://img.test/prodigal.jpg']);

    $this->actingAs($reader)->get('/sermons/'.$locked->id)
        ->assertOk()
        ->assertSee('The Prodigal Returns')
        ->assertSee('Pastor Elena Marsh')
        ->assertSee('https://img.test/prodigal.jpg')
        ->assertSee('$15/month')
        ->assertSee('Your free sermons reset on '.now()->addMonthNoOverflow()->startOfMonth()->format('F j'));
});

// covers: AC-9
test('a sermon paywall without a cover image falls back to the placeholder', function () {
    $reader = User::factory()->create();
    collect(range(1, 2))->each(fn () => $this->actingAs($reader)->get('/sermons/'.meteredSermon()->id));

    $this->actingAs($reader)->get('/sermons/'.meteredSermon(null, ['image_url' => null])->id)
        ->assertSee('bible-sermon-placeholder.jpg');
});

// covers: AC-11
test('reopening an article with no free views left repeats the last free note', function () {
    $reader = User::factory()->create();
    $posts = collect(range(1, 5))->map(fn () => meteredPost());
    $posts->each(fn ($post) => $this->actingAs($reader)->get(postUrl($post)));

    $this->actingAs($reader)->get(postUrl($posts[0]))
        ->assertSee('secret ending')
        ->assertSee('That was your last free article this month');
});

// covers: AC-11
test('reopening an article with plenty of views left shows no counter', function () {
    $reader = User::factory()->create();
    $post = meteredPost();
    $this->actingAs($reader)->get(postUrl($post));

    $this->actingAs($reader)->get(postUrl($post))->assertDontSee('free article');

    expect(ContentView::count())->toBe(1);
});

// covers: AC-7, AC-8
test('the free count resets when the month changes', function () {
    $this->travelTo(now()->setDate(2026, 10, 31)->setTime(23, 59));
    $reader = User::factory()->create();
    collect(range(1, 5))->each(fn () => $this->actingAs($reader)->get(postUrl(meteredPost())));
    $next = meteredPost();
    $this->actingAs($reader)->get(postUrl($next))->assertSee('Your free articles reset on November 1');

    $this->travelTo(now()->setDate(2026, 11, 1)->setTime(0, 1));

    $this->actingAs($reader)->get(postUrl($next))->assertSee('secret ending');
    expect(ContentView::where('period', '2026-11-01')->count())->toBe(1);
});

// covers: AC-12
test('a sermon signed link expires 6 hours after the page loads', function () {
    $this->freezeTime();
    $expiry = null;
    Storage::disk('sermons')->buildTemporaryUrlsUsing(function ($path, $expiration) use (&$expiry) {
        $expiry = $expiration;

        return 'https://signed.test/'.$path;
    });

    $this->actingAs(User::factory()->create())->get('/sermons/'.meteredSermon()->id)->assertOk();

    expect($expiry->equalTo(now()->addHours(6)))->toBeTrue();
});

// covers: AC-12
test('the audio path comes from the stored file name on the sermons disk', function (?string $url, ?string $path) {
    expect((new Sermon(['audio_url' => $url]))->audioPath())->toBe($path);
})->with([
    'full IONOS url' => ['https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/romans-8.mp3', 'sermons/romans-8.mp3'],
    'url with a query' => ['https://s3.test/sermons-bc/sermons/psalm-23.mp3?v=2', 'sermons/psalm-23.mp3'],
    'empty' => ['', null],
    'null' => [null, null],
]);

// covers: AC-13
test('listing pages never render full article bodies or audio links', function () {
    $reader = User::factory()->create();
    $author = User::factory()->create();
    $reader->following()->attach($author->id);
    $post = meteredPost($author);
    $sermon = meteredSermon($author);

    foreach (['/', '/category/'.$post->category->title, '/@'.$author->username, '/sermons', '/sermons/feed', '/search', '/search-audio'] as $url) {
        $html = $this->actingAs($reader)->get($url)->assertOk()->getContent();

        expect($html)->not->toContain('secret ending', "full body leaked on $url")
            ->and($html)->not->toContain($sermon->audio_url, "audio link leaked on $url")
            ->and($html)->not->toContain('<audio', "audio player on $url");
    }

    expect(ContentView::count())->toBe(0);
});

// covers: AC-13
test('guests are sent to login from articles and sermons', function () {
    $post = meteredPost();

    $this->get(postUrl($post))->assertRedirect('/login');
    $this->get('/sermons/'.meteredSermon()->id)->assertRedirect('/login');
});

// covers: AC-14
test('the view prune runs daily', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command, 'model:prune'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 0 * * *');
});

// covers: AC-15
test('the admin table labels admin, author, member, and free users', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Site Admin']);
    User::factory()->create(['is_author' => true, 'name' => 'Flag Author']);
    $legacy = User::factory()->create(['name' => 'Legacy Payer']);
    planSubscription($legacy, 'price_author_legacy');
    $member = User::factory()->create(['name' => 'Member Reader']);
    planSubscription($member, 'price_member');
    User::factory()->create(['name' => 'Free Reader']);

    $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

    foreach (['Site Admin' => 'Admin', 'Flag Author' => 'Author', 'Legacy Payer' => 'Author', 'Member Reader' => 'Member', 'Free Reader' => 'Free'] as $name => $label) {
        expect($html)->toMatch('/'.$name.'((?!<\/tr>).)*>\s*'.$label.'\s*</s');
    }
});

// covers: AC-15
test('the admin table does not run a query per user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $count = function (int $users) use ($admin) {
        User::where('id', '!=', $admin->id)->delete();
        User::factory()->count($users)->create()->each(fn ($user) => planSubscription($user, 'price_member'));

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });
        $this->actingAs($admin)->get('/admin')->assertOk();

        return $queries;
    };

    expect($count(8))->toBe($count(2));
});

// covers: AC-6
test('the post form shows ad fields to legacy author subscribers', function () {
    $legacy = User::factory()->create();
    planSubscription($legacy, 'price_author_legacy');

    $this->actingAs($legacy)->get('/post/create-post')->assertOk()->assertSee('Ad Heading');
});

// covers: AC-4
test('every legacy price in the list counts as author, with stray spaces and commas', function () {
    config(['services.stripe.author_legacy_prices' => ' price_old_a ,price_old_b,, ']);
    $first = User::factory()->create();
    planSubscription($first, 'price_old_a');
    $second = User::factory()->create();
    planSubscription($second, 'price_old_b');
    Log::spy();

    expect($first->planTier())->toBe('author')
        ->and($second->planTier())->toBe('author');

    Log::shouldNotHaveReceived('warning');
});

// covers: AC-5
test('a free user with no subscription has no full access and no ads', function () {
    $user = User::factory()->create();

    expect($user->hasFullAccess())->toBeFalse()
        ->and($user->canRunAds())->toBeFalse()
        ->and($user->planLabel())->toBe('Free');
});

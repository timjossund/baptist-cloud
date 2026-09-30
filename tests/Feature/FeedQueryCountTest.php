<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedFeedPosts(User $viewer, int $count): void
{
    $category = Category::first() ?? Category::create(['title' => 'General']);

    User::factory()->count($count)->create()->each(function (User $author) use ($viewer, $category) {
        $viewer->following()->attach($author->id);
        Post::create([
            'title' => 'Post',
            'slug' => uniqid(),
            'content' => 'Body',
            'category_id' => $category->id,
            'user_id' => $author->id,
            'published_at' => now(),
            'ad_heading' => 'Heading',
            'ad_description' => 'Text',
            'ad_link' => 'https://example.com',
        ]);
    });
}

function countQueries(callable $request): int
{
    $count = 0;
    DB::listen(function () use (&$count) {
        $count++;
    });
    $request();

    return $count;
}

test('home feed query count does not grow with the number of posts', function () {
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    seedFeedPosts($viewer, 1);
    $few = countQueries(fn () => $this->get('/')->assertOk());

    seedFeedPosts($viewer, 4);
    $many = countQueries(fn () => $this->get('/')->assertOk());

    expect($many)->toBe($few);
});

<?php

use App\Models\BcAd;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;

function adPostPayload(array $overrides = []): array
{
    $category = Category::first() ?? Category::create(['title' => 'General']);

    return array_merge([
        'title' => 'Ad test',
        'content' => 'Body',
        'category_id' => $category->id,
        'ad_heading' => 'Buy my book',
        'ad_description' => 'Great read',
        'ad_link' => 'https://example.com',
    ], $overrides);
}

function adPost(User $owner, array $attributes = []): Post
{
    $category = Category::first() ?? Category::create(['title' => 'General']);

    return Post::create(array_merge([
        'title' => 'Ad post',
        'slug' => 'ad-post-'.uniqid(),
        'content' => 'Body',
        'category_id' => $category->id,
        'user_id' => $owner->id,
        'published_at' => now(),
        'ad_heading' => 'Author Ad Heading',
        'ad_description' => 'Author ad text',
        'ad_link' => 'https://example.com',
    ], $attributes));
}

test('authors who cannot run ads have ad fields ignored on update', function () {
    $author = User::factory()->create();
    $post = adPost($author, ['ad_heading' => null, 'ad_description' => null, 'ad_link' => null]);

    $this->actingAs($author)
        ->patch('/post/'.$post->slug, adPostPayload())
        ->assertSessionHasNoErrors();

    expect($post->refresh()->ad_heading)->toBeNull();
});

test('authors allowed to run ads can save them', function () {
    $author = User::factory()->create(['is_author' => true]);
    $post = adPost($author, ['ad_heading' => null, 'ad_description' => null, 'ad_link' => null]);

    $this->actingAs($author)
        ->patch('/post/'.$post->slug, adPostPayload())
        ->assertSessionHasNoErrors();

    expect($post->refresh()->ad_heading)->toBe('Buy my book');
});

test('ad links must be http or https urls', function () {
    $author = User::factory()->create(['is_author' => true]);
    $post = adPost($author);

    $this->actingAs($author)
        ->patch('/post/'.$post->slug, adPostPayload(['ad_link' => 'javascript:alert(1)']))
        ->assertSessionHasErrors('ad_link');
});

test('an unsubscribed author\'s ad is replaced by a platform ad', function () {
    BcAd::create(['title' => 'Platform Ad Title', 'description' => 'x', 'link' => 'https://bc.example', 'int' => 1]);
    $author = User::factory()->create();
    $post = adPost($author);

    $this->actingAs(User::factory()->create())
        ->get('/@'.$author->username.'/'.$post->slug)
        ->assertOk()
        ->assertDontSee('Author Ad Heading')
        ->assertSee('Platform Ad Title');
});

test('an entitled author\'s own ad is shown', function () {
    BcAd::create(['title' => 'Platform Ad Title', 'description' => 'x', 'link' => 'https://bc.example', 'int' => 1]);
    $author = User::factory()->create(['is_author' => true]);
    $post = adPost($author);

    $this->actingAs(User::factory()->create())
        ->get('/@'.$author->username.'/'.$post->slug)
        ->assertOk()
        ->assertSee('Author Ad Heading');
});

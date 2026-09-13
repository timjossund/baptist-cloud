<?php

use App\Models\Category;
use App\Models\Listing;
use App\Models\Post;
use App\Models\User;

test('create post screen can be rendered with markdown guide', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'title' => 'General',
        'slug' => 'general',
    ]);

    $response = $this->actingAs($user)->get('/post/create-post');

    $response->assertStatus(200);
    $response->assertSee('Create A New Article');
    $response->assertSee('Markdown Quick Guide');
});

test('edit post screen can be rendered with markdown guide', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'title' => 'General',
        'slug' => 'general',
    ]);
    $post = Post::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Sample Article Title',
        'slug' => 'sample-article-title',
        'content' => 'Sample content body',
        'image' => 'sample.jpg',
    ]);

    $response = $this->actingAs($user)->get('/post/' . $post->slug . '/edit');

    $response->assertStatus(200);
    $response->assertSee('Publish Your Post');
    $response->assertSee('Markdown Quick Guide');
});

test('create listing screen can be rendered with markdown guide', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $response = $this->actingAs($user)->get('/create-listing');

    $response->assertStatus(200);
    $response->assertSee('Create A New Listing');
    $response->assertSee('Markdown Quick Guide');
});

test('edit listing screen can be rendered with markdown guide', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $listing = Listing::create([
        'position' => 'Pastor',
        'church' => 'Grace Baptist Church',
        'city' => 'Springfield',
        'state' => 'IL',
        'content' => 'Position details here',
        'email' => 'info@grace.org',
        'phone' => '555-123-4567',
    ]);

    $response = $this->actingAs($user)->get('/position/' . $listing->id . '/edit');

    $response->assertStatus(200);
    $response->assertSee('Edit Listing');
    $response->assertSee('Markdown Quick Guide');
});

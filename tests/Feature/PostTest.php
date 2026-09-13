<?php

use App\Jobs\ProcessPostImage;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

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

test('storing a post dispatches ProcessPostImage job', function () {
    Queue::fake();
    Storage::fake('local');

    $user = User::factory()->create();
    $category = Category::create([
        'title' => 'General',
        'slug' => 'general',
    ]);

    $file = UploadedFile::fake()->image('featured.jpg', 800, 600);

    $response = $this->actingAs($user)->post('/post/create-post', [
        'image' => $file,
        'title' => 'Test Post Title',
        'category_id' => $category->id,
        'content' => 'Test content body',
    ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Test Post Title')->first();
    expect($post)->not->toBeNull();

    Queue::assertPushed(ProcessPostImage::class, function ($job) use ($post) {
        return $job->post->id === $post->id;
    });
});

test('ProcessPostImage job resizes and stores image', function () {
    Storage::fake('local');
    Storage::fake('postImages');

    $user = User::factory()->create();
    $category = Category::create([
        'title' => 'General',
        'slug' => 'general',
    ]);
    $post = Post::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Job Test Post',
        'slug' => 'job-test-post',
        'content' => 'Content here',
    ]);

    $file = UploadedFile::fake()->image('test.jpg', 1600, 1200);
    $tempPath = $file->store('temp-post-images', 'local');

    $job = new ProcessPostImage($post, $tempPath);
    $job->handle();

    $post->refresh();
    expect($post->getRawOriginal('image'))->not->toBeNull();
    Storage::disk('postImages')->assertExists('post-images/' . $post->getRawOriginal('image'));
    Storage::disk('local')->assertMissing($tempPath);
});

test('viewHorizon gate allows admin users and denies non-admin users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $regularUser = User::factory()->create(['is_admin' => false]);

    expect(Gate::forUser($admin)->allows('viewHorizon'))->toBeTrue();
    expect(Gate::forUser($regularUser)->allows('viewHorizon'))->toBeFalse();
});

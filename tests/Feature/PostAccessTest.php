<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

function makePost(User $owner, array $attributes = []): Post
{
    $category = Category::first() ?? Category::create(['title' => 'General']);

    return Post::create(array_merge([
        'title' => 'Hello',
        'slug' => 'hello-'.uniqid(),
        'content' => 'Body',
        'category_id' => $category->id,
        'user_id' => $owner->id,
        'published_at' => now(),
    ], $attributes));
}

function makeAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

test('published posts are visible to verified users', function () {
    $author = User::factory()->create();
    $post = makePost($author);

    $this->actingAs(User::factory()->create())
        ->get('/@'.$author->username.'/'.$post->slug)
        ->assertOk();
});

test('drafts are hidden from other users', function () {
    $author = User::factory()->create();
    $draft = makePost($author, ['published_at' => null]);

    $this->actingAs(User::factory()->create())
        ->get('/@'.$author->username.'/'.$draft->slug)
        ->assertNotFound();
});

test('drafts are visible to their author and to admins', function () {
    $author = User::factory()->create();
    $draft = makePost($author, ['published_at' => null]);

    $this->actingAs($author)->get('/@'.$author->username.'/'.$draft->slug)->assertOk();
    $this->actingAs(makeAdmin())->get('/@'.$author->username.'/'.$draft->slug)->assertOk();
});

test('only the author can edit a post', function () {
    $post = makePost(User::factory()->create());

    $this->actingAs(User::factory()->create())->get('/post/'.$post->slug.'/edit')->assertForbidden();
});

test('only the author or an admin can delete a post', function () {
    $post = makePost(User::factory()->create());

    $this->actingAs(User::factory()->create())->delete('/post/'.$post->id.'/delete')->assertForbidden();
    expect(Post::find($post->id))->not->toBeNull();

    $this->actingAs(makeAdmin())->delete('/post/'.$post->id.'/delete')->assertRedirect();
    expect(Post::find($post->id))->toBeNull();
});

test('post body html is escaped when rendered', function () {
    $author = User::factory()->create();
    $post = makePost($author, [
        'content' => "<script>alert(1)</script>\n\n[x](javascript:alert(1))",
    ]);

    $this->actingAs($author)
        ->get('/@'.$author->username.'/'.$post->slug)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('href="javascript:', false);
});

test('usernames cannot contain path characters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test',
            'username' => '../evil',
            'email' => $user->email,
        ])
        ->assertSessionHasErrors('username');
});

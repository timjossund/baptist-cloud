<?php

use App\Models\Listing;
use App\Models\User;

function listingData(array $overrides = []): array
{
    return array_merge([
        'position' => 'Pastor',
        'church' => 'First Baptist',
        'city' => 'Dallas',
        'state' => 'TX',
        'content' => 'Details',
        'email' => 'church@example.com',
        'phone' => '555-1234',
    ], $overrides);
}

test('a listing can be created without facebook or website', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/create-listing', listingData())
        ->assertSessionHasNoErrors()
        ->assertRedirect('/positions');

    $listing = Listing::sole();
    expect($listing->user_id)->toBe($user->id)
        ->and($listing->isApproved())->toBeFalse();
});

test('approval and owner cannot be set from the request', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user)
        ->post('/create-listing', listingData(['approved_at' => now(), 'user_id' => $other->id]));

    $listing = Listing::sole();
    expect($listing->user_id)->toBe($user->id)
        ->and($listing->isApproved())->toBeFalse();
});

test('an admin can edit a listing without facebook or website', function () {
    $listing = Listing::create(listingData());
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->patch('/position/'.$listing->id.'/save', listingData(['church' => 'Second Baptist']))
        ->assertSessionHasNoErrors()
        ->assertRedirect('/positions');

    expect($listing->refresh()->church)->toBe('Second Baptist');
});

test('non admins cannot edit a listing', function () {
    $listing = Listing::create(listingData());

    $this->actingAs(User::factory()->create())
        ->patch('/position/'.$listing->id.'/save', listingData())
        ->assertForbidden();
});

test('listing html is escaped when rendered', function () {
    $listing = Listing::factory()->create(['content' => '<script>alert(1)</script>']);

    $this->actingAs(User::factory()->create())
        ->get('/position/'.$listing->id)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('other users only see approved listings', function () {
    Listing::factory()->create(['position' => 'Approved Pastor']);
    Listing::factory()->pending()->create(['position' => 'Pending Pastor']);

    $this->actingAs(User::factory()->create())
        ->get('/positions')
        ->assertOk()
        ->assertSee('Approved Pastor')
        ->assertDontSee('Pending Pastor');
});

test('posters see their own pending listings', function () {
    $poster = User::factory()->create();
    $listing = Listing::factory()->pending()->create(['position' => 'My Pending Pastor']);
    $listing->user_id = $poster->id;
    $listing->save();

    $this->actingAs($poster)
        ->get('/positions')
        ->assertOk()
        ->assertSee('My Pending Pastor')
        ->assertSee('Pending review');
});

test('a pending listing is hidden from strangers but visible to its poster and admins', function () {
    $poster = User::factory()->create();
    $listing = Listing::factory()->pending()->create();
    $listing->user_id = $poster->id;
    $listing->save();

    $this->actingAs(User::factory()->create())
        ->get('/position/'.$listing->id)
        ->assertNotFound();

    $this->actingAs($poster)
        ->get('/position/'.$listing->id)
        ->assertOk()
        ->assertSee('Pending review');

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/position/'.$listing->id)
        ->assertOk();
});

test('a poster can edit their own listing', function () {
    $poster = User::factory()->create();
    $listing = Listing::factory()->pending()->create();
    $listing->user_id = $poster->id;
    $listing->save();

    $this->actingAs($poster)
        ->get('/position/'.$listing->id.'/edit')
        ->assertOk();

    $this->actingAs($poster)
        ->patch('/position/'.$listing->id.'/save', listingData(['church' => 'Grace Baptist']))
        ->assertSessionHasNoErrors()
        ->assertRedirect('/positions');

    expect($listing->refresh()->church)->toBe('Grace Baptist');
});

test('a poster editing an approved listing sends it back to review', function () {
    $poster = User::factory()->create();
    $listing = Listing::factory()->create();
    $listing->user_id = $poster->id;
    $listing->save();

    $this->actingAs($poster)
        ->patch('/position/'.$listing->id.'/save', listingData())
        ->assertSessionHas('success', 'Changes saved. Your listing will reappear once an admin approves it.');

    expect($listing->refresh()->isApproved())->toBeFalse();
});

test('an admin edit keeps a listing approved', function () {
    $listing = Listing::factory()->create();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->patch('/position/'.$listing->id.'/save', listingData());

    expect($listing->refresh()->isApproved())->toBeTrue();
});

test('other users cannot open the edit form', function () {
    $listing = Listing::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/position/'.$listing->id.'/edit')
        ->assertForbidden();
});

test('an admin can approve a pending listing', function () {
    $listing = Listing::factory()->pending()->create(['position' => 'Youth Pastor']);

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post('/admin/positions/'.$listing->id.'/approve')
        ->assertSessionHas('success', 'Position approved');

    expect($listing->refresh()->isApproved())->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->get('/positions')
        ->assertSee('Youth Pastor');
});

test('an admin can reject a pending listing', function () {
    $listing = Listing::factory()->pending()->create();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->delete('/admin/positions/'.$listing->id)
        ->assertSessionHas('success', 'Position rejected');

    expect(Listing::count())->toBe(0);
});

test('the review queue lists pending listings and shows an empty state', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/pending-positions')
        ->assertOk()
        ->assertSee('No positions awaiting review.');

    Listing::factory()->pending()->create(['position' => 'Music Minister']);
    Listing::factory()->create(['position' => 'Already Live']);

    $this->actingAs($admin)
        ->get('/admin/pending-positions')
        ->assertOk()
        ->assertSee('Music Minister')
        ->assertSee('Unknown')
        ->assertDontSee('Already Live');
});

test('non admins cannot use the review queue', function () {
    $listing = Listing::factory()->pending()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/pending-positions')->assertForbidden();
    $this->actingAs($user)->post('/admin/positions/'.$listing->id.'/approve')->assertForbidden();
    $this->actingAs($user)->delete('/admin/positions/'.$listing->id)->assertForbidden();

    expect($listing->refresh()->isApproved())->toBeFalse();
});

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
    $this->actingAs(User::factory()->create())
        ->post('/create-listing', listingData())
        ->assertSessionHasNoErrors()
        ->assertRedirect('/positions');

    expect(Listing::count())->toBe(1);
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
    $listing = Listing::create(listingData(['content' => '<script>alert(1)</script>']));

    $this->actingAs(User::factory()->create())
        ->get('/position/'.$listing->id)
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

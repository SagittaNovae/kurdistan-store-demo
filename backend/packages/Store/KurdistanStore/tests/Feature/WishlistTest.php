<?php

it('returns 401 when unauthenticated customer accesses wishlist', function () {
    $this->getJson('/api/v1/wishlist')->assertStatus(401);
});

it('returns empty wishlist for authenticated customer with no items', function () {
    $customer = \Webkul\Customer\Models\Customer::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($customer, 'customer');

    $this->getJson('/api/v1/wishlist')
        ->assertOk()
        ->assertJsonStructure(['data'])
        ->assertJson(['data' => []]);
});

it('returns 401 when unauthenticated customer tries to add to wishlist', function () {
    $this->postJson('/api/v1/wishlist', ['product_id' => 1])
        ->assertStatus(401);
});

it('returns 401 when unauthenticated customer tries to remove from wishlist', function () {
    $this->deleteJson('/api/v1/wishlist/1')
        ->assertStatus(401);
});

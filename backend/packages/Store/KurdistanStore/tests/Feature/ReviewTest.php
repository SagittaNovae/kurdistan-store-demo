<?php

use Illuminate\Testing\Fluent\AssertableJson;

it('returns approved reviews for a product', function () {
    $this->getJson('/api/v1/products/1/reviews')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns 401 when unauthenticated customer tries to post a review', function () {
    $this->postJson('/api/v1/products/1/reviews', [
        'title'   => 'Great',
        'comment' => 'Love it',
        'rating'  => 5,
    ])->assertStatus(401);
});

it('validates review payload', function () {
    // Create + authenticate a customer
    $customer = \Webkul\Customer\Models\Customer::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $this->actingAs($customer, 'customer');

    $this->postJson('/api/v1/products/1/reviews', [
        'title'   => '',
        'comment' => '',
        'rating'  => 0,
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['title', 'comment', 'rating']);
});

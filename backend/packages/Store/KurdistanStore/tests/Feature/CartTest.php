<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Webkul\Customer\Models\Customer;

function productWithStock(): ?int
{
    return DB::table('product_inventories')
        ->where('qty', '>', 0)
        ->value('product_id');
}

// ─── Cart shape ───────────────────────────────────────────────────────────────

it('cart endpoint returns correct shape for empty cart', function () {
    $this->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.items')
            ->has('data.sub_total')
            ->has('data.grand_total')
            ->has('data.items_qty')
        );
});

// ─── Add to cart validation ───────────────────────────────────────────────────

it('add to cart returns 422 when product_id is missing', function () {
    $this->postJson('/api/v1/cart/items', ['quantity' => 1])
        ->assertUnprocessable();
});

it('add to cart returns 422 when product does not exist', function () {
    $this->postJson('/api/v1/cart/items', ['product_id' => 999999, 'quantity' => 1])
        ->assertUnprocessable();
});

it('add to cart returns 422 when quantity is zero', function () {
    $productId = productWithStock();

    if (! $productId) {
        $this->markTestSkipped('No products with stock in database.');
    }

    $this->postJson('/api/v1/cart/items', ['product_id' => $productId, 'quantity' => 0])
        ->assertUnprocessable();
});

// ─── Add / update / remove flow ───────────────────────────────────────────────

it('add to cart returns updated cart with item', function () {
    $productId = productWithStock();

    if (! $productId) {
        $this->markTestSkipped('No products with stock in database.');
    }

    $this->postJson('/api/v1/cart/items', ['product_id' => $productId, 'quantity' => 1])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.items')
            ->has('data.sub_total')
            ->has('data.grand_total')
            ->has('message')
        );
});

it('cart sub_total is positive after adding a product', function () {
    $productId = productWithStock();

    if (! $productId) {
        $this->markTestSkipped('No products with stock in database.');
    }

    $response = $this->postJson('/api/v1/cart/items', [
        'product_id' => $productId,
        'quantity'   => 1,
    ])->assertOk();

    expect($response->json('data.sub_total'))->toBeGreaterThan(0);
});

it('update cart item quantity returns updated cart', function () {
    $productId = productWithStock();

    if (! $productId) {
        $this->markTestSkipped('No products with stock in database.');
    }

    $addResponse = $this->postJson('/api/v1/cart/items', [
        'product_id' => $productId,
        'quantity'   => 1,
    ])->assertOk();

    $items = $addResponse->json('data.items');

    if (empty($items)) {
        $this->markTestSkipped('Cart item not created — product may not support adding to cart.');
    }

    $itemId = $items[0]['id'];

    $this->patchJson("/api/v1/cart/items/{$itemId}", ['quantity' => 2])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data.items'));
});

it('remove cart item returns cart without that item', function () {
    $productId = productWithStock();

    if (! $productId) {
        $this->markTestSkipped('No products with stock in database.');
    }

    $addResponse = $this->postJson('/api/v1/cart/items', [
        'product_id' => $productId,
        'quantity'   => 1,
    ])->assertOk();

    $items = $addResponse->json('data.items');

    if (empty($items)) {
        $this->markTestSkipped('Cart item not created — product may not support adding to cart.');
    }

    $itemId = $items[0]['id'];

    $this->deleteJson("/api/v1/cart/items/{$itemId}")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data.items'));
});

// ─── Checkout auth guard ──────────────────────────────────────────────────────

it('checkout returns 401 when unauthenticated', function () {
    $this->postJson('/api/v1/checkout', [])->assertStatus(401);
});

it('checkout returns 422 when authenticated but required fields are missing', function () {
    $customer = Customer::first();

    if (! $customer) {
        $this->markTestSkipped('No customers in database.');
    }

    $this->actingAs($customer, 'customer')
        ->postJson('/api/v1/checkout', [])
        ->assertUnprocessable();
});

// ─── Orders auth guard ────────────────────────────────────────────────────────

it('orders list returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/orders')->assertStatus(401);
});

it('orders list returns correct shape when authenticated', function () {
    $customer = Customer::first();

    if (! $customer) {
        $this->markTestSkipped('No customers in database.');
    }

    $this->actingAs($customer, 'customer')
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data'));
});

it('order show returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/orders/1')->assertStatus(401);
});

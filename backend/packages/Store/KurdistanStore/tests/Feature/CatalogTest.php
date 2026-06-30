<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

// ─── Filter params ────────────────────────────────────────────────────────────

it('accepts category_id filter without error', function () {
    $this->getJson('/api/v1/products?category_id=1')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('accepts min_price filter without error', function () {
    $this->getJson('/api/v1/products?min_price=10000')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('accepts max_price filter without error', function () {
    $this->getJson('/api/v1/products?max_price=100000')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('accepts in_stock filter without error', function () {
    $this->getJson('/api/v1/products?in_stock=1')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('accepts price_asc sort without error', function () {
    $this->getJson('/api/v1/products?sort=price_asc')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('accepts price_desc sort without error', function () {
    $this->getJson('/api/v1/products?sort=price_desc')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('price_asc returns cheaper products before expensive ones', function () {
    $response = $this->getJson('/api/v1/products?sort=price_asc&per_page=100')->assertOk();
    $prices = collect($response->json('data'))->pluck('price');

    if ($prices->count() < 2) {
        $this->markTestSkipped('Need at least 2 products to verify sort order.');
    }

    expect($prices->first())->toBeLessThanOrEqual($prices->last());
});

it('price_desc returns expensive products before cheaper ones', function () {
    $response = $this->getJson('/api/v1/products?sort=price_desc&per_page=100')->assertOk();
    $prices = collect($response->json('data'))->pluck('price');

    if ($prices->count() < 2) {
        $this->markTestSkipped('Need at least 2 products to verify sort order.');
    }

    expect($prices->first())->toBeGreaterThanOrEqual($prices->last());
});

// ─── Product detail shape ─────────────────────────────────────────────────────

it('product show includes type, special_price, and variants fields', function () {
    $product = DB::table('products')->first();

    if (! $product) {
        $this->markTestSkipped('No products in database.');
    }

    $this->getJson('/api/v1/products/'.$product->id)
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.id')
            ->has('data.type')
            ->has('data.name')
            ->has('data.price')
            ->has('data.special_price')
            ->has('data.variants')
        );
});

it('variants field is an array in product detail', function () {
    $product = DB::table('products')->first();

    if (! $product) {
        $this->markTestSkipped('No products in database.');
    }

    $response = $this->getJson('/api/v1/products/'.$product->id)->assertOk();

    expect($response->json('data.variants'))->toBeArray();
});

// ─── Multilingual data ────────────────────────────────────────────────────────

it('every product in list has a non-empty name', function () {
    $response = $this->getJson('/api/v1/products')->assertOk();
    $items = collect($response->json('data'));

    if ($items->isEmpty()) {
        $this->markTestSkipped('No products in database — add products via Bagisto admin (/admin).');
    }

    $items->pluck('name')->each(fn ($name) => expect($name)->not->toBeNull()->not->toBe(''));
});

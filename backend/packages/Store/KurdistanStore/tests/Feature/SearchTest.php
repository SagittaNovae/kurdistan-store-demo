<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

it('search returns correct paginated shape', function () {
    $this->getJson('/api/v1/products?search=a')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data')
            ->has('meta.total')
            ->has('meta.current_page')
            ->has('meta.last_page')
        );
});

it('search with nonsense query returns zero results', function () {
    $term = 'xyzxyz'.uniqid();

    $response = $this->getJson('/api/v1/products?search='.urlencode($term))->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});

it('search accepts category_id alongside search term', function () {
    $this->getJson('/api/v1/products?search=a&category_id=1')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('search accepts price filters alongside search term', function () {
    $this->getJson('/api/v1/products?search=a&min_price=1000&max_price=9999999')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data')->has('meta.total'));
});

it('search results match queried product name', function () {
    $product = DB::table('product_flat')
        ->where('status', 1)
        ->whereNotNull('name')
        ->where('name', '!=', '')
        ->first();

    if (! $product) {
        $this->markTestSkipped('No active products in database — add products via /admin.');
    }

    $term = mb_substr($product->name, 0, 4);

    $response = $this->getJson('/api/v1/products?search='.urlencode($term))->assertOk();

    expect($response->json('meta.total'))->toBeGreaterThan(0);
});

it('search result items all have required fields', function () {
    $response = $this->getJson('/api/v1/products?search=a')->assertOk();
    $items = collect($response->json('data'));

    if ($items->isEmpty()) {
        $this->markTestSkipped('No products match the query — add products via /admin.');
    }

    $items->each(function ($item) {
        expect($item)->toHaveKey('id')
            ->toHaveKey('name')
            ->toHaveKey('price')
            ->toHaveKey('slug');
    });
});

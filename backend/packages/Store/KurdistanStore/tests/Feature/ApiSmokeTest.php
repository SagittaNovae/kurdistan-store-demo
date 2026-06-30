<?php

use Illuminate\Testing\Fluent\AssertableJson;

it('health endpoint returns ok', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(['status' => 'ok', 'service' => 'kurdistan-store-api']);
});

it('products endpoint returns correct shape', function () {
    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data')
            ->has('meta')
            ->has('meta.current_page')
            ->has('meta.last_page')
            ->has('meta.total')
        );
});

it('products endpoint accepts search param without crashing', function () {
    $this->getJson('/api/v1/products?search=keyboard')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data')
            ->has('meta.total')
        );
});

it('categories endpoint returns correct shape', function () {
    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('no ai routes exist', function () {
    // 404 = truly gone, 405 = only a GET catch-all matches (no POST handler) — either means the AI endpoints are absent
    expect($this->postJson('/api/v1/ai/search', ['query' => 'test'])->status())->toBeIn([404, 405]);
    expect($this->postJson('/api/v1/ai/chat', ['message' => 'hi'])->status())->toBeIn([404, 405]);
});

<?php

use Illuminate\Testing\Fluent\AssertableJson;

// ─── Quote ────────────────────────────────────────────────────────────────────

it('quote returns available rate for Erbil City', function () {
    $this->getJson('/api/v1/shipping/quote?governorate=Erbil&district=Erbil+City')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.available', true)
            ->has('data.rate')
            ->has('data.estimated_days')
            ->has('data.rate_formatted')
        );
});

it('quote falls back to governorate rate when district is unknown', function () {
    $this->getJson('/api/v1/shipping/quote?governorate=Erbil&district=UnknownVillage')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.available', true)
            ->has('data.rate')
        );
});

it('quote returns unavailable for unknown governorate', function () {
    $this->getJson('/api/v1/shipping/quote?governorate=Baghdad')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.available', false)
            ->has('data.message')
        );
});

it('quote returns 422 when governorate is missing', function () {
    $this->getJson('/api/v1/shipping/quote')->assertUnprocessable();
});

it('quote returns correct rate for Sulaymaniyah City', function () {
    $response = $this->getJson('/api/v1/shipping/quote?governorate=Sulaymaniyah&district=Sulaymaniyah+City')
        ->assertOk();

    expect($response->json('data.available'))->toBeTrue();
    expect($response->json('data.rate'))->toEqual(3000);
    expect($response->json('data.estimated_days'))->toBe(1);
});

// ─── Zones ────────────────────────────────────────────────────────────────────

it('zones returns grouped structure keyed by governorate', function () {
    $this->getJson('/api/v1/shipping/zones')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('data'));
});

it('zones includes all four Kurdistan governorates', function () {
    $response = $this->getJson('/api/v1/shipping/zones')->assertOk();
    $governorates = array_keys($response->json('data'));

    expect($governorates)
        ->toContain('Erbil')
        ->toContain('Sulaymaniyah')
        ->toContain('Duhok')
        ->toContain('Halabja');
});

it('every zone entry has rate, estimated_days, and district fields', function () {
    $response = $this->getJson('/api/v1/shipping/zones')->assertOk();

    foreach ($response->json('data') as $zones) {
        foreach ($zones as $zone) {
            expect($zone)
                ->toHaveKey('rate')
                ->toHaveKey('estimated_days')
                ->toHaveKey('district');
        }
    }
});

it('Erbil City has a lower rate than Mergasor', function () {
    $response = $this->getJson('/api/v1/shipping/zones')->assertOk();
    $erbilZones = collect($response->json('data.Erbil'));

    $erbilCity = $erbilZones->firstWhere('district', 'Erbil City');
    $mergasor = $erbilZones->firstWhere('district', 'Mergasor');

    if (! $erbilCity || ! $mergasor) {
        $this->markTestSkipped('Expected zone data not found — run php artisan kurdistan:seed-zones.');
    }

    expect($erbilCity['rate'])->toBeLessThan($mergasor['rate']);
});

it('zones response is cached — second call returns same data', function () {
    $first = $this->getJson('/api/v1/shipping/zones')->assertOk()->json('data');
    $second = $this->getJson('/api/v1/shipping/zones')->assertOk()->json('data');

    expect($first)->toBe($second);
});

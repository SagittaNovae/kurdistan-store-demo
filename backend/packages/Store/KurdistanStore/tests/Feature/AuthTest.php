<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Webkul\Customer\Models\Customer;

// ─── Registration ─────────────────────────────────────────────────────────────

it('register returns 201 with phone-primary account (no email)', function () {
    $phone = '07' . rand(10000000, 99999999);

    $this->postJson('/api/v1/auth/register', [
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'phone'                 => $phone,
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.id')
            ->has('data.phone')
            ->has('data.name')
            ->has('message')
        );
});

it('register returns 201 with phone + optional email', function () {
    $phone = '07' . rand(10000000, 99999999);
    $email = 'opt-' . uniqid() . '@example.com';

    $this->postJson('/api/v1/auth/register', [
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'phone'                 => $phone,
        'email'                 => $email,
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])
        ->assertStatus(201)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.id')
            ->where('data.phone', $phone)
            ->where('data.email', $email)
            ->etc()
        );
});

it('register returns 422 on missing phone', function () {
    $this->postJson('/api/v1/auth/register', [
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['phone']);
});

it('register returns 422 on duplicate phone', function () {
    $phone = '07' . rand(10000000, 99999999);

    $payload = [
        'first_name'            => 'Dup',
        'last_name'             => 'User',
        'phone'                 => $phone,
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $this->postJson('/api/v1/auth/register', $payload)->assertStatus(201);
    $this->postJson('/api/v1/auth/register', $payload)->assertUnprocessable()
         ->assertJsonValidationErrors(['phone']);
});

it('register returns 422 on duplicate email when email provided', function () {
    $email = 'dup-' . uniqid() . '@example.com';

    $first = [
        'first_name'            => 'First',
        'last_name'             => 'User',
        'phone'                 => '07' . rand(10000000, 99999999),
        'email'                 => $email,
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];
    $second = array_merge($first, ['phone' => '07' . rand(10000000, 99999999)]);

    $this->postJson('/api/v1/auth/register', $first)->assertStatus(201);
    $this->postJson('/api/v1/auth/register', $second)->assertUnprocessable()
         ->assertJsonValidationErrors(['email']);
});

// ─── Login ────────────────────────────────────────────────────────────────────

it('login returns 401 on wrong password', function () {
    $this->postJson('/api/v1/auth/login', [
        'phone'    => '07000000000',
        'password' => 'wrongpassword',
    ])->assertStatus(401);
});

it('login returns 422 when phone missing', function () {
    $this->postJson('/api/v1/auth/login', [
        'password' => 'Password123!',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['phone']);
});

it('login returns 200 with user data after registration', function () {
    $phone    = '07' . rand(10000000, 99999999);
    $password = 'Password123!';

    $this->postJson('/api/v1/auth/register', [
        'first_name'            => 'Login',
        'last_name'             => 'User',
        'phone'                 => $phone,
        'password'              => $password,
        'password_confirmation' => $password,
    ])->assertStatus(201);

    $this->postJson('/api/v1/auth/login', [
        'phone'    => $phone,
        'password' => $password,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.id')
            ->where('data.phone', $phone)
            ->has('data.name')
        );
});

// ─── Me / Logout ──────────────────────────────────────────────────────────────

it('me returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/auth/me')->assertStatus(401);
});

it('logout returns 401 when unauthenticated', function () {
    $this->postJson('/api/v1/auth/logout')->assertStatus(401);
});

it('me returns user data when authenticated', function () {
    $customer = Customer::factory()->create([
        'phone'    => '07' . rand(10000000, 99999999),
        'email'    => null,
        'password' => bcrypt('Password123!'),
    ]);

    $this->actingAs($customer, 'customer')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.id')
            ->has('data.phone')
            ->has('data.name')
        );
});

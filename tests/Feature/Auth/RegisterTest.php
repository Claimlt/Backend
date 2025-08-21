<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('fails if required fields are missing', function () {
    $response = $this->postJson('/api/register', []);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
        'first_name',
        'last_name',
        'email',
        'password',
        'contact_number',
        'nic',
    ]);
});

it('fails if email is invalid', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'contact_number' => '0771234567',
        'nic' => '1234567891',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('fails if password confirmation does not match', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'wrong-password',
        'contact_number' => '0771234567',
        'nic' => '1234567891',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['password']);
});

it('registers successfully with valid data', function () {
    $response = $this->postJson('/api/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'contact_number' => '0771234567',
        'nic' => '1234567891',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'user' => ['id', 'first_name', 'last_name', 'email', 'role', 'status'],
        'token',
    ]);

    $this->assertDatabaseHas('user', [
        'email' => 'john@example.com',
    ]);
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'user']);
});

it('fails login with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

it('fails login with non-existent user', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonuser@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'User not found']);
});

it('fails login with empty email', function () {
    $response = $this->postJson('/api/login', [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('fails login with empty password', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'user@example.com',
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

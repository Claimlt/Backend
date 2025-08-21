<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

// Index tests
it('returns all posts', function () {
    Post::factory()->count(3)->create();

    $response = $this->getJson(route('posts.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'posts') // ✅ check inside posts
        ->assertJsonStructure([
            'posts' => [
                '*' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
            ]
        ]);
});

// Store tests
it('creates a post with valid data', function () {
    $data = [
        'title' => 'Test Post',
        'description' => 'This is a test description.',
        'user_id' => $this->user->id
    ];

    $response = $this->postJson(route('posts.store'), $data);

    $response->assertCreated()
        ->assertJsonStructure([
            'post' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
        ])
        ->assertJsonFragment(['title' => 'Test Post']);

    $this->assertDatabaseHas('post', $data); // ✅ matches your migration table name
});

it('fails to create a post with invalid data', function () {
    $response = $this->postJson(route('posts.store'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'description', 'user_id']);
});

it('fails to create a post with non-existent user', function () {
    $data = [
        'title' => 'Test Post',
        'description' => 'Test Description',
        'user_id' => 9999
    ];

    $response = $this->postJson(route('posts.store'), $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id']);
});

// Show tests
it('returns a single post', function () {
    $post = Post::factory()->create();

    $response = $this->getJson(route('posts.show', $post));

    $response->assertOk()
        ->assertJsonStructure([
            'post' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
        ])
        ->assertJsonFragment(['id' => $post->id, 'title' => $post->title]);
});

it('fails to return a non-existent post', function () {
    $response = $this->getJson(route('posts.show', 9999));

    $response->assertNotFound();
});

// Update tests
it('updates a post with valid data', function () {
    $post = Post::factory()->create();
    $updateData = ['title' => 'Updated Title'];

    $response = $this->putJson(route('posts.update', $post), $updateData);

    $response->assertOk()
        ->assertJsonStructure([
            'post' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
        ])
        ->assertJsonFragment(['title' => 'Updated Title']);

    $this->assertDatabaseHas('post', ['id' => $post->id, 'title' => 'Updated Title']);
});

it('fails to update with invalid data', function () {
    $post = Post::factory()->create();

    $response = $this->putJson(route('posts.update', $post), [
        'title' => '', // Invalid empty title
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('fails to update a non-existent post', function () {
    $response = $this->putJson(route('posts.update', 9999), [
        'title' => 'Updated Title'
    ]);

    $response->assertNotFound();
});

// Destroy tests
it('deletes a post', function () {
    $post = Post::factory()->create();

    $response = $this->deleteJson(route('posts.destroy', $post));

    $response->assertNoContent();

    $this->assertDatabaseMissing('post', ['id' => $post->id]);
});

it('fails to delete a non-existent post', function () {
    $response = $this->deleteJson(route('posts.destroy', 9999));

    $response->assertNotFound();
});

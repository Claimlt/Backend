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
    $this->actingAs($this->user);
    Post::factory()->count(3)->create();

    $response = $this->getJson(route('posts.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
            ]
        ]);
});

// Store tests
it('creates a post with valid data', function () {
    $this->actingAs($this->user);
    $data = [
        'title' => 'Test Post',
        'description' => 'This is a test description.',
    ];

    $response = $this->postJson(route('posts.store'), $data);

    $response->assertCreated()
        ->assertJsonStructure(
            [
                'data' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']

            ]
        )
        ->assertJsonFragment(['title' => 'Test Post']);

    $this->assertDatabaseHas('post', [
        'title' => 'Test Post',
        'description' => 'This is a test description.',
        'user_id' => $this->user->id
    ]);
});

it('fails to create a post with invalid data', function () {
    $this->actingAs($this->user);
    $response = $this->postJson(route('posts.store'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'description']);
});

it('fails to create a post for an unauthenticated user', function () {
    $data = [
        'title' => 'Test Post',
        'description' => 'Test Description',
    ];

    $response = $this->postJson(route('posts.store'), $data);

    $response->assertUnauthorized();
});

// Show tests
it('returns a single post', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson(route('posts.show', $post));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
        ])
        ->assertJsonFragment(['id' => $post->id, 'title' => $post->title]);
});

it('fails to return a non-existent post', function () {
    $this->actingAs($this->user);
    $response = $this->getJson(route('posts.show', 9999));

    $response->assertNotFound();
});

// Update tests
it('updates a post with valid data', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    $updateData = ['title' => 'Updated Title', 'description' => 'Updated Description'];

    $response = $this->putJson(route('posts.update', $post), $updateData);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at']
        ])
        ->assertJsonFragment(['title' => 'Updated Title']);

    $this->assertDatabaseHas('post', ['id' => $post->id, 'title' => 'Updated Title']);
});

it('fails to update a post with invalid data', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson(route('posts.update', $post), [
        'title' => '',
        'description' => 'Updated Description'
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

it('fails to update a post for an unauthenticated user', function () {
    $post = Post::factory()->create();
    $updateData = ['title' => 'Updated Title', 'description' => 'Updated Description'];

    $response = $this->putJson(route('posts.update', $post), $updateData);

    $response->assertUnauthorized();
});

it('fails to update a post that does not belong to the user', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    $updateData = ['title' => 'Updated Title', 'description' => 'Updated Description'];

    $response = $this->putJson(route('posts.update', $post), $updateData);

    $response->assertForbidden();
});


// Destroy tests
it('deletes a post', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson(route('posts.destroy', $post));

    $response->assertNoContent();

    $this->assertDatabaseMissing('post', ['id' => $post->id]);
});

it('fails to delete a non-existent post', function () {
    $this->actingAs($this->user);
    $response = $this->deleteJson(route('posts.destroy', 9999));

    $response->assertNotFound();
});

it('fails to delete a post that does not belong to the user', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson(route('posts.destroy', $post));

    $response->assertForbidden();
});

it('fails to delete a post for an unauthenticated user', function () {
    $post = Post::factory()->create();

    $response = $this->deleteJson(route('posts.destroy', $post));

    $response->assertUnauthorized();
});

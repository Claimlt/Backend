<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('public');
});

// Helper: upload fake images and return UUIDs
function uploadFakeImages($user, $count = 2)
{
    $uuids = [];
    for ($i = 1; $i <= $count; $i++) {
        $file = UploadedFile::fake()->image("test{$i}.jpg");
        $response = test()->actingAs($user)->postJson(route('image-upload'), [
            'type' => 'posts',
            'image' => $file,
        ]);
        $response->assertCreated();
        $uuids[] = Image::latest()->first()->id; // Use UUID if Image model uses it
    }
    return $uuids;
}

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
it('creates a post with valid data including images', function () {
    $this->actingAs($this->user);

    $imageUUIDs = uploadFakeImages($this->user, 2);

    $data = [
        'title' => 'Test Post with Images',
        'description' => 'This is a test description.',
        'images' => $imageUUIDs,
    ];

    $response = $this->postJson(route('posts.store'), $data);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'user_id',
                'created_at',
                'updated_at',
                'images' => [
                    '*' => ['id', 'filename', 'created_at', 'updated_at']
                ]
            ]
        ])
        ->assertJsonFragment(['title' => 'Test Post with Images']);

    $post = Post::first();

    $this->assertDatabaseHas('post', [
        'id' => $post->id,
        'title' => 'Test Post with Images',
        'description' => 'This is a test description.',
        'user_id' => $this->user->id,
    ]);

    foreach ($imageUUIDs as $uuid) {
        $this->assertDatabaseHas('image', [
            'id' => $uuid,
            'imageable_id' => $post->id,
            'imageable_type' => Post::class,
        ]);
        Storage::disk('public')->assertExists(Image::find($uuid)->filename);
    }
});

it('fails to create a post with missing or invalid images', function () {
    $this->actingAs($this->user);

    // Missing images
    $response1 = $this->postJson(route('posts.store'), [
        'title' => 'Test',
        'description' => 'Test Description',
    ]);
    $response1->assertUnprocessable()->assertJsonValidationErrors(['images']);

    // More than 5 images
    $imageUUIDs = uploadFakeImages($this->user, 6);
    $response2 = $this->postJson(route('posts.store'), [
        'title' => 'Test',
        'description' => 'Test Description',
        'images' => $imageUUIDs,
    ]);
    $response2->assertUnprocessable()->assertJsonValidationErrors(['images']);
});

it('fails to create a post for an unauthenticated user', function () {
    $imageUUIDs = Image::factory()->count(2)->create()->pluck('id')->toArray();

    $data = [
        'title' => 'Test Post',
        'description' => 'Test Description',
        'images' => $imageUUIDs,
    ];

    $response = $this->postJson(route('posts.store'), $data);
    $response->assertUnauthorized();
});

// Show tests
it('returns a single post with images', function () {
    $this->actingAs($this->user);

    $imageUUIDs = uploadFakeImages($this->user, 2);
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    foreach ($imageUUIDs as $uuid) {
        Image::find($uuid)->update([
            'imageable_id' => $post->id,
            'imageable_type' => Post::class,
        ]);
    }

    $response = $this->getJson(route('posts.show', $post));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'user_id', 'created_at', 'updated_at', 'images']
        ])
        ->assertJsonFragment(['id' => $post->id, 'title' => $post->title]);
});

it('fails to return a non-existent post', function () {
    $this->actingAs($this->user);
    $response = $this->getJson(route('posts.show', 9999));
    $response->assertNotFound();
});

// Update tests
it('updates a post with valid data and images', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $updateData = [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ];

    $response = $this->putJson(route('posts.update', $post), $updateData);

    $response->assertOk()->assertJsonFragment(['title' => 'Updated Title']);

});

it('fails to update a post with invalid data', function () {
    $this->actingAs($this->user);
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    $response = $this->putJson(route('posts.update', $post), [
        'title' => '',
        'description' => 'Updated Description',
    ]);
    $response->assertUnprocessable()->assertJsonValidationErrors(['title']);
});

it('fails to update a post for an unauthenticated user', function () {
    $post = Post::factory()->create();
    $updateData = ['title' => 'Updated', 'description' => 'Updated Desc', 'images' => []];
    $response = $this->putJson(route('posts.update', $post), $updateData);
    $response->assertUnauthorized();
});

it('fails to update a post that does not belong to the user', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    $updateData = ['title' => 'Updated', 'description' => 'Updated Desc', 'images' => []];
    $response = $this->putJson(route('posts.update', $post), $updateData);
    $response->assertForbidden();
});

// Destroy tests
it('deletes a post along with images', function () {
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

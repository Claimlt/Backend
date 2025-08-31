<?php

use App\Models\Post;
use App\Models\User;
use App\Models\Claim;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Setup
 */
beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

/**
 * INDEX
 */
it('returns a list of claims with user and images', function () {
    $claims = Claim::factory()->count(2)->for($this->user)->create();

    $response = $this->actingAs($this->user)->getJson(route('claims.index'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'message',
                    'user' => ['id', 'first_name'],
                    'images',
                ]
            ]
        ]);
});

/**
 * STORE
 */
it('creates a claim with valid data and images', function () {
    $image = Image::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($this->user)->postJson(route('claims.store'), [
        'message' => 'Test claim',
        'post' => $post->id,
        'images' => [$image->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.message', 'Test claim');

    $this->assertDatabaseHas('claim_request', ['message' => 'Test claim']);
    $this->assertDatabaseHas('image', [
        'id' => $image->id,
        'imageable_id' => Claim::first()->id,
        'imageable_type' => Claim::class,
    ]);
});

it('rejects claim creation without authentication', function () {
    $response = $this->postJson(route('claims.store'), [
        'message' => 'Unauthorized claim',
        'post' => 1,
    ]);

    $response->assertUnauthorized();
});

it('validates claim creation request', function () {
    $response = $this->actingAs($this->user)->postJson(route('claims.store'), [
        'message' => '', // invalid
        'post' => null, // invalid
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message', 'post']);
});

/**
 * SHOW
 */
it('shows a specific claim with user and images', function () {
    $claim = Claim::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->getJson(route('claims.show', $claim));

    $response->assertOk()
        ->assertJsonPath('data.id', (string) $claim->id);
});

/**
 * UPDATE
 */
it('updates a claim successfully when authorized', function () {
    $claim = Claim::factory()->for($this->user)->create([
        'message' => 'Old message'
    ]);

    $response = $this->actingAs($this->user)->putJson(route('claims.update', $claim), [
        'message' => 'Updated message',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.message', 'Updated message');

    $this->assertDatabaseHas('claim_request', ['id' => (string) $claim->id, 'message' => 'Updated message']);
});

it('prevents unauthorized users from updating a claim', function () {
    $claim = Claim::factory()->for($this->otherUser)->create();

    $response = $this->actingAs($this->user)->putJson(route('claims.update', $claim), [
        'message' => 'Hacked',
    ]);

    $response->assertForbidden();
});

it('validates update request', function () {
    $claim = Claim::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->putJson(route('claims.update', $claim), [
        'message' => '', // invalid
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

/**
 * DESTROY
 */
it('deletes a claim with its images when authorized', function () {
    $claim = Claim::factory()->for($this->user)->create();
    $image = Image::factory()->for($claim, 'imageable')->create([
        'filename' => 'test.jpg'
    ]);
    Storage::disk('public')->put($image->filename, 'fake');

    $response = $this->actingAs($this->user)->deleteJson(route('claims.destroy', $claim));

    $response->assertNoContent();

    $this->assertDatabaseMissing('claim_request', ['id' => $claim->id]);
    $this->assertDatabaseMissing('image', ['id' => $image->id]);
    Storage::disk('public')->assertMissing('test.jpg');
});

it('prevents unauthorized users from deleting a claim', function () {
    $claim = Claim::factory()->for($this->otherUser)->create();

    $response = $this->actingAs($this->user)->deleteJson(route('claims.destroy', $claim));

    $response->assertForbidden();
});

it('rejects delete without authentication', function () {
    $claim = Claim::factory()->for($this->user)->create();

    $response = $this->deleteJson(route('claims.destroy', $claim));

    $response->assertUnauthorized();
});

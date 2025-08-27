<?php

use App\Models\User;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('uploads an image successfully', function () {
    $this->actingAs($this->user, 'sanctum');

    Storage::fake('public');

    $file = UploadedFile::fake()->image('example.jpg');

    $response = $this->postJson(route('image-upload'), [
        'type' => 'posts',
        'image' => $file,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'filename',
                'created_at',
                'updated_at',
            ],
        ]);

    $image = Image::first();

    Storage::disk('public')->assertExists($image->filename);

    $this->assertDatabaseHas('image', [
        'id' => $image->id,
        'filename' => $image->filename,
        'imageable_type' => \App\Models\Post::class,
    ]);
});

it('fails to upload an image without type', function () {
    $this->actingAs($this->user, 'sanctum');

    Storage::fake('public');

    $file = UploadedFile::fake()->image('example.jpg');

    $response = $this->postJson(route('image-upload'), [
        'image' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

it('fails to upload a non-image file', function () {
    $this->actingAs($this->user, 'sanctum');

    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson(route('image-upload'), [
        'type' => 'posts',
        'image' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});

it('fails to upload an image for unauthenticated user', function () {

    $file = UploadedFile::fake()->image('example.jpg');

    $response = $this->postJson(route('image-upload'), [
        'type' => 'posts',
        'image' => $file,
    ]);

    $response->assertUnauthorized();
});

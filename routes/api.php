<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return response('Hello World', 200);
});

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login']);
Route::post("/logout", [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post("/images", [ImageController::class, 'store'])->middleware('auth:sanctum')->name("image-upload");
Route::apiResource("/posts", PostController::class)->middleware('auth:sanctum');

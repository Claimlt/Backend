<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login']);
Route::post("/logout", [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get("/profile", [ProfileController::class, 'profile'])->middleware('auth:sanctum');
Route::put("/profile", [ProfileController::class, 'update'])->middleware('auth:sanctum');
Route::post("/profile-avatar", [ProfileController::class, 'updateAvatar'])->middleware('auth:sanctum');

Route::post("/images", [ImageController::class, 'store'])->middleware('auth:sanctum')->name("image-upload");
Route::apiResource("/posts", PostController::class)->middleware('auth:sanctum');
Route::apiResource("/claims", ClaimController::class)->middleware('auth:sanctum');
Route::post("/claims/{claim}/approve", [ClaimController::class, 'approve'])->middleware('auth:sanctum');
Route::get("posts/{post}/claims", [ClaimController::class, 'getByPost'])->middleware('auth:sanctum');
Route::get("/my-claims", [ClaimController::class, 'getByUser'])->middleware('auth:sanctum');

Route::apiResource("/users", UserController::class)->middleware('auth:sanctum');


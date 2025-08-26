<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserDetailsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login']);
Route::post("/logout", [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//User Details
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/details', [UserDetailsController::class, 'show']);
    Route::post('/user/details/update', [UserDetailsController::class, 'updateDetails']);
});

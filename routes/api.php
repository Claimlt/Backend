<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return response('Hello World', 200);
});

Route::post("/register", [AuthController::class, 'register']);
Route::post("/login", [AuthController::class, 'login']);

<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->get('/user', fn (Request $request) => $request->user());

Route::apiResource('events', EventController::class);
Route::apiResource('categories', CategoryController::class);

<?php

use App\Http\Controllers\Api\SoundScapeController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/soundscapes', [SoundScapeController::class, 'index']);
Route::get('/soundscapes/{id}', [SoundScapeController::class, 'show']);
Route::post('/soundscapes/{id}/favorite', [SoundScapeController::class, 'toggleFavorite']);

Route::get('/favorites', [SoundScapeController::class, 'favorites']);
});
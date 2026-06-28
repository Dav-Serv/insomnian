<?php

use App\Http\Controllers\Api\SoundScapeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\SettingAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    // home
    Route::get('/home', [HomeController::class, 'index']);
    // mockSleepLog hanya untuk testing, kalau mau deploy di comment saja codenya
    Route::post('/home/mock-sleep', [HomeController::class, 'mockSleepLog']);

    // setting account
    Route::get('/account', [SettingAccountController::class, 'index']);
    Route::post('/account/update', [SettingAccountController::class, 'update']);

    // soundscape
    Route::get('/soundscapes', [SoundScapeController::class, 'index']);
    Route::get('/soundscapes/{id}', [SoundScapeController::class, 'show']);
    Route::post('/soundscapes/{id}/favorite', [SoundScapeController::class, 'toggleFavorite']);

    // favorites
    Route::get('/favorites', [SoundScapeController::class, 'favorites']);
    Route::put('/soundscapes/{id}/audio-url', [SoundScapeController::class, 'updateAudioUrl']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

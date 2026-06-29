<?php

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminSoundScapeController;
use App\Http\Controllers\Api\SoundScapeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\SettingAccountController;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Controllers\Api\SleepDiaryController;
use App\Http\Controllers\Api\ToolController;
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

    //image
    Route::get('/soundscapes/{id}/thumbnail', [SoundScapeController::class, 'streamThumbnail']);

    Route::middleware(IsAdminMiddleware::class)->prefix('admin')->group(function () {
    //CRUD
    Route::apiResource('users', AdminUserController::class);
    Route::apiResource('soundscapes', AdminSoundScapeController::class);
    });

    // diary
    Route::get('/diary', [SleepDiaryController::class, 'index']);
    Route::post('/diary-tambah', [SleepDiaryController::class, 'store']);

    // tools
    Route::get('/tools', [ToolController::class, 'index']);
    Route::get('/tools/category/{slug}', [ToolController::class, 'getByCategory']);
    Route::get('/tools/{id}', [ToolController::class, 'show']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

// audio (diluar middleware auth:sanctum agar bisa di-stream langsung oleh browser dengan query token)
Route::get('/stream/{id}', [SoundScapeController::class, 'streamAudio']);


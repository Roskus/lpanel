<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes([
    'register' => (env('APP_ENV') != 'production') ? true : false, // Registration Routes...
]);

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');

// Website
Route::get('/site', [App\Http\Controllers\SiteController::class, 'index']);
Route::get('/site/add', [App\Http\Controllers\SiteController::class, 'add']);
Route::get('/site/edit/{id}', [App\Http\Controllers\SiteController::class, 'edit']);
Route::post('/site/save', [App\Http\Controllers\SiteController::class, 'save']);
Route::get('/site/delete/{id}', [App\Http\Controllers\SiteController::class, 'delete']);

// Profile
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'detail']);
Route::post('/profile/save', [App\Http\Controllers\ProfileController::class, 'save']);

// User
Route::get('/user', [App\Http\Controllers\User\UserIndexController::class, 'index']);
Route::get('/user/create', [App\Http\Controllers\User\UserCreateController::class, 'create']);
Route::get('/user/update/{id}', [App\Http\Controllers\User\UserUpdateController::class, 'update']);
Route::post('/user/save', [App\Http\Controllers\User\UserSaveController::class, 'save']);
Route::get('/user/delete/{id}', [App\Http\Controllers\User\UserDeleteController::class, 'delete']);

// Database
Route::get('/database', [App\Http\Controllers\DatabaseController::class, 'index']);
Route::get('/database/add', [App\Http\Controllers\DatabaseController::class, 'add']);
Route::get('/database/edit/{id}', [App\Http\Controllers\DatabaseController::class, 'edit']);
Route::post('/database/save', [App\Http\Controllers\DatabaseController::class, 'save']);

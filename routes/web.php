<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes([
    'register' => true, // Registration Routes...
]);

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');

// Website
Route::get('/site', [App\Http\Controllers\SiteController::class, 'index']);
Route::get('/site/add', [App\Http\Controllers\SiteController::class, 'add']);
Route::get('/site/edit/{id}', [App\Http\Controllers\SiteController::class, 'edit']);
Route::post('/site/save', [App\Http\Controllers\SiteController::class, 'save']);

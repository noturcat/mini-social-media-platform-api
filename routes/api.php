<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TypesenseController;
use App\Http\Controllers\AuthController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:api')->get('/user', [AuthController::class, 'me']);

// Post routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/', [PostController::class, 'store']);
    Route::get('{post}', [PostController::class, 'show']);
    Route::put('{post}', [PostController::class, 'update']);
    Route::delete('{post}', [PostController::class, 'destroy']);
});

// Blog routes
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::post('/', [BlogController::class, 'store']);
    Route::get('{blog}', [BlogController::class, 'show']);
    Route::put('{blog}', [BlogController::class, 'update']);
    Route::delete('{blog}', [BlogController::class, 'destroy']);
});

// Event routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::post('/', [EventController::class, 'store']);
    Route::get('{event}', [EventController::class, 'show']);
    Route::put('{event}', [EventController::class, 'update']);
    Route::delete('{event}', [EventController::class, 'destroy']);
});

// People routes
Route::prefix('people')->group(function () {
    Route::get('/', [PersonController::class, 'index']);
    Route::post('/', [PersonController::class, 'store']);
    Route::get('{person}', [PersonController::class, 'show']);
    Route::put('{person}', [PersonController::class, 'update']);
    Route::delete('{person}', [PersonController::class, 'destroy']);
});

// Typesense
Route::get('/typesense/sync-all', [TypesenseController::class, 'syncAll']);

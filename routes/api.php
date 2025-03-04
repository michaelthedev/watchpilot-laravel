<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});

Route::group(['prefix' => '/auth'], function() {
   Route::post('/login', [LoginController::class, 'login']);

    Route::post('/register', [RegisterController::class, 'register']);
});

Route::group(['prefix' => '/discover'], function() {
   Route::get('/search', [DiscoverController::class, '']);
   Route::get('/airing/{type?}', [DiscoverController::class, '']);
   Route::get('/trending/{type?}', [DiscoverController::class, '']);
   Route::get('/featured/{type?}', [DiscoverController::class, 'featured']);
});

Route::middleware('auth:api')->group(function() {
    Route::apiResource('watchlist', WatchlistController::class);

    Route::group(['prefix' => '/library'], function() {
        Route::get('/likes', [LibraryController::class, 'likes']);
        Route::post('/likes', [LibraryController::class, 'addLike']);
        Route::delete('/likes', [LibraryController::class, 'deleteLike']);

        // watching
        Route::get('/watching', [LibraryController::class, 'watching']);
        Route::post('/watching', [LibraryController::class, 'addWatching']);
        Route::delete('/watching', [LibraryController::class, 'deleteWatching']);

        Route::post('/sync', []);
    });
});

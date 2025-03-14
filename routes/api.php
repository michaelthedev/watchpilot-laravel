<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\MiscController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});

Route::group(['prefix' => '/auth'], function() {
    Route::post('/login', [LoginController::class, 'login']);

    Route::post('/register', [RegisterController::class, 'register']);
});

Route::group(['prefix' => '/discover'], function() {
   Route::get('/search', [DiscoverController::class, 'search']);
   Route::get('/airing/{type?}', [DiscoverController::class, 'airing']);
   Route::get('/trending/{type?}', [DiscoverController::class, 'trending']);
   Route::get('/featured/{type?}', [DiscoverController::class, 'featured']);
});


Route::middleware('auth:api')->group(function() {
    Route::apiResource('watchlist', WatchlistController::class);
    Route::group(['prefix' => '/watchlist'], function() {
        Route::get('/', [WatchlistController::class, 'index']);
        Route::post('/', [WatchlistController::class, 'store']);

        Route::get('/{uid}', [WatchlistController::class, 'show']);
        Route::patch('/{uid}', [WatchlistController::class, 'update']);
        Route::delete('/{uid}', [WatchlistController::class, 'destroy']);

        Route::get('/{uid}/items', [WatchlistController::class, 'items']);
        Route::post('/{uid}/items', [WatchlistController::class, 'addItem']);
        Route::delete('/{uid}/items', [WatchlistController::class, 'removeItem']);
    });

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


Route::group([
    'prefix' => '{type}',
    'controller' => MediaController::class
], function() {
    Route::get('/{id}', 'show');
    Route::get('/{id}/related', 'related');
    Route::get('/{id}/providers', 'providers');
    Route::get('/{id}/seasons/{number}', 'seasons');
})->where(['type' => '^(movie|tv-show)$']);

Route::group(['prefix' => '/misc'], function() {
    Route::get('/timezones', [MiscController::class, 'timezones']);
});

<?php


use Illuminate\Support\Facades\Route;
use Norquensir\Bag\Http\Controllers\IdentifierController;
use Norquensir\Bag\Http\Controllers\PostalController;
use Norquensir\Bag\Http\Controllers\SearchController;

Route::prefix('bag')->group(function () {
    Route::get('/', PostalController::class);
    Route::get('/id/{identifier}', IdentifierController::class);
    Route::get('/s', SearchController::class);
});

<?php

use App\Http\Controllers\IdentifierController;
use App\Http\Controllers\PostalController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('bag')->group(function () {
    Route::get('/', PostalController::class);
    Route::get('/id/{identifier}', IdentifierController::class);
    Route::get('/s', SearchController::class);
});

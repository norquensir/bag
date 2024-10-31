<?php

use App\Http\Controllers\IdentifierController;
use App\Http\Controllers\PostalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', PostalController::class);
    Route::get('/id/{identifier}', IdentifierController::class);
    Route::get('/s', SearchController::class);
    Route::get('/views/postals', [ViewController::class, 'postals']);
});

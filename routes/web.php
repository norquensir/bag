<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentifierController;
use App\Http\Controllers\PostalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', HomeController::class);
    Route::get('/postal', PostalController::class);
    Route::get('/identifier/{identifier}', IdentifierController::class);
    Route::get('/search', SearchController::class);
    Route::get('/views/postals', [ViewController::class, 'postals']);
});

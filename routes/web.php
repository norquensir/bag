<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdentifierController;
use App\Http\Controllers\PostalController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', HomeController::class);
    Route::get('/postal', PostalController::class);
    Route::get('/identifier/{identifier}', IdentifierController::class);
    Route::get('/search', SearchController::class);

    Route::middleware('throttle:downloads')
        ->get('/files/{file}/download', FileController::class)
        ->name('files.download');
});

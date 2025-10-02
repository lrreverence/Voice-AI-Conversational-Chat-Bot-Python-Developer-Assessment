<?php

use Illuminate\Support\Facades\Route;
use Modules\ContactManager\Http\Controllers\Api\ContactController;

Route::get('/', function () {
    return view('app');
});

Route::prefix('api')->group(function () {
    Route::apiResource('contacts', ContactController::class);
});

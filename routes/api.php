<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/sms', [\App\Http\Controllers\Api\SmsController::class, 'send'])
        ->middleware(\App\Http\Middleware\AuthenticateApiKey::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

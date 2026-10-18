<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'business', 'status' => 'ok']);
});

Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/v1.php';
})->middleware('auth.jwt');

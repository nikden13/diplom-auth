<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('auth/tokens', [\App\Http\Controllers\AuthController::class, 'getTokens']);
Route::post('auth/refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);
Route::post('auth/code', [\App\Http\Controllers\AuthController::class, 'generateCode']);
Route::post('auth/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('authCustom');

Route::get('auth/message', [\App\Http\Controllers\AuthController::class, 'getMessageForSign']);
Route::get('auth/check', [\App\Http\Controllers\AuthController::class, 'check']);


<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
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

// Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->post('/users', [UserController::class, 'store']);

// Route::middleware('auth:sanctum')->post('/transfer', [TransactionController::class, 'transfer']);

Route::middleware('auth')->post('/transfer', [TransactionController::class, 'transfer']);

Route::post('/login', [AuthController::class, 'login']);

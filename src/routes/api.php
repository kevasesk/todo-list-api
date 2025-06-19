<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\AuthController;

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

Route::post('/register', [AuthController::class, 'registerApi'])->name('api.register');
Route::post('/login', [AuthController::class, 'loginApi'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logoutApi'])->name('api.logout');

    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('api.tasks.complete');
});

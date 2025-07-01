<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('tasks', TaskController::class);

Route::post('tasks/{task}/done', [TaskController::class, 'markAsDone']);
});

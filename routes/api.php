<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public/projects/{token}', [PublicController::class, 'showProject']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/password', [SettingsController::class, 'updatePassword']);

    Route::get('/tasks', [TaskController::class, 'indexAll']);

    Route::apiResource('clients', ClientController::class);

    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/share', [ProjectController::class, 'share']);
    Route::delete('projects/{project}/share', [ProjectController::class, 'unshare']);

    Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
});
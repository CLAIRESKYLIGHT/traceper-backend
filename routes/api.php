<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\OfficialController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DashboardController;

// 🔓 Public routes
Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Public read endpoints for citizens (all authenticated users can view)
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::get('/barangays', [BarangayController::class, 'index']);
    Route::get('/barangays/{id}', [BarangayController::class, 'show']);
    Route::get('/officials', [OfficialController::class, 'index']);
    Route::get('/officials/{id}', [OfficialController::class, 'show']);
    Route::get('/contractors', [ContractorController::class, 'index']);
    Route::get('/contractors/{id}', [ContractorController::class, 'show']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
    
    // Dashboard statistics (accessible to all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'stats']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']); // Alternative endpoint

    // Admin only: CRUD operations
    Route::middleware('role:admin')->group(function () {
        // Projects CRUD
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::put('/projects/{id}', [ProjectController::class, 'update']);
        Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
        
        // Barangays CRUD
        Route::post('/barangays', [BarangayController::class, 'store']);
        Route::put('/barangays/{id}', [BarangayController::class, 'update']);
        Route::delete('/barangays/{id}', [BarangayController::class, 'destroy']);

        // Officials CRUD
        Route::post('/officials', [OfficialController::class, 'store']);
        Route::put('/officials/{id}', [OfficialController::class, 'update']);
        Route::delete('/officials/{id}', [OfficialController::class, 'destroy']);

        // Contractors CRUD
        Route::post('/contractors', [ContractorController::class, 'store']);
        Route::put('/contractors/{id}', [ContractorController::class, 'update']);
        Route::delete('/contractors/{id}', [ContractorController::class, 'destroy']);

        // Transactions CRUD
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::put('/transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

        // Documents CRUD
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::put('/documents/{id}', [DocumentController::class, 'update']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    });

    // If you want staff to have some privileges (e.g., create transactions),
    // you can create another group:
    Route::middleware('role:admin|staff')->group(function () {
        // staff endpoints here
    });

});
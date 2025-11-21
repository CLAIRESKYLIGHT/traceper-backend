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
use App\Http\Controllers\Api\FinancialRecordController;
use App\Http\Controllers\Api\BarangayIraShareController;

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
    
    // Financial records (read-only for citizens)
    Route::get('/financial-records', [FinancialRecordController::class, 'index']);
    Route::get('/financial-records/{id}', [FinancialRecordController::class, 'show']);
    Route::get('/financial-records/year/{year}', [FinancialRecordController::class, 'getByYear']);
    
    // Barangay IRA shares (read-only for citizens)
    Route::get('/barangay-ira-shares', [BarangayIraShareController::class, 'index']);
    Route::get('/barangay-ira-shares/{id}', [BarangayIraShareController::class, 'show']);
    
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

        // Financial Records CRUD
        Route::post('/financial-records', [FinancialRecordController::class, 'store']);
        Route::put('/financial-records/{id}', [FinancialRecordController::class, 'update']);
        Route::delete('/financial-records/{id}', [FinancialRecordController::class, 'destroy']);

        // Barangay IRA Shares CRUD
        Route::post('/barangay-ira-shares', [BarangayIraShareController::class, 'store']);
        Route::put('/barangay-ira-shares/{id}', [BarangayIraShareController::class, 'update']);
        Route::delete('/barangay-ira-shares/{id}', [BarangayIraShareController::class, 'destroy']);
    });

    // If you want staff to have some privileges (e.g., create transactions),
    // you can create another group:
    Route::middleware('role:admin|staff')->group(function () {
        // staff endpoints here
    });

});
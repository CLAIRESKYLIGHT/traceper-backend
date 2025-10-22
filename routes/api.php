<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\OfficialController;
use App\Http\Controllers\Api\ContractorController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DocumentController;
// 🔓 Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
     // Barangays
    Route::apiResource('barangays', BarangayController::class);
    Route::post('/barangays', [BarangayController::class, 'store']);

    // Officials
    Route::apiResource('officials', OfficialController::class);
    Route::post('/officials', [OfficialController::class, 'store']);

    // Contractors
    Route::apiResource('contractors', ContractorController::class);
    Route::post('/contractors', [ContractorController::class, 'store']);
    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects', [ProjectController::class, 'store']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::post('/documents', [DocumentController::class, 'store']);

});


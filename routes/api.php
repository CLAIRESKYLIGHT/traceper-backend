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


Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});



// Public route
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require valid token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // citizen & above: read routes
    Route::get('/projects', [ProjectController::class,'index']);
    Route::get('/projects/{project}', [ProjectController::class,'show']);
    Route::get('/barangays', [BarangayController::class,'index']);
    Route::get('/barangays/{id}', [BarangayController::class,'show']);
    Route::get('/officials', [OfficialController::class,'index']);
    Route::get('/contractors', [ContractorController::class,'index']);

    // admin/staff: write routes (use middleware/role checks inside controller or a custom middleware)
    Route::middleware('can:admin-or-staff')->group(function(){
        Route::apiResource('projects-admin', ProjectController::class)->except(['index','show']);
        Route::apiResource('barangays-admin', BarangayController::class)->except(['index','show']);
        Route::apiResource('officials-admin', OfficialController::class)->except(['index','show']);
        Route::apiResource('contractors-admin', ContractorController::class)->except(['index','show']);
        Route::post('/projects/{project}/transactions', [TransactionController::class,'store']);
        Route::post('/documents/upload', [DocumentController::class,'store']);
    
    // admin/staff: write routes (use middleware/role checks inside controller or a custom middleware)
    Route::middleware('can:admin-or-staff')->group(function(){
        Route::apiResource('projects-admin', ProjectController::class)->except(['index','show']);
        Route::apiResource('barangays-admin', BarangayController::class)->except(['index','show']);
        Route::apiResource('officials-admin', OfficialController::class)->except(['index','show']);
        Route::apiResource('contractors-admin', ContractorController::class)->except(['index','show']);
        Route::post('/projects/{project}/transactions', [TransactionController::class,'store']);
        Route::post('/documents/upload', [DocumentController::class,'store']);
    });

});
});
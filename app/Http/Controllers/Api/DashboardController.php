<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Barangay;
use App\Models\Contractor;
use App\Models\Official;
use App\Models\Transaction;
use App\Models\Document;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function stats()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'projects' => Project::count(),
                    'barangays' => Barangay::count(),
                    'contractors' => Contractor::count(),
                    'officials' => Official::count(),
                    'transactions' => Transaction::count(),
                    'documents' => Document::count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

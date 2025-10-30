<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Barangay;
use App\Models\Contractor;
use App\Models\Official;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'projects' => Project::count(),
            'barangays' => Barangay::count(),
            'contractors' => Contractor::count(),
            'officials' => Official::count(),
        ]);
    }
}

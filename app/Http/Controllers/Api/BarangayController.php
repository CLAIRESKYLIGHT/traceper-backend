<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\Http\Request;


class BarangayController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    // Get all barangays
   public function index(Request $request)
{
    $query = Barangay::withCount(['officials', 'projects']);
    
    // Optionally filter by year for IRA shares
    $year = $request->input('year', date('Y'));
    
    $barangays = $query->get()->map(function ($barangay) use ($year) {
        // Get current year IRA share
        $currentIraShare = $barangay->iraShares()
            ->where('year', $year)
            ->first();
        
        $barangayData = $barangay->toArray();
        $barangayData['current_ira_share'] = $currentIraShare ? (float) $currentIraShare->ira_share : null;
        $barangayData['ira_share_year'] = $currentIraShare ? $currentIraShare->year : null;
        
        return $barangayData;
    });
    
    return response()->json($barangays);
}

public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'population' => 'nullable|integer',
        'status' => 'nullable|string',
    ]);

    $barangay = Barangay::create($data);
    return response()->json($barangay, 201);
}

public function show($id, Request $request)
{
    $barangay = Barangay::with(['officials', 'projects'])->findOrFail($id);
    
    // Get all IRA shares for this barangay
    $iraShares = $barangay->iraShares()->orderBy('year', 'desc')->get();
    
    // Get current year (or specified year) IRA share
    $year = $request->input('year', date('Y'));
    $currentIraShare = $iraShares->where('year', $year)->first();
    
    // Calculate totals
    $totalBudgetAllocated = $barangay->projects()->sum('budget_allocated');
    $totalAmountSpent = $barangay->projects()->sum('amount_spent');
    
    $response = [
        'id' => $barangay->id,
        'name' => $barangay->name,
        'description' => $barangay->description,
        'population' => $barangay->population,
        'status' => $barangay->status,
        'officials_count' => $barangay->officials()->count(),
        'projects_count' => $barangay->projects()->count(),
        'officials' => $barangay->officials,
        'projects' => $barangay->projects,
        'financial_summary' => [
            'total_budget_allocated' => (float) $totalBudgetAllocated,
            'total_amount_spent' => (float) $totalAmountSpent,
            'remaining_budget' => (float) ($totalBudgetAllocated - $totalAmountSpent),
        ],
        'ira_shares' => $iraShares->map(function ($share) {
            return [
                'year' => $share->year,
                'ira_share' => (float) $share->ira_share,
                'notes' => $share->notes,
            ];
        }),
        'current_ira_share' => $currentIraShare ? [
            'year' => $currentIraShare->year,
            'ira_share' => (float) $currentIraShare->ira_share,
            'notes' => $currentIraShare->notes,
        ] : null,
    ];
    
    return response()->json($response);
}

public function update(Request $request, $id)
{
    $barangay = Barangay::findOrFail($id);
    $barangay->update($request->only('name', 'description', 'population', 'status'));
    return response()->json($barangay);
}

public function destroy($id)
{
    $barangay = Barangay::findOrFail($id);
    $barangay->delete();
    return response()->json(['message' => 'Barangay deleted successfully']);
}
}

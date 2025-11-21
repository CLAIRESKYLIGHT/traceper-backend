<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BarangayIraShare;

class BarangayIraShareController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    // Get all barangay IRA shares
    public function index(Request $request)
    {
        $query = BarangayIraShare::with('barangay');
        
        // Filter by year if provided
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }
        
        $shares = $query->orderBy('ira_share', 'desc')->get();
        return response()->json($shares);
    }

    // Get single barangay IRA share
    public function show($id)
    {
        $share = BarangayIraShare::with('barangay')->findOrFail($id);
        return response()->json($share);
    }

    // Create new barangay IRA share
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barangay_id' => 'required|integer|exists:barangays,id',
            'year' => 'required|integer',
            'ira_share' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if record already exists for this barangay and year
        $existing = BarangayIraShare::where('barangay_id', $validated['barangay_id'])
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'IRA share already exists for this barangay and year.',
                'data' => $existing
            ], 422);
        }

        $share = BarangayIraShare::create($validated);

        return response()->json([
            'message' => 'Barangay IRA share created successfully.',
            'data' => $share->load('barangay')
        ], 201);
    }

    // Update barangay IRA share
    public function update(Request $request, $id)
    {
        $share = BarangayIraShare::findOrFail($id);
        
        $validated = $request->validate([
            'barangay_id' => 'sometimes|required|integer|exists:barangays,id',
            'year' => 'sometimes|required|integer',
            'ira_share' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check uniqueness if year or barangay_id is being changed
        if (isset($validated['barangay_id']) || isset($validated['year'])) {
            $barangayId = $validated['barangay_id'] ?? $share->barangay_id;
            $year = $validated['year'] ?? $share->year;
            
            $existing = BarangayIraShare::where('barangay_id', $barangayId)
                ->where('year', $year)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'IRA share already exists for this barangay and year.',
                ], 422);
            }
        }

        $share->update($validated);

        return response()->json([
            'message' => 'Barangay IRA share updated successfully.',
            'data' => $share->load('barangay')
        ]);
    }

    // Delete barangay IRA share
    public function destroy($id)
    {
        $share = BarangayIraShare::findOrFail($id);
        $share->delete();

        return response()->json(['message' => 'Barangay IRA share deleted successfully.']);
    }
}


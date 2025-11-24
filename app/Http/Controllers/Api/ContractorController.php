<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contractor;

class ContractorController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show']);
    }
    // Get all contractors
    public function index()
    {
        // Get all contractors first
        $contractors = Contractor::with(['projects.transactions'])->get();

        // Calculate total_received for each contractor
        $contractors->transform(function($contractor) {
            $totalReceived = $contractor->projects->sum(function($project) {
                return $project->transactions
                    ->filter(function($transaction) {
                        // Include expense transactions (case-insensitive) or null (defaults to expense)
                        $type = strtolower($transaction->type ?? 'expense');
                        return $type === 'expense';
                    })
                    ->sum('amount');
            });
            
            $contractor->total_received = (float) $totalReceived;
            return $contractor;
        });

        return response()->json(['data' => $contractors]);
    }

    // Get one contractor
    public function show($id)
    {
        $contractor = Contractor::with(['projects.transactions'])->findOrFail($id);
        
        // Calculate total_received from expense transactions
        $totalReceived = $contractor->projects->sum(function($project) {
            return $project->transactions
                ->filter(function($transaction) {
                    // Include expense transactions (case-insensitive) or null (defaults to expense)
                    $type = strtolower($transaction->type ?? 'expense');
                    return $type === 'expense';
                })
                ->sum('amount');
        });
        
        $contractor->total_received = (float) $totalReceived;

        return response()->json(['data' => $contractor]);
    }

    // Create a new contractor
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'business_registration' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $contractor = Contractor::create($validated);

        return response()->json([
            'message' => 'Contractor created successfully.',
            'data' => $contractor
        ], 201);
    }

    // Update existing contractor
    public function update(Request $request, $id)
    {
        $contractor = Contractor::findOrFail($id);
        $contractor->update($request->all());

        return response()->json([
            'message' => 'Contractor updated successfully.',
            'data' => $contractor
        ]);
    }

    // Delete contractor
    public function destroy($id)
    {
        Contractor::destroy($id);
        return response()->json(['message' => 'Contractor deleted successfully.']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contractor;

class ContractorController extends Controller
{
    // Get all contractors
    public function index()
    {
        return response()->json(Contractor::all());
    }

    // Get one contractor
    public function show($id)
    {
        $contractor = Contractor::findOrFail($id);
        return response()->json($contractor);
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

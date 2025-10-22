<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barangay;

class BarangayController extends Controller
{
    // List all barangays
    public function index()
    {
        return response()->json(Barangay::all());
    }

    // Show single barangay
    public function show($id)
    {
        $barangay = Barangay::findOrFail($id);
        return response()->json($barangay);
    }

    // Create new barangay
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $barangay = Barangay::create($validated);

        return response()->json([
            'message' => 'Barangay created successfully',
            'data' => $barangay
        ], 201);
    }

    // Update barangay
    public function update(Request $request, $id)
    {
        $barangay = Barangay::findOrFail($id);
        $barangay->update($request->all());

        return response()->json([
            'message' => 'Barangay updated successfully',
            'data' => $barangay
        ]);
    }

    // Delete barangay
    public function destroy($id)
    {
        Barangay::destroy($id);
        return response()->json(['message' => 'Barangay deleted successfully']);
    }
}

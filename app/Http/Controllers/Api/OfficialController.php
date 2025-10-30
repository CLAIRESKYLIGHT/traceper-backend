<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Official;

class OfficialController extends Controller
{
        public function __construct()
        {
            $this->middleware('auth:sanctum');
            $this->middleware('role:admin')->except(['index', 'show']);
        }
    // List all officials
    public function index()
    {
        $officials = Official::with('barangay')->get();
        return response()->json($officials);
    }

    // Show one official
    public function show($id)
    {
        $official = Official::with('barangay')->findOrFail($id);
        return response()->json($official);
    }

    // Create new official
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'type' => 'required|in:elected,appointed',
            'contact_info' => 'nullable|string|max:255',
            'barangay_id' => 'nullable|exists:barangays,id',
        ]);

        $official = Official::create($validated);

        return response()->json([
            'message' => 'Official created successfully.',
            'data' => $official
        ], 201);
    }

    // Update existing official
    public function update(Request $request, $id)
    {
        $official = Official::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:elected,appointed',
            'contact_info' => 'nullable|string|max:255',
            'barangay_id' => 'nullable|exists:barangays,id',
        ]);

        $official->update($validated);

        return response()->json([
            'message' => 'Official updated successfully.',
            'data' => $official
        ]);
    }

    // Delete official
    public function destroy($id)
    {
        Official::destroy($id);
        return response()->json(['message' => 'Official deleted successfully.']);
    }
}

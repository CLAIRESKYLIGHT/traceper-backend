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
    $data = $request->validate([
        'barangay_id' => 'required|exists:barangays,id',
        'name' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'term' => 'nullable|string|max:255',
    ]);

    $official = Official::create($data);
    return response()->json($official, 201);
}

public function update(Request $request, $id)
{
    $official = Official::findOrFail($id);
    $official->update($request->only('name', 'position', 'term'));
    return response()->json($official);
}

public function destroy($id)
{
    $official = Official::findOrFail($id);
    $official->delete();
    return response()->json(['message' => 'Official deleted successfully']);
}
}

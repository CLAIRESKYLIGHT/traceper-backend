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
   public function index()
{
    return Barangay::withCount(['officials', 'projects'])->get();
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

public function show($id)
{
    $barangay = Barangay::with(['officials', 'projects'])->findOrFail($id);
    return response()->json($barangay);
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

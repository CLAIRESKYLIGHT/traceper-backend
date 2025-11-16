<?php

namespace App\Http\Controllers;

use App\Models\Official;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
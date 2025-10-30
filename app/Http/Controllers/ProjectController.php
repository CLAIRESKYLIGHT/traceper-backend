<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Project::with(['barangay', 'contractor'])->get());
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'barangay_id' => 'required|exists:barangays,id',
            'contractor_id' => 'required|exists:contractors,id',
            'budget_allocated' => 'required|numeric|min:1',
            'amount_spent' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'status' => 'required|string|in:Not Started,In Progress,Completed,Delayed,Cancelled',
            'objectives' => 'nullable|string',
            'estimated_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'message' => '✅ Project created successfully!',
            'data' => $project,
        ], 201);
    }

    /**
     * Display a single project with relationships.
     */
    public function show(Project $project)
    {
        return response()->json($project->load(['barangay', 'contractor', 'documents', 'transactions']));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, $id)
    {
    $project = Project::findOrFail($id);

    $validated = $request->validate([
        'title' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'barangay_id' => 'nullable|exists:barangays,id',
        'contractor_id' => 'nullable|exists:contractors,id',
        'total_cost' => 'nullable|numeric',
        'budget_allocated' => 'nullable|numeric',
        'amount_spent' => 'nullable|numeric',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
        'objectives' => 'nullable|string',
        'status' => 'nullable|string',
    ]);

    $project->update($validated);

    return response()->json([
        'message' => '✅ Project updated successfully',
        'data' => $project
    ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(['message' => '🗑️ Project deleted successfully.']);
    }
}

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
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'budget_allocated' => 'sometimes|required|numeric|min:1',
            'amount_spent' => 'nullable|numeric|min:0',
            'status' => 'sometimes|required|string|in:Not Started,In Progress,Completed,Delayed,Cancelled',
            'start_date' => 'nullable|date',
            'estimated_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => '✅ Project updated successfully!',
            'data' => $project,
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

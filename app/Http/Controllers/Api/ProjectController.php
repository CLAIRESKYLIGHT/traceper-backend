<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    // Get all projects with relations
        public function index()
        {
            $projects = Project::with(['barangay', 'contractor', 'officials', 'documents'])->get();
            return response()->json($projects);
        }

    // Show single project
    public function show($id)
    {
        $project = Project::with(['barangay', 'contractor', 'officials'])->findOrFail($id);
        return response()->json($project);
    }

    // Create new project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
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

        $project = Project::create($validated);

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => $project
        ], 201);
    }

    // Update project
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());

        return response()->json([
            'message' => 'Project updated successfully.',
            'data' => $project
        ]);
    }

    // Delete project
    public function destroy($id)
    {
        Project::destroy($id);
        return response()->json(['message' => 'Project deleted successfully.']);
    }
}

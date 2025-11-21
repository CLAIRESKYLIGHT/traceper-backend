<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{

        public function __construct()
        {
            $this->middleware('auth:sanctum');
            $this->middleware('role:admin')->except(['index', 'show']);
        }
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
        // Convert empty strings to null for nullable fields
        $input = $request->all();
        $nullableFields = ['description', 'barangay_id', 'contractor_id', 'budget_allocated', 'amount_spent', 'start_date', 'estimated_completion_date', 'actual_completion_date', 'objectives', 'status'];
        
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        $validated = validator($input, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'barangay_id' => 'nullable|exists:barangays,id',
            'contractor_id' => 'nullable|exists:contractors,id',
            'budget_allocated' => 'nullable|numeric',
            'amount_spent' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'estimated_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'objectives' => 'nullable|string',
            'status' => 'nullable|string|in:Not Started,In Progress,Completed,Delayed,Cancelled',
        ])->validate();

        // Ensure description is set (even if null) and set defaults for required fields
        $projectData = array_merge($validated, [
            'description' => $validated['description'] ?? null,
            'amount_spent' => $validated['amount_spent'] ?? 0,
            'status' => $validated['status'] ?? 'Not Started',
        ]);

        $project = Project::create($projectData);

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

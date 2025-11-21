<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    
    
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show', 'download']);
    }
    // Get all documents
    public function index()
    {
        $documents = Document::with('project')->get();
        return response()->json($documents);
    }

    // Upload new document
    public function store(Request $request)
    {
        // Convert empty strings to null for nullable fields and ensure project_id is integer
        $input = $request->all();
        
        // Convert project_id to integer if it's a string
        if (isset($input['project_id'])) {
            $input['project_id'] = is_numeric($input['project_id']) ? (int)$input['project_id'] : $input['project_id'];
        }
        
        // Convert empty strings to null for nullable fields
        $nullableFields = ['type', 'description'];
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        $validated = validator($input, [
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
        ])->validate();

        // Save file to storage/app/public/documents
        $path = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'type' => $validated['type'] ?? null,
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'data' => $document->load('project')
        ], 201);
    }

    // Get single document
    public function show($id)
    {
        $document = Document::with('project')->findOrFail($id);
        return response()->json($document);
    }

    // Download document file
    public function download($id)
    {
        $document = Document::findOrFail($id);
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found.'], 404);
        }
        
        return response()->download($filePath);
    }

    // Delete document
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }
}

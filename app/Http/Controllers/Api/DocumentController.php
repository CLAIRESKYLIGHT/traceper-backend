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
        $this->middleware('role:admin')->except(['index', 'show']);
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
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
        ]);

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
            'data' => $document
        ], 201);
    }

    // Download document
    public function show($id)
    {
        $document = Document::findOrFail($id);
        return response()->download(storage_path('app/public/' . $document->file_path));
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

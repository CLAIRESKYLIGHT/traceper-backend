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
        $documents = Document::with(['project', 'transaction'])->get();
        return response()->json($documents);
    }

    // Upload new document
    public function store(Request $request)
    {
        // Convert empty strings to null for nullable fields and ensure IDs are integers
        $input = $request->all();
        
        // Convert project_id to integer if it's a string
        if (isset($input['project_id'])) {
            if ($input['project_id'] === '' || $input['project_id'] === null) {
                $input['project_id'] = null;
            } else {
                $input['project_id'] = is_numeric($input['project_id']) ? (int)$input['project_id'] : $input['project_id'];
            }
        }
        
        // Convert transaction_id to integer if it's a string
        if (isset($input['transaction_id'])) {
            if ($input['transaction_id'] === '' || $input['transaction_id'] === null) {
                $input['transaction_id'] = null;
            } else {
                $input['transaction_id'] = is_numeric($input['transaction_id']) ? (int)$input['transaction_id'] : $input['transaction_id'];
            }
        }
        
        // Convert empty strings to null for nullable fields
        $nullableFields = ['type', 'description', 'project_id', 'transaction_id'];
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        // Check if this is an update to existing placeholder document
        $isUpdate = $request->has('document_id');
        
        // If transaction_id is provided, get project_id from transaction BEFORE validation
        // This ensures project_id is available and doesn't need to be sent from frontend
        if (!empty($input['transaction_id']) && empty($input['project_id'])) {
            try {
                $transaction = \App\Models\Transaction::findOrFail($input['transaction_id']);
                $input['project_id'] = $transaction->project_id;
            } catch (\Exception $e) {
                // Transaction not found - validation will catch this
            }
        }
        
        $validated = validator($input, [
            'project_id' => 'nullable|integer|exists:projects,id',
            'transaction_id' => 'nullable|integer|exists:transactions,id',
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => $isUpdate ? 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048' : 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
            'document_id' => 'sometimes|integer|exists:documents,id',
        ])->after(function ($validator) use ($input, $isUpdate) {
            // At least one of project_id or transaction_id must be provided (unless updating)
            if (!$isUpdate && empty($input['project_id']) && empty($input['transaction_id'])) {
                $validator->errors()->add('project_id', 'Either project_id or transaction_id must be provided.');
                $validator->errors()->add('transaction_id', 'Either project_id or transaction_id must be provided.');
            }
        })->validate();

        // Final check: If transaction_id is provided but project_id is still missing, get it from transaction
        if (!empty($validated['transaction_id']) && empty($validated['project_id'])) {
            $transaction = \App\Models\Transaction::findOrFail($validated['transaction_id']);
            $validated['project_id'] = $transaction->project_id;
        }
        
        // Ensure project_id is set (required for database)
        if (empty($validated['project_id'])) {
            return response()->json([
                'message' => 'Project ID is required. Either provide project_id or transaction_id.',
                'errors' => ['project_id' => ['Project ID is required']]
            ], 422);
        }

        // Check if updating existing document (placeholder) or creating new
        $documentId = $request->input('document_id');
        
        if ($documentId) {
            // Update existing document (placeholder) with file
            $document = Document::findOrFail($documentId);
            
            // Delete old file if exists
            if ($document->file_path && $request->hasFile('file')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
            }
            
            // Save new file if provided
            $path = $request->hasFile('file') 
                ? $request->file('file')->store('documents', 'public')
                : $document->file_path;
            
            $document->update([
                'title' => $validated['title'] ?? $document->title,
                'type' => $validated['type'] ?? $document->type,
                'description' => $validated['description'] ?? $document->description,
                'file_path' => $path,
            ]);
        } else {
            // Create new document
            $path = $request->hasFile('file') 
                ? $request->file('file')->store('documents', 'public')
                : null;

            $document = Document::create([
                'project_id' => $validated['project_id'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'title' => $validated['title'],
                'type' => $validated['type'] ?? null,
                'description' => $validated['description'] ?? null,
                'file_path' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'data' => $document->load(['project', 'transaction'])
        ], 201);
    }

    // Get single document
    public function show($id)
    {
        $document = Document::with(['project', 'transaction'])->findOrFail($id);
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

    // Update document (especially for uploading file to placeholder)
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $input = $request->all();
        
        // Convert empty strings to null
        $nullableFields = ['type', 'description'];
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        $validated = validator($input, [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
        ])->validate();

        // Delete old file if new file is being uploaded
        if ($request->hasFile('file') && $document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Save new file if provided
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $document->update($validated);

        return response()->json([
            'message' => 'Document updated successfully.',
            'data' => $document->load(['project', 'transaction'])
        ]);
    }

    // Delete document
    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }
}

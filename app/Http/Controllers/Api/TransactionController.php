<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Project;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    // List all transactions
    public function index()
    {
        $transactions = Transaction::with(['project', 'official'])->get();
        return response()->json($transactions);
    }

    // Show one transaction
    public function show($id)
    {
        $transaction = Transaction::with(['project', 'official'])->findOrFail($id);
        return response()->json($transaction);
    }

    // Create a new transaction
    public function store(Request $request)
    {
        // Convert empty strings to null for nullable fields and ensure proper types
        $input = $request->all();
        
        // Convert project_id to integer if it's a string
        if (isset($input['project_id'])) {
            $input['project_id'] = is_numeric($input['project_id']) ? (int)$input['project_id'] : $input['project_id'];
        }
        
        // Convert official_id to integer if it's a string, or null if empty
        if (isset($input['official_id'])) {
            if ($input['official_id'] === '' || $input['official_id'] === null) {
                $input['official_id'] = null;
            } else {
                $input['official_id'] = is_numeric($input['official_id']) ? (int)$input['official_id'] : $input['official_id'];
            }
        }
        
        // Convert empty strings to null for nullable fields
        $nullableFields = ['description', 'official_id'];
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }
        
        // Set default transaction_date to today if not provided or empty
        if (!isset($input['transaction_date']) || $input['transaction_date'] === '') {
            $input['transaction_date'] = now()->toDateString();
        }

        $validated = validator($input, [
            'project_id' => 'required|integer|exists:projects,id',
            'official_id' => 'nullable|integer|exists:officials,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ])->validate();

        $transaction = Transaction::create($validated);

        // Optional: automatically update project's amount_spent
        $project = Project::find($validated['project_id']);
        if ($project) {
            $project->increment('amount_spent', $validated['amount']);
        }

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'data' => $transaction->load(['project', 'official'])
        ], 201);
    }

    // Update a transaction
    public function update(Request $request, $id)
    {
        // Convert empty strings to null for nullable fields and ensure proper types
        $input = $request->all();
        
        // Convert project_id to integer if it's a string
        if (isset($input['project_id'])) {
            $input['project_id'] = is_numeric($input['project_id']) ? (int)$input['project_id'] : $input['project_id'];
        }
        
        // Convert official_id to integer if it's a string, or null if empty
        if (isset($input['official_id'])) {
            if ($input['official_id'] === '' || $input['official_id'] === null) {
                $input['official_id'] = null;
            } else {
                $input['official_id'] = is_numeric($input['official_id']) ? (int)$input['official_id'] : $input['official_id'];
            }
        }
        
        // Convert empty strings to null for nullable fields
        $nullableFields = ['description', 'official_id'];
        foreach ($nullableFields as $field) {
            if (isset($input[$field]) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        $validated = validator($input, [
            'project_id' => 'sometimes|required|integer|exists:projects,id',
            'official_id' => 'nullable|integer|exists:officials,id',
            'transaction_date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
        ])->validate();

        $transaction = Transaction::findOrFail($id);
        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'data' => $transaction->load(['project', 'official'])
        ]);
    }

    // Delete a transaction
    public function destroy($id)
    {
        Transaction::destroy($id);
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }
}

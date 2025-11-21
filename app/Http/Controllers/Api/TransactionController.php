<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Project;
use App\Models\Document;

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
        $transactions = Transaction::with(['project', 'official', 'documents'])->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'project_id' => $transaction->project_id,
                'transaction_date' => $transaction->transaction_date,
                'type' => ucfirst($transaction->type), // Capitalize: "Income" or "Expense"
                'amount' => (float) $transaction->amount,
                'official_id' => $transaction->official_id,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
                'project' => $transaction->project ? [
                    'id' => $transaction->project->id,
                    'title' => $transaction->project->title,
                ] : null,
                'official' => $transaction->official ? [
                    'id' => $transaction->official->id,
                    'name' => $transaction->official->name,
                    'position' => $transaction->official->position,
                ] : null,
                'documents' => $transaction->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_path' => $doc->file_path,
                    ];
                }),
            ];
        });
        return response()->json($transactions);
    }

    // Show one transaction
    public function show($id)
    {
        $transaction = Transaction::with(['project', 'official', 'documents'])->findOrFail($id);
        return response()->json([
            'id' => $transaction->id,
            'project_id' => $transaction->project_id,
            'transaction_date' => $transaction->transaction_date,
            'type' => ucfirst($transaction->type), // Capitalize: "Income" or "Expense"
            'amount' => (float) $transaction->amount,
            'official_id' => $transaction->official_id,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'project' => $transaction->project ? [
                'id' => $transaction->project->id,
                'title' => $transaction->project->title,
            ] : null,
            'official' => $transaction->official ? [
                'id' => $transaction->official->id,
                'name' => $transaction->official->name,
                'position' => $transaction->official->position,
            ] : null,
            'documents' => $transaction->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'file_path' => $doc->file_path,
                ];
            }),
        ]);
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
        
        // Normalize and set default type - accept "Income" or "Expense" (capitalized)
        if (!isset($input['type']) || $input['type'] === '') {
            $input['type'] = 'expense';
        } else {
            // Normalize to lowercase for storage, but accept both cases
            $input['type'] = strtolower($input['type']);
            // Accept both "income"/"Income" and "expense"/"Expense"
            if ($input['type'] === 'income') {
                $input['type'] = 'income';
            } elseif ($input['type'] === 'expense') {
                $input['type'] = 'expense';
            } else {
                // If invalid, default to expense
                $input['type'] = 'expense';
            }
        }

        $validated = validator($input, [
            'project_id' => 'required|integer|exists:projects,id',
            'official_id' => 'nullable|integer|exists:officials,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|gt:0', // Greater than 0
            'type' => 'required|string|in:income,expense,Income,Expense',
        ])->validate();
        
        // Ensure type is lowercase for storage
        $validated['type'] = strtolower($validated['type']);

        $transaction = Transaction::create($validated);

        // Update project's amount_spent based on transaction type
        $project = Project::find($validated['project_id']);
        if ($project) {
            if ($validated['type'] === 'expense') {
                // Expenses increase amount_spent
                $project->increment('amount_spent', $validated['amount']);
            } elseif ($validated['type'] === 'income') {
                // Income decreases amount_spent (reduces spending)
                $project->decrement('amount_spent', $validated['amount']);
                // Ensure amount_spent doesn't go below 0
                if ($project->amount_spent < 0) {
                    $project->update(['amount_spent' => 0]);
                }
            }
        }

        // Automatically create a document entry for this transaction
        // This ensures every transaction has a document placeholder ready for file upload
        $project = Project::find($validated['project_id']);
        $official = $transaction->official;
        
        $documentTitle = $validated['description'] 
            ? "Transaction Document - {$validated['description']}"
            : "Transaction Document - ₱" . number_format($validated['amount'], 2) . " - " . $validated['transaction_date'];
        
        $documentDescription = "Transaction #{$transaction->id} for Project: " . ($project->title ?? 'N/A') . "\n" .
            "Amount: ₱" . number_format($validated['amount'], 2) . "\n" .
            ($validated['description'] ? "Description: {$validated['description']}\n" : '') .
            ($official ? "Authorized by: {$official->name} ({$official->position})\n" : '') .
            "Date: {$validated['transaction_date']}";

        Document::create([
            'project_id' => $validated['project_id'],
            'transaction_id' => $transaction->id,
            'title' => $documentTitle,
            'type' => 'receipt', // Default type, can be changed when file is uploaded
            'description' => $documentDescription,
            'file_path' => null, // Null until file is uploaded
        ]);

        $transaction->load(['project', 'official', 'documents']);
        
        return response()->json([
            'id' => $transaction->id,
            'project_id' => $transaction->project_id,
            'transaction_date' => $transaction->transaction_date,
            'type' => ucfirst($transaction->type), // Capitalize: "Income" or "Expense"
            'amount' => (float) $transaction->amount,
            'official_id' => $transaction->official_id,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'project' => $transaction->project ? [
                'id' => $transaction->project->id,
                'title' => $transaction->project->title,
            ] : null,
            'official' => $transaction->official ? [
                'id' => $transaction->official->id,
                'name' => $transaction->official->name,
                'position' => $transaction->official->position,
            ] : null,
            'documents' => $transaction->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'file_path' => $doc->file_path,
                ];
            }),
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
        
        // Normalize type if provided - accept "Income" or "Expense" (capitalized)
        if (isset($input['type']) && $input['type'] !== '') {
            $input['type'] = strtolower($input['type']);
            // Accept both "income"/"Income" and "expense"/"Expense"
            if ($input['type'] === 'income') {
                $input['type'] = 'income';
            } elseif ($input['type'] === 'expense') {
                $input['type'] = 'expense';
            } else {
                // If invalid, default to expense
                $input['type'] = 'expense';
            }
        } else {
            // If not provided, keep existing type or default to expense
            $existingTransaction = Transaction::find($id);
            if ($existingTransaction && $existingTransaction->type) {
                $input['type'] = $existingTransaction->type;
            } else {
                $input['type'] = 'expense';
            }
        }

        $validated = validator($input, [
            'project_id' => 'sometimes|required|integer|exists:projects,id',
            'official_id' => 'nullable|integer|exists:officials,id',
            'transaction_date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:255',
            'amount' => 'sometimes|required|numeric|gt:0', // Greater than 0
            'type' => 'sometimes|required|string|in:income,expense,Income,Expense',
        ])->validate();
        
        // Ensure type is lowercase for storage
        if (isset($validated['type'])) {
            $validated['type'] = strtolower($validated['type']);
        }

        $transaction = Transaction::findOrFail($id);
        $oldAmount = $transaction->amount;
        $oldType = $transaction->type ?? 'expense';
        
        $transaction->update($validated);
        
        // Update project's amount_spent if amount or type changed
        if ($transaction->project && ($oldAmount != $validated['amount'] || $oldType != $validated['type'])) {
            // Revert old transaction impact
            if ($oldType === 'expense') {
                $transaction->project->decrement('amount_spent', $oldAmount);
                // Ensure amount_spent doesn't go below 0
                if ($transaction->project->amount_spent < 0) {
                    $transaction->project->update(['amount_spent' => 0]);
                }
            } elseif ($oldType === 'income') {
                $transaction->project->increment('amount_spent', $oldAmount);
            }
            
            // Apply new transaction impact
            if ($validated['type'] === 'expense') {
                $transaction->project->increment('amount_spent', $validated['amount']);
            } elseif ($validated['type'] === 'income') {
                $transaction->project->decrement('amount_spent', $validated['amount']);
                // Ensure amount_spent doesn't go below 0
                if ($transaction->project->amount_spent < 0) {
                    $transaction->project->update(['amount_spent' => 0]);
                }
            }
        }

        $transaction->load(['project', 'official', 'documents']);
        
        return response()->json([
            'id' => $transaction->id,
            'project_id' => $transaction->project_id,
            'transaction_date' => $transaction->transaction_date,
            'type' => ucfirst($transaction->type), // Capitalize: "Income" or "Expense"
            'amount' => (float) $transaction->amount,
            'official_id' => $transaction->official_id,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'project' => $transaction->project ? [
                'id' => $transaction->project->id,
                'title' => $transaction->project->title,
            ] : null,
            'official' => $transaction->official ? [
                'id' => $transaction->official->id,
                'name' => $transaction->official->name,
                'position' => $transaction->official->position,
            ] : null,
            'documents' => $transaction->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'file_path' => $doc->file_path,
                ];
            }),
        ]);
    }

    // Delete a transaction
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $amount = $transaction->amount;
        $type = $transaction->type ?? 'expense';
        
        // Revert the transaction's impact on project's amount_spent
        if ($transaction->project) {
            if ($type === 'expense') {
                $transaction->project->decrement('amount_spent', $amount);
                // Ensure amount_spent doesn't go below 0
                if ($transaction->project->amount_spent < 0) {
                    $transaction->project->update(['amount_spent' => 0]);
                }
            } elseif ($type === 'income') {
                $transaction->project->increment('amount_spent', $amount);
            }
        }
        
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }
}

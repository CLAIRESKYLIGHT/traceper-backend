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
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'official_id' => 'nullable|exists:officials,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $transaction = Transaction::create($validated);

        // Optional: automatically update project's amount_spent
        $project = Project::find($validated['project_id']);
        $project->increment('amount_spent', $validated['amount']);

        return response()->json([
            'message' => 'Transaction recorded successfully.',
            'data' => $transaction
        ], 201);
    }

    // Update a transaction
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());

        return response()->json([
            'message' => 'Transaction updated successfully.',
            'data' => $transaction
        ]);
    }

    // Delete a transaction
    public function destroy($id)
    {
        Transaction::destroy($id);
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }
}

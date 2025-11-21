<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialRecord;

class FinancialRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    // Get all financial records
    public function index()
    {
        $records = FinancialRecord::orderBy('year', 'desc')->get()->map(function ($record) {
            return [
                'id' => $record->id,
                'year' => $record->year,
                'total_revenue' => (float) $record->total_revenue,
                'ira_allocation' => (float) ($record->ira_allocation ?? 0),
                'service_business_income' => (float) ($record->service_business_income ?? 0),
                'local_tax_collections' => (float) ($record->local_tax_collections ?? 0),
                'total_expenditures' => (float) ($record->total_expenditures ?? 0),
                'personnel_services' => (float) ($record->personnel_services ?? 0),
                'maintenance_operating_expenses' => (float) ($record->maintenance_operating_expenses ?? 0),
                'capital_outlay' => (float) ($record->capital_outlay ?? 0),
                'fiscal_balance' => (float) ($record->fiscal_balance ?? 0),
                'total_assets' => (float) ($record->total_assets ?? 0),
                'total_liabilities' => (float) ($record->total_liabilities ?? 0),
                'net_equity' => (float) ($record->net_equity ?? 0),
                'created_at' => $record->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
                'updated_at' => $record->updated_at?->format('Y-m-d\TH:i:s.000000\Z'),
            ];
        });
        // Support both formats: array or wrapped in "data"
        return response()->json($records);
    }

    // Get single financial record
    public function show($id)
    {
        $record = FinancialRecord::findOrFail($id);
        return response()->json([
            'id' => $record->id,
            'year' => $record->year,
            'total_revenue' => (float) $record->total_revenue,
            'ira_allocation' => (float) ($record->ira_allocation ?? 0),
            'service_business_income' => (float) ($record->service_business_income ?? 0),
            'local_tax_collections' => (float) ($record->local_tax_collections ?? 0),
            'total_expenditures' => (float) ($record->total_expenditures ?? 0),
            'personnel_services' => (float) ($record->personnel_services ?? 0),
            'maintenance_operating_expenses' => (float) ($record->maintenance_operating_expenses ?? 0),
            'capital_outlay' => (float) ($record->capital_outlay ?? 0),
            'fiscal_balance' => (float) ($record->fiscal_balance ?? 0),
            'total_assets' => (float) ($record->total_assets ?? 0),
            'total_liabilities' => (float) ($record->total_liabilities ?? 0),
            'net_equity' => (float) ($record->net_equity ?? 0),
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    // Get financial record by year
    public function getByYear($year)
    {
        $record = FinancialRecord::where('year', $year)->first();
        
        if (!$record) {
            return response()->json([
                'message' => 'Financial record not found for year ' . $year
            ], 404);
        }
        
        return response()->json([
            'id' => $record->id,
            'year' => $record->year,
            'total_revenue' => (float) $record->total_revenue,
            'ira_allocation' => (float) ($record->ira_allocation ?? 0),
            'service_business_income' => (float) ($record->service_business_income ?? 0),
            'local_tax_collections' => (float) ($record->local_tax_collections ?? 0),
            'total_expenditures' => (float) ($record->total_expenditures ?? 0),
            'personnel_services' => (float) ($record->personnel_services ?? 0),
            'maintenance_operating_expenses' => (float) ($record->maintenance_operating_expenses ?? 0),
            'capital_outlay' => (float) ($record->capital_outlay ?? 0),
            'fiscal_balance' => (float) ($record->fiscal_balance ?? 0),
            'total_assets' => (float) ($record->total_assets ?? 0),
            'total_liabilities' => (float) ($record->total_liabilities ?? 0),
            'net_equity' => (float) ($record->net_equity ?? 0),
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    // Create new financial record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|unique:financial_records,year',
            'total_revenue' => 'nullable|numeric|min:0',
            'ira_allocation' => 'nullable|numeric|min:0',
            'service_business_income' => 'nullable|numeric|min:0',
            'local_tax_collections' => 'nullable|numeric|min:0',
            'total_expenditures' => 'nullable|numeric|min:0',
            'personnel_services' => 'nullable|numeric|min:0',
            'maintenance_operating_expenses' => 'nullable|numeric|min:0',
            'capital_outlay' => 'nullable|numeric|min:0',
            'fiscal_balance' => 'nullable|numeric',
            'total_assets' => 'nullable|numeric|min:0',
            'total_liabilities' => 'nullable|numeric|min:0',
            'net_equity' => 'nullable|numeric',
        ]);

        // Set defaults to 0 for numeric fields if not provided
        $defaults = [
            'total_revenue' => 0,
            'ira_allocation' => 0,
            'service_business_income' => 0,
            'local_tax_collections' => 0,
            'total_expenditures' => 0,
            'personnel_services' => 0,
            'maintenance_operating_expenses' => 0,
            'capital_outlay' => 0,
            'fiscal_balance' => 0,
            'total_assets' => 0,
            'total_liabilities' => 0,
            'net_equity' => 0,
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($validated[$key])) {
                $validated[$key] = $defaultValue;
            }
        }

        // Remove total_revenue from validated data - it will be calculated automatically
        unset($validated['total_revenue']);

        $record = FinancialRecord::create($validated);

        return response()->json([
            'id' => $record->id,
            'year' => $record->year,
            'total_revenue' => (float) $record->total_revenue,
            'ira_allocation' => (float) ($record->ira_allocation ?? 0),
            'service_business_income' => (float) ($record->service_business_income ?? 0),
            'local_tax_collections' => (float) ($record->local_tax_collections ?? 0),
            'total_expenditures' => (float) ($record->total_expenditures ?? 0),
            'personnel_services' => (float) ($record->personnel_services ?? 0),
            'maintenance_operating_expenses' => (float) ($record->maintenance_operating_expenses ?? 0),
            'capital_outlay' => (float) ($record->capital_outlay ?? 0),
            'fiscal_balance' => (float) ($record->fiscal_balance ?? 0),
            'total_assets' => (float) ($record->total_assets ?? 0),
            'total_liabilities' => (float) ($record->total_liabilities ?? 0),
            'net_equity' => (float) ($record->net_equity ?? 0),
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),
        ], 201);
    }

    // Update financial record
    public function update(Request $request, $id)
    {
        $record = FinancialRecord::findOrFail($id);
        
        $validated = $request->validate([
            // Year is read-only - cannot be changed
            'total_revenue' => 'nullable|numeric|min:0',
            'ira_allocation' => 'nullable|numeric|min:0',
            'service_business_income' => 'nullable|numeric|min:0',
            'local_tax_collections' => 'nullable|numeric|min:0',
            'total_expenditures' => 'nullable|numeric|min:0',
            'personnel_services' => 'nullable|numeric|min:0',
            'maintenance_operating_expenses' => 'nullable|numeric|min:0',
            'capital_outlay' => 'nullable|numeric|min:0',
            'fiscal_balance' => 'nullable|numeric',
            'total_assets' => 'nullable|numeric|min:0',
            'total_liabilities' => 'nullable|numeric|min:0',
            'net_equity' => 'nullable|numeric',
        ]);

        // Remove total_revenue from validated data - it will be calculated automatically
        unset($validated['total_revenue']);

        $record->update($validated);

        return response()->json([
            'id' => $record->id,
            'year' => $record->year,
            'total_revenue' => (float) $record->total_revenue,
            'ira_allocation' => (float) ($record->ira_allocation ?? 0),
            'service_business_income' => (float) ($record->service_business_income ?? 0),
            'local_tax_collections' => (float) ($record->local_tax_collections ?? 0),
            'total_expenditures' => (float) ($record->total_expenditures ?? 0),
            'personnel_services' => (float) ($record->personnel_services ?? 0),
            'maintenance_operating_expenses' => (float) ($record->maintenance_operating_expenses ?? 0),
            'capital_outlay' => (float) ($record->capital_outlay ?? 0),
            'fiscal_balance' => (float) ($record->fiscal_balance ?? 0),
            'total_assets' => (float) ($record->total_assets ?? 0),
            'total_liabilities' => (float) ($record->total_liabilities ?? 0),
            'net_equity' => (float) ($record->net_equity ?? 0),
            'created_at' => $record->created_at?->toIso8601String(),
            'updated_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    // Delete financial record
    public function destroy($id)
    {
        $record = FinancialRecord::findOrFail($id);
        $record->delete();

        return response()->json(['message' => 'Financial record deleted successfully.']);
    }
}


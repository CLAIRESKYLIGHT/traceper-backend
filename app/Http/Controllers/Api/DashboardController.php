<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Barangay;
use App\Models\Contractor;
use App\Models\Official;
use App\Models\Transaction;
use App\Models\Document;
use App\Models\FinancialRecord;
use App\Models\BarangayIraShare;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function stats()
    {
        try {
            // Get current year and previous year for comparison
            $currentYear = date('Y');
            $previousYear = $currentYear - 1;
            
            // Get financial records
            $currentFinancial = FinancialRecord::where('year', $currentYear)->first();
            $previousFinancial = FinancialRecord::where('year', $previousYear)->first();
            
            // Calculate project financials
            $totalBudgetAllocated = Project::sum('budget_allocated');
            $totalAmountSpent = Project::sum('amount_spent');
            $totalRemainingBudget = $totalBudgetAllocated - $totalAmountSpent;
            
            // Calculate transaction totals by type
            $totalExpenses = Transaction::where('type', 'expense')->sum('amount');
            $totalIncome = Transaction::where('type', 'income')->sum('amount');
            $totalTransactions = Transaction::sum('amount');
            
            // Get latest financial record (most recent year)
            $latestFinancial = FinancialRecord::orderBy('year', 'desc')->first();
            
            // Calculate revenue growth if we have previous year data
            $revenueGrowth = null;
            if ($currentFinancial && $previousFinancial) {
                $revenueGrowth = $currentFinancial->total_revenue - $previousFinancial->total_revenue;
            }
            
            // Get barangay IRA shares for current year
            $barangayIraShares = BarangayIraShare::where('year', $currentYear)
                ->with('barangay')
                ->orderBy('ira_share', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    // Basic counts
                    'projects' => Project::count(),
                    'barangays' => Barangay::count(),
                    'contractors' => Contractor::count(),
                    'officials' => Official::count(),
                    'transactions' => Transaction::count(),
                    'documents' => Document::count(),
                    
                    // Project financials
                    'project_financials' => [
                        'total_budget_allocated' => (float) $totalBudgetAllocated,
                        'total_amount_spent' => (float) $totalAmountSpent,
                        'total_remaining_budget' => (float) $totalRemainingBudget,
                        'total_transactions' => (float) $totalTransactions,
                        'total_expenses' => (float) $totalExpenses,
                        'total_income' => (float) $totalIncome,
                    ],
                    
                    // Municipal financials (current year)
                    'financials' => $currentFinancial ? [
                        'year' => $currentFinancial->year,
                        'revenue' => [
                            'total' => (float) $currentFinancial->total_revenue,
                            'ira_allocation' => (float) $currentFinancial->ira_allocation,
                            'service_business_income' => (float) $currentFinancial->service_business_income,
                            'local_tax_collections' => (float) $currentFinancial->local_tax_collections,
                            'property_tax' => (float) $currentFinancial->property_tax,
                            'goods_services_tax' => (float) $currentFinancial->goods_services_tax,
                        ],
                        'expenditures' => [
                            'total' => (float) $currentFinancial->total_expenditures,
                            'personnel_services' => (float) $currentFinancial->personnel_services,
                            'maintenance_operating' => (float) $currentFinancial->maintenance_operating_expenses,
                            'capital_outlay' => (float) $currentFinancial->capital_outlay,
                        ],
                        'project_spending' => [
                            'total_expenses' => (float) $currentFinancial->total_project_expenses,
                            'total_income' => (float) $currentFinancial->total_project_income,
                            'net_project_spending' => (float) ($currentFinancial->total_project_expenses - $currentFinancial->total_project_income),
                        ],
                        'budget_summary' => [
                            'total_revenue' => (float) $currentFinancial->total_revenue,
                            'official_expenditures' => (float) $currentFinancial->total_expenditures,
                            'project_expenses' => (float) $currentFinancial->total_project_expenses,
                            'project_income' => (float) $currentFinancial->total_project_income,
                            'total_actual_expenditures' => (float) $currentFinancial->total_actual_expenditures,
                            'available_budget' => (float) $currentFinancial->available_budget,
                        ],
                        'fiscal_balance' => (float) $currentFinancial->fiscal_balance,
                        'assets' => [
                            'total' => (float) $currentFinancial->total_assets,
                            'liabilities' => (float) $currentFinancial->total_liabilities,
                            'net_equity' => (float) $currentFinancial->net_equity,
                        ],
                        'revenue_growth' => $revenueGrowth ? (float) $revenueGrowth : null,
                    ] : null,
                    
                    // Previous year comparison
                    'previous_year' => $previousFinancial ? [
                        'year' => $previousFinancial->year,
                        'total_revenue' => (float) $previousFinancial->total_revenue,
                        'total_expenditures' => (float) $previousFinancial->total_expenditures,
                        'fiscal_balance' => (float) $previousFinancial->fiscal_balance,
                    ] : null,
                    
                    // Barangay IRA shares
                    'barangay_ira_shares' => $barangayIraShares->map(function ($share) {
                        return [
                            'barangay_id' => $share->barangay_id,
                            'barangay_name' => $share->barangay->name,
                            'ira_share' => (float) $share->ira_share,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

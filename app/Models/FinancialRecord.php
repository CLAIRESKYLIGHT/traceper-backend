<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'total_revenue',
        'ira_allocation',
        'service_business_income',
        'local_tax_collections',
        'property_tax',
        'goods_services_tax',
        'total_expenditures',
        'personnel_services',
        'maintenance_operating_expenses',
        'capital_outlay',
        'fiscal_balance',
        'total_assets',
        'total_liabilities',
        'net_equity',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_revenue' => 'decimal:2',
        'ira_allocation' => 'decimal:2',
        'service_business_income' => 'decimal:2',
        'local_tax_collections' => 'decimal:2',
        'property_tax' => 'decimal:2',
        'goods_services_tax' => 'decimal:2',
        'total_expenditures' => 'decimal:2',
        'personnel_services' => 'decimal:2',
        'maintenance_operating_expenses' => 'decimal:2',
        'capital_outlay' => 'decimal:2',
        'fiscal_balance' => 'decimal:2',
        'total_assets' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'net_equity' => 'decimal:2',
    ];

    /**
     * Calculate total revenue from all revenue components
     */
    public function calculateTotalRevenue(): float
    {
        $ira = $this->attributes['ira_allocation'] ?? $this->ira_allocation ?? 0;
        $service = $this->attributes['service_business_income'] ?? $this->service_business_income ?? 0;
        $localTax = $this->attributes['local_tax_collections'] ?? $this->local_tax_collections ?? 0;
        $propertyTax = $this->attributes['property_tax'] ?? $this->property_tax ?? 0;
        $goodsTax = $this->attributes['goods_services_tax'] ?? $this->goods_services_tax ?? 0;
        
        return (float) ($ira + $service + $localTax + $propertyTax + $goodsTax);
    }

    /**
     * Get total revenue - always returns calculated value if stored value is 0 or null
     */
    public function getTotalRevenueAttribute($value)
    {
        // If stored value is null or 0, calculate it from components
        if ($value === null || $value == 0) {
            return $this->calculateTotalRevenue();
        }
        return (float) $value;
    }

    /**
     * Boot method to auto-calculate total_revenue before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($record) {
            // Always calculate total_revenue from components before saving
            $record->attributes['total_revenue'] = $record->calculateTotalRevenue();
        });
    }

    /**
     * Get all transactions for this financial record year
     */
    public function getTransactionsForYear()
    {
        return \App\Models\Transaction::whereYear('transaction_date', $this->year);
    }

    /**
     * Calculate total project spending (expenses) for this year
     */
    public function getTotalProjectExpensesAttribute(): float
    {
        return (float) \App\Models\Transaction::whereYear('transaction_date', $this->year)
            ->where('type', 'expense')
            ->sum('amount');
    }

    /**
     * Calculate total project income for this year
     */
    public function getTotalProjectIncomeAttribute(): float
    {
        return (float) \App\Models\Transaction::whereYear('transaction_date', $this->year)
            ->where('type', 'income')
            ->sum('amount');
    }

    /**
     * Calculate available budget (revenue - official expenditures - project expenses + project income)
     */
    public function getAvailableBudgetAttribute(): float
    {
        $revenue = $this->total_revenue;
        $officialExpenditures = $this->total_expenditures ?? 0;
        $projectExpenses = $this->total_project_expenses;
        $projectIncome = $this->total_project_income;
        
        return (float) ($revenue - $officialExpenditures - $projectExpenses + $projectIncome);
    }

    /**
     * Calculate total actual expenditures (official + project expenses)
     */
    public function getTotalActualExpendituresAttribute(): float
    {
        $officialExpenditures = $this->total_expenditures ?? 0;
        $projectExpenses = $this->total_project_expenses;
        
        return (float) ($officialExpenditures + $projectExpenses);
    }
}


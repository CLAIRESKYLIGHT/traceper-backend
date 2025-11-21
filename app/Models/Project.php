<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
        'contractor_id',
        'title',
        'description',
        'objectives',
        'budget_allocated',
        'amount_spent',
        'status',
        'start_date',
        'estimated_completion_date',
        'actual_completion_date',
    ];

    // Relationships
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function officials()
    {
        return $this->belongsToMany(Official::class, 'project_officials');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Calculate remaining budget
    public function getRemainingBudgetAttribute()
    {
        return $this->budget_allocated - $this->amount_spent;
    }

    // Get total transactions amount (should match amount_spent)
    public function getTotalTransactionsAttribute()
    {
        return $this->transactions()->sum('amount');
    }
}

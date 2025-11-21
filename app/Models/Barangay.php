<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Barangay extends Model
{
    protected $fillable = ['name', 'description', 'population', 'status'];

    public function officials()
    {
        return $this->hasMany(Official::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // Get total budget allocated for all projects in this barangay
    public function getTotalBudgetAllocatedAttribute()
    {
        return $this->projects()->sum('budget_allocated');
    }

    // Get total amount spent for all projects in this barangay
    public function getTotalAmountSpentAttribute()
    {
        return $this->projects()->sum('amount_spent');
    }
}

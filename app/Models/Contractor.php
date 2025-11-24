<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'business_registration',
        'contact_info',
        'address',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // Calculate total amount received from all expense transactions
    public function getTotalReceivedAttribute()
    {
        // If projects are already loaded with transactions, use them
        if ($this->relationLoaded('projects')) {
            return (float) $this->projects->sum(function($project) {
                if ($project->relationLoaded('transactions')) {
                    return $project->transactions
                        ->filter(function($transaction) {
                            $type = strtolower($transaction->type ?? 'expense');
                            return $type === 'expense';
                        })
                        ->sum('amount');
                }
                return 0;
            });
        }
        
        // Otherwise, calculate from database
        return (float) \App\Models\Transaction::whereHas('project', function($query) {
            $query->where('contractor_id', $this->id);
        })
        ->where(function($query) {
            $query->whereRaw('LOWER(type) = ?', ['expense'])
                  ->orWhereNull('type');
        })
        ->sum('amount');
    }
}

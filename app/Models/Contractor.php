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

    // Calculate total amount received from all projects
    public function getTotalReceivedAttribute()
    {
        return $this->projects()->sum('amount_spent');
    }
}

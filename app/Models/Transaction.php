<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'official_id',
        'transaction_date',
        'description',
        'amount',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function official()
    {
        return $this->belongsTo(Official::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}

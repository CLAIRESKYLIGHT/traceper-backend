<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangayIraShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
        'year',
        'ira_share',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'ira_share' => 'decimal:2',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}


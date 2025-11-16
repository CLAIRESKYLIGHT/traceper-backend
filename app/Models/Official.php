<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    protected $fillable = ['barangay_id', 'name', 'position', 'term'];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}

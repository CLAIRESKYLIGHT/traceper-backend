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
}

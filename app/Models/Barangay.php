<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barangay extends Model
{
    //
use HasFactory;
  protected $fillable = ['name','description'];
  public function projects(){ return $this->hasMany(Project::class); }
  public function officials(){ return $this->hasMany(Official::class); }
}

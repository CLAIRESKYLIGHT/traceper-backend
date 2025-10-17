<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    //
    use HasFactory;
  protected $fillable = ['name','position','type','contact','photo','barangay_id'];
  public function barangay(){ return $this->belongsTo(Barangay::class); }
  public function projects(){ return $this->belongsToMany(Project::class,'project_officials')->withPivot('role_in_project'); }
}

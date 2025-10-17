<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
use HasFactory;
  protected $fillable = ['title','description','objectives','budget_allocated','amount_spent','start_date','estimated_completion_date','actual_completion_date','status','barangay_id','contractor_id'];
  public function barangay(){ return $this->belongsTo(Barangay::class); }
  public function contractor(){ return $this->belongsTo(Contractor::class); }
  public function transactions(){ return $this->hasMany(Transaction::class); }
  public function documents(){ return $this->hasMany(Document::class); }
  public function officials(){ return $this->belongsToMany(Official::class,'project_officials')->withPivot('role_in_project'); }
}

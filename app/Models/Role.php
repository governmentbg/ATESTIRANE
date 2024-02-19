<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory, SoftDeletes;
    
    public function users(){
        return $this->belongsToMany(User::class);
    }
    
    public function local_admin_role(){
        return $this->belongsToMany(User::class)->wherePivot('role_id', '=', 2);
    }
    
    public function central_admin_role(){
        return $this->belongsToMany(User::class)->wherePivot('role_id', '=', 1);
    }
}

<?php

namespace App\Models;

use App\Models\Attestation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use HasFactory, SoftDeletes;
    
    public function attestation(){
        return $this->belongsTo(Attestation::class);
    }

    public function members(){
        return $this->belongsToMany(User::class, 'commission_members', 'commission_id', 'user_id');
    }
    
    public function evaluated_members(){
        return $this->belongsToMany(User::class, 'commission_evaluated_members', 'commission_id', 'user_id');
    }

    public function director(){
        return $this->belongsTo(User::class, 'director_id');
    }
}

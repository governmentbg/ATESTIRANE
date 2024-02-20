<?php

namespace App\Models;

use App\Models\AttestationForm;
use App\Models\Commission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attestation extends Model
{
    use HasFactory;

    public function forms(){
        return $this->hasMany(AttestationForm::class);
    }

    public function commissions(){
        return $this->hasMany(Commission::class);
    }
}

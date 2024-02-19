<?php

namespace App\Models;

use App\Models\User;
use App\Models\AttestationForm;
use App\Models\AttestationFormScoreSignature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttestationFormScore extends Model
{   
    use HasFactory, SoftDeletes;

    public function status_user(){
        return $this->belongsTo(User::class, 'status_by');
    }

    public function attestation_form(){
        return $this->belongsTo(AttestationForm::class);
    }

    public function signatures(){
        return $this->hasMany(AttestationFormScoreSignature::class);
    }

    protected $casts = [
        'scores' => 'object',
        'add_info' => 'object'
    ];

}

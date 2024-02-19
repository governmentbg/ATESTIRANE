<?php

namespace App\Models;

use App\Models\User;
use App\Models\Attestation;
use App\Models\AttestationFormGoal;
use App\Models\AttestationFormMeeting;
use App\Models\AttestationFormScore;
use App\Models\Commission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttestationForm extends Model
{   
    use HasFactory;
    
    public function attestation(){
        return $this->belongsTo(Attestation::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function director(){
        return $this->belongsTo(User::class, 'director_id');
    }

    public function commission(){
        return $this->belongsTo(Commission::class);
    }

    public function active_goals(){
        return $this->hasOne(AttestationFormGoal::class);
    }

    public function archive_goals(){
        return $this->hasMany(AttestationFormGoal::class)->onlyTrashed();
    }

    public function meeting(){
        return $this->hasOne(AttestationFormMeeting::class);
    }

    public function scores(){
        return $this->hasOne(AttestationFormScore::class);
    }

    protected $casts = [
        'personal_data' => 'object'
    ];
}

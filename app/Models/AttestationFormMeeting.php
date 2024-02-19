<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttestationFormMeeting extends Model
{   
    use HasFactory, SoftDeletes;

    public function requested_user(){
        return $this->belongsTo(User::class, 'requested_by');
    }

}

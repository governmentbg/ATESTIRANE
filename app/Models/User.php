<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Position;
use App\Models\Organisation;
use App\Models\Commission;
use App\Models\AttestationForm;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function position(){
        return $this->hasOne(Position::class, 'id', 'position_id');
    }
    
    public function organisation(){
        return $this->hasOne(Organisation::class, 'id', 'organisation_id');
    }

    public function roles(){
        return $this->belongsToMany(Role::class);
    }

    public function commissions(){
        return $this->belongsToMany(Commission::class, 'commission_members', 'user_id', 'commission_id' );
    }

    public function evaluated_by_commissions(){
        return $this->belongsToMany(Commission::class, 'commission_evaluated_members', 'user_id', 'commission_id' );
    }

    public function director_commissions(){
        return $this->hasMany(Commission::class, 'director_id');
    }

    public function attestation_forms(){
        return $this->hasMany(AttestationForm::class);
    }
}

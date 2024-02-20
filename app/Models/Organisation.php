<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use App\Models\User;
use App\Models\AttestationForm;

use DB;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;
    
    public function users(){
        return $this->hasMany(User::class, 'organisation_id', 'id');
    }

    static function get_all_child_organisations($organisation_id = 0){
        // local administrator get organisations for this user
        
        $expression = DB::raw("WITH RECURSIVE rectree AS (
        SELECT * 
            FROM organisations 
            WHERE id = :organisation_id
        UNION ALL 
        SELECT t.* 
            FROM organisations t 
            JOIN rectree
                ON t.parent_id = rectree.id
        ) SELECT * FROM rectree ORDER BY id;");
        
        $string = $expression->getValue(DB::connection()->getQueryGrammar());
        
        $organisations = DB::select( $string, array(
            'organisation_id' => $organisation_id
        ));
        
        return $organisations;
    }

    static function get_grand_parent_organisation($organisation_id = 0){
        // local administrator get organisations for this user
        
        $expression = DB::raw("WITH RECURSIVE rectree AS (
        SELECT * 
            FROM organisations 
            WHERE id = :organisation_id
        UNION ALL 
        SELECT t.* 
            FROM organisations t 
            JOIN rectree
                ON t.id = rectree.parent_id
        ) SELECT * FROM rectree WHERE parent_id = 0 ORDER BY id;");
        
        $string = $expression->getValue(DB::connection()->getQueryGrammar());
        
        $organisation = DB::select( $string, array(
            'organisation_id' => $organisation_id
        ));
        
        return $organisation[0];
    }

    public function attestation_forms(): HasManyThrough
    {
        return $this->hasManyThrough(AttestationForm::class, User::class);
    }
}

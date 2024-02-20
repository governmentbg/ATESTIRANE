<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use App\Models\Role;
use Illuminate\Http\Request;

class AttestationController extends Controller
{
    public function add(){
        $attestation = Attestation::where('status', '=', 'active')->first();
        
        if($attestation){
            $attestation->period_from = date('d-m-Y', strtotime($attestation->period_from));
            $attestation->period_to = date('d-m-Y', strtotime($attestation->period_to));
        }
        
        return view('attestation.add', compact('attestation'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'management_form_version' => 'required'
        ];
        
        if(($request->filled('period_from') == true && $request->filled('period_to') == false) || ($request->filled('period_from') == false && $request->filled('period_to') == true)){
            return redirect()->back()->with('error', 'Трябва да попълните начална и крайна дата или ги оставете празни.');
        }
        
        $validated_data = $request->validate($fields);
        
        $new_attestation = new Attestation;
        
        $old_attestation = Attestation::where('status', '=', 'active')->first();
        
        if(empty($old_attestation) && ($request->filled('period_from') == false || $request->filled('period_to') == false) ){
            return redirect()->back()->with('error', 'Задължително трябва да попълните начална и крайна дата.');
        }
        
        if($request->filled('period_from') == false && $request->filled('period_to') == false && !empty($old_attestation)){
            $new_attestation->period_from = date('Y-m-d', strtotime('+1 year', strtotime($old_attestation->period_from)));
            $new_attestation->period_to = date('Y-m-d', strtotime('+1 year', strtotime($old_attestation->period_to)));
        } else {
            $new_attestation->period_from = date('Y-m-d', strtotime($request->input('period_from')));
            $new_attestation->period_to = date('Y-m-d', strtotime($request->input('period_to')));
        }

        $new_attestation->year = date('Y', strtotime($new_attestation->period_to));
        
        $new_attestation->management_form_version = $request->input('management_form_version');
        $new_attestation->status = 'active';
        
        $id = $new_attestation->save();
        log_event('attestation_created', ['attestation' => $new_attestation]);

        $roles = Role::whereIn('id', [3, 5])->get();
        foreach( $roles as $role ){
            $role->users()->detach();
        }
        
        if(!empty($old_attestation)){
            $old_attestation->status = 'completed';
            
            $old_attestation->save();
        }
        
        return redirect()->route('dashboard')->with('success', 'Атестацията беше създадена!');
    }
}

<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use App\Models\User;
use App\Models\Commission;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\AttestationForm;
use App\Models\AttestationFormGoal;
use Illuminate\Http\Request;

class CommissionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function list(Request $request)
    {
        // get all commissions
        $commissions_temp = Commission::orderBy('id', 'asc');
        
        // search
        if($request->filled('approval_order')){
            $commissions_temp->where('approval_order', 'like', '%'.$request->input('approval_order').'%');
        }
        
        if($request->filled('approval_date')){
            $commissions_temp->where('approval_date', '=', date('Y-m-d', strtotime($request->input('approval_date'))));
        }
        
        $commissions = $commissions_temp->paginate(10);
        
        return view('commissions.list', compact('commissions'));
    }
    
    public function edit(int $id = 0)
    {
        if($id){
            $commission = Commission::find($id);
            
            if(!$commission){
                return redirect()->route('commissions.list')->with('error', 'Атестационната комисия не беше намерена!');
            }
            
            $commission->valid_until = date('d.m.Y', strtotime($commission->valid_until));
            $commission->approval_date = date('d.m.Y', strtotime($commission->approval_date));
            
            $selected_members = array();
            $selected_evaluated_members = array();
            
            foreach($commission->members as $member){
                array_push($selected_members, $member->id);
            }
            
            foreach ($commission->evaluated_members as $evaluated_members) {
                array_push($selected_evaluated_members, $evaluated_members->id);
            }
        }
        else{
            $commission = null;
            
            $selected_members = array();
            $selected_evaluated_members = array();
        }
        
        $current_user_organisaton = Auth::user()->organisation_id;
        
        $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
        $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
        
        $org_tree = [];
        foreach($all_child_organisations as $org){
            array_push($org_tree, $org->id);
        }

        $attestation = Session::get('attestation');
        
        $members = User::whereIn('organisation_id', $org_tree)->get();
        $evaluated_members = User::whereIn('organisation_id', $org_tree)
                                ->where('digital_attestation', '=', 1)
                                ->whereDoesntHave('evaluated_by_commissions', function($q) use ($attestation){
                                    $q->where('attestation_id', $attestation->id);
                                })->get();
        
        return view('commissions.edit', compact('id', 'commission', 'members', 'evaluated_members', 'selected_members', 'selected_evaluated_members'));
    }

    public function view(int $id)
    {
        $commission = Commission::find($id);
        
        if(!$commission){
            return redirect()->route('commissions.list')->with('error', 'Атестационната комисия не беше намерена!');
        }
        
        $director_user = User::find($commission->director_id);
        $commission->valid_until = date('d.m.Y', strtotime($commission->valid_until));
        $commission->approval_date = date('d.m.Y', strtotime($commission->approval_date));
        //dd($commission);
        return view('commissions.view', compact('commission', 'director_user'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'members' => 'required',
            'director_id' => 'required',
            'valid_until' => 'required',
            'approval_order' => 'required',
            'approval_date' => 'required',
            'evaluated_members' => 'required'
        ];
        
        $id = $request->input('id');
        
        $validated_data = $request->validate($fields);
        //dd($request->all());
        
        if($id){
            $commission = Commission::find($id);
            
            if(!$commission){
                return redirect()->route('commissions.list')->with('error', 'Атестационната комисия не беше намерена!');
            }
        }
        else{
            $organisation_id = Auth::user()->organisation_id;
            
            $commission = new Commission;

            $attestation = Attestation::where('status', 'active')->orderBy('created_at')->first();
            
            $commission->organisation_id = $organisation_id;
            $commission->attestation_id = $attestation->id;
        }
        
        $old_director_id = $commission->director_id ?? null;
        
        $commission->director_id = $request->input('director_id');
        $commission->valid_until = date('Y-m-d', strtotime($request->input('valid_until')));
        $commission->approval_order = $request->input('approval_order');
        $commission->approval_date = date('Y-m-d', strtotime($request->input('approval_date')));
        
        $commission->save();
        
        if($id){
            log_event('commisions_updated', ['commission' => $commission]);
        }
        else{
            log_event('commisions_created', ['commission' => $commission]);
        }
        
        // проверявам дали не участва в друга комисия;
        $commissions = Commission::where('director_id', '=', $old_director_id)->where('attestation_id', '=', session('attestation_id'))->get();
        if($commissions->isEmpty()){
            $old_director_user = User::find($old_director_id);
            if( $old_director_user ){
                $old_director_user->roles()->detach(3);
            }
        }
        
        $new_director_user = User::find($commission->director_id);
        $new_director_user->roles()->syncWithoutDetaching(3);
        
        // attach members
        $commission->members()->sync($request->input('members'));
        
        // add role = 5 
        foreach($commission->members as $user){
            $result = $user->roles()->syncWithoutDetaching(5);
            
            if($result['attached']){
                log_event('employee_set_role_5', ['user' => $user, 'role_id' => 5]);
            }
        }
        
        // attach evaluated members
        $commission->evaluated_members()->sync($request->input('evaluated_members'));
        foreach( $request->input('evaluated_members') as $evaluated_member ){
           $this->generate_attestation_form($evaluated_member, $commission); 
        }
        
        return redirect()->route('commissions.list')->with('success', 'Атестационната комисия беше '.($id ? 'обновена!' : 'добавена!'));
    }

    public function generate_attestation_form($user_id, $commission){
        $user = User::find($user_id);
        $attestation = Session::get('attestation');

        $attestation_form = AttestationForm::where('user_id', $user->id)->where('attestation_id', $attestation->id)->first(); 
        if( !$attestation_form ){
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($user->organisation_id);

            if( $user->appointment_date >= date('Y-01-01') ){
                $from_date = date('d.m.Y', strtotime($user->appointment_date));
            } else {
                $from_date = date('d.m.Y', strtotime($attestation->period_from));
            }

            $personal_data = [
                'name' => $user->name,
                'position' => $user->position->name,
                'administration' => ($grand_parent_organisation ? $grand_parent_organisation->name:''),
                'organisation' => $user->organisation->name,
                'from_date' => $from_date,
                'to_date' => date('d.m.Y', strtotime($attestation->period_to))
            ];

            $attestation_form = new AttestationForm;
            $attestation_form->attestation_id = $attestation->id;
            $attestation_form->user_id = $user->id;
            $attestation_form->commission_id = $commission->id;
            $attestation_form->director_id = $commission->director_id;
            $attestation_form->type = $user->position->attestation_form_type ?? 'general';
            $attestation_form->personal_data = $personal_data;
            $attestation_form->save();

            $attestation_form_goals = new AttestationFormGoal;
            $attestation_form_goals->attestation_form_id = $attestation_form->id;
            $attestation_form_goals->goals_status = 'preview';
            $attestation_form_goals->save();

            $attestation_form->refresh();
            
        }
    }
}

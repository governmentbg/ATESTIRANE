<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use App\Models\User;
use App\Models\Position;
use App\Models\Organisation;
use App\Models\TotalScoreType;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;
use App\Rules\ValidEGN;

class EmployeesController extends Controller
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
        // get all users
        $users_temp = User::orderBy('id', 'asc')->where('only_evaluate', 0);
        
        // local administrator get organisations for this user
        if(session('role_id') == 2){
            $current_user_organisaton = Auth::user()->organisation_id;
            
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
            $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
            
            $org_tree = [];
            foreach($all_child_organisations as $org){
                array_push($org_tree, $org->id);
            }
            
            // filter users for this administrator only
            $users_temp = User::whereIn('organisation_id', $org_tree)->where('only_evaluate', 0);
            
            $orgs = Organisation::whereIn('id', $org_tree)->orderBy('parent_id', 'asc')->get()->toArray();
        }
        else{
            $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        }
        
        // search
        if($request->filled('name')){
            $users_temp->where('name', 'like', '%'.$request->input('name').'%');
        }
        
        if($request->filled('email')){
            $users_temp->where('email', 'like', '%'.$request->input('email').'%');
        }
        
        if($request->filled('position_id')){
            $users_temp->where('position_id', '=', $request->input('position_id'));
        }
        
        if($request->filled('digital_attestation')){
            $users_temp->where('digital_attestation', '=', $request->input('digital_attestation'));
        }
        
        if($request->filled('organisation_id')){
            $users_temp->where('organisation_id', '=', $request->input('organisation_id'));
        }
        
        $users = $users_temp->paginate(10);
        
        $tree = buildTree($orgs, 'parent_id', 'id');
        
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, $request->input('organisation_id') ?? 0, true, 1, false);
        
        $positions = array();
        
        $management = Position::where('type', '=', 'management')->get();
        $positions['Ръководни длъжности'] = $management;
        
        $experts = Position::where('type', '=', 'experts')->get();
        $positions['Експертни длъжности'] = $experts;
        
        $general = Position::where('type', '=', 'general')->get();
        $positions['Обща/специализирана администрация'] = $general;
        
        $technical = Position::where('type', '=', 'technical')->get();
        $positions['Технически длъжности'] = $technical;
        
        return view('employees.list', compact('users', 'html', 'positions'));
    }

    public function show_search(){
        return view('employees.show_search');
    }
    
    public function search(Request $request){
        //dd($request->all());
        
        $fields = [
            'search_egn' => 'required'
        ];
        
        $validated_data = $request->validate($fields);
        
        $egn = $request->input('search_egn');
        
        $user = User::where('egn', '=', $egn)->withTrashed()->first();
        
        $request->merge(['egn' => $egn]);
        $request->flash();
        
        if(!empty($user)){
            if($user->trashed()){
                // show restore screen
                return redirect()->route('employees.edit', ['id' => $user->id]);
            }
            else{
                if( $user->only_evaluate ){
                    return redirect()->route('employees.show_search')->with('error', 'Има активен потребител с това ЕГН в секция Оценяващи потребители!');
                }
                // local administrator get organisations for this user
                if(session('role_id') == 2){
                    $current_user_organisaton = Auth::user()->organisation_id;
                    
                    $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
                    $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
                    
                    $org_tree = [];
                    foreach($all_child_organisations as $org){
                        array_push($org_tree, $org->id);
                    }
                    
                    if(in_array($user->organisation_id, $org_tree)){
                        // show restore screen
                        return redirect()->route('employees.edit', ['id' => $user->id]);
                    }
                    else{
                        return redirect()->route('employees.list')->with('error', 'Има активен служител с това ЕГН който е в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                    }
                }
                else{
                    // show restore screen
                    return redirect()->route('employees.edit', ['id' => $user->id]);
                }
            }
        }
        else{
            // show add form
            return redirect()->route('employees.edit')->withInput();
        }
    }

    public function edit(int $id = 0)
    {
        if($id){
            $user = User::where('id', '=', $id)->withTrashed()->first();
            
            if(empty($user)){
                return redirect()->route('employees.list')->with('error', 'Електронното досие не беше намерено!');
            }

            $total_score_type = $user->position ? $user->position->type:'management';
        }
        else{
            $user = null;
            $total_score_type = 'management';
        }
        
        // local administrator get organisations for this user
        if(session('role_id') == 2){
            $current_user_organisaton = Auth::user()->organisation_id;
            
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
            $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
            
            $org_tree = [];
            foreach($all_child_organisations as $org){
                array_push($org_tree, $org->id);
            }
            
            if(!empty($user)){
                if($user->trashed() == false && !in_array($user->organisation_id, $org_tree)){
                    return redirect()->route('employees.list')->with('error', 'този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                }
            }
            
            $orgs = Organisation::whereIn('id', $org_tree)->orderBy('parent_id', 'asc')->get()->toArray();
        }
        else{
            $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        }
        
        $tree = buildTree($orgs, 'parent_id', 'id');
        
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, (old('organisation_id') ? old('organisation_id') : $user->organisation_id ?? 0), true, 1, false);
        
        $positions = array();
        
        $management = Position::where('type', '=', 'management')->get();
        $positions['Ръководни длъжности'] = $management;
        
        $experts = Position::where('type', '=', 'experts')->get();
        $positions['Експертни длъжности'] = $experts;
        
        $general = Position::where('type', '=', 'general')->get();
        $positions['Обща/специализирана администрация'] = $general;
        
        $technical = Position::where('type', '=', 'technical')->get();
        $positions['Технически длъжности'] = $technical;
        
        if(!empty($user)){
            $user->appointment_date = !empty($user->appointment_date) ? date('d.m.Y', strtotime($user->appointment_date)) : null;
            $user->reassignment_date = !empty($user->reassignment_date) ? date('d.m.Y', strtotime($user->reassignment_date)) : null;
            $user->leaving_date = !empty($user->leaving_date) ? date('d.m.Y', strtotime($user->leaving_date)) : null;
            $user->returning_date = !empty($user->returning_date) ? date('d.m.Y', strtotime($user->returning_date)) : null;
        }

        $total_score_types = TotalScoreType::where('attestation_form_type', $total_score_type)->get();
        
        return view('employees.edit', compact('id', 'html', 'positions', 'user', 'total_score_types'));
    }

    public function view(int $id)
    {
        $user = User::where('id', '=', $id)->withTrashed()->first();
        
        if(!empty($user)){
            // local administrator get organisations for this user
            if(session('role_id') == 2){
                $current_user_organisaton = Auth::user()->organisation_id;
                
                $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
                $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
                
                $org_tree = [];
                foreach($all_child_organisations as $org){
                    array_push($org_tree, $org->id);
                }
                
                if($user->trashed() == false && !in_array($user->organisation_id, $org_tree)){
                    return redirect()->route('employees.list')->with('error', 'този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                }
            }
            
            $user->appointment_date = !empty($user->appointment_date) ? date('d.m.Y', strtotime($user->appointment_date)) : null;
            $user->reassignment_date = !empty($user->reassignment_date) ? date('d.m.Y', strtotime($user->reassignment_date)) : null;
            $user->leaving_date = !empty($user->leaving_date) ? date('d.m.Y', strtotime($user->leaving_date)) : null;
            $user->returning_date = !empty($user->returning_date) ? date('d.m.Y', strtotime($user->returning_date)) : null;
            
            return view('employees.view', compact('user'));
        }
        else{
            return redirect()->route('employees.list')->with('error', 'Електронното досие не беше намерено!');
        }
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'name' => 'required',
            'email' => 'email|required',
            'rank' => 'required',
            'organisation_id' => 'required',
            'position_id' => 'required',
            'digital_attestation' => 'required',
            'appointment_date' => 'required'
        ];
        
        if($request->has('picture')){
            $fields['picture'] = 'required|file|mimes:jpeg,png,jpg,gif';
        }
        
        $id = $request->input('id');
        
        if($id){
            $user = User::where('id', '=', $id)->withTrashed()->first();
            
            if(empty($user)){
                return redirect()->route('employees.list')->with('error', 'Електронното досие не беше намерено!');
            }
            
            // local administrator get organisations for this user
            if(session('role_id') == 2){
                $current_user_organisaton = Auth::user()->organisation_id;
                
                $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
                $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
                
                $org_tree = [];
                foreach($all_child_organisations as $org){
                    array_push($org_tree, $org->id);
                }
                
                if($user->trashed() == false && !in_array($user->organisation_id, $org_tree)){
                    return redirect()->route('employees.list')->with('error', 'Този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                }
            }
            
            $fields['egn'] = ['required', Rule::unique('users')->ignore($user->id), new ValidEGN];
            // $fields['egn'] = ['required', Rule::unique('users')->ignore($user->id)];
        }
        else{
            $fields['egn'] = ['required', 'unique:users,egn', new ValidEGN];
            // $fields['egn'] = ['required', 'unique:users,egn'];
            
            $user = new User;
        }
        
        $validated_data = $request->validate($fields);
        
        $user->only_evaluate = 0;
        $user->name = $request->input('name');
        $user->egn = $request->input('egn');
        $user->email = $request->input('email');
        $user->rank = $request->input('rank');
        $user->rank_acquisition = $request->input('rank_acquisition');
        $user->organisation_id = $request->input('organisation_id');
        $user->position_id = $request->input('position_id');
        $user->digital_attestation = $request->input('digital_attestation');
        
        $user->appointment_date = $request->filled('appointment_date') ? date('Y-m-d', strtotime($request->input('appointment_date'))) : null;
        $user->reassignment_date = $request->filled('reassignment_date') ? date('Y-m-d', strtotime($request->input('reassignment_date'))) : null;
        $user->leaving_date = $request->filled('leaving_date') ? date('Y-m-d', strtotime($request->input('leaving_date'))) : null;
        $user->returning_date = $request->filled('returning_date') ? date('Y-m-d', strtotime($request->input('returning_date'))) : null;

        $user->old_attestation_year_1 = $request->old_attestation_year_1;
        $user->old_attestation_score_1 = $request->old_attestation_score_1;
        $user->old_attestation_year_2 = $request->old_attestation_year_2;
        $user->old_attestation_score_2 = $request->old_attestation_score_2;
        
        if($user->trashed()){
            $user->reassignment_date = date('Y-m-d');
            
            $user->save();
            
            $user->restore();
            
            log_event('employee_updated', ['user' => $user]);
        }
        else{
            $user->save();
            
            if($id){
                log_event('employee_updated', ['user' => $user]);
            }
            else{
                log_event('employee_created', ['user' => $user]);
            }
            
            if($request->input('digital_attestation') == 1){
                $result = $user->roles()->syncWithoutDetaching(4);
                
                if($result['attached']){
                    log_event('employee_set_role_4', ['user' => $user, 'role_id' => 4]);
                }
            }
            else{
                $result = $user->roles()->detach(4);
                
                if($result){
                    log_event('employee_unset_role_4', ['user' => $user, 'role_id' => 4]);
                }
            }
        }
        
        if($request->has('picture_delete')){
            if(Storage::disk('local')->exists('images/'.$user->photo_url)){
                Storage::delete('images/'.$user->photo_url);
            }
            
            $user->photo_url = null;
            
            $user->save();
        }
        
        // upload image
        if ($request->hasFile('picture')) {
            if ($request->file('picture')->isValid()) {
                // get old file
                if(!empty($user->photo_url)){
                    if(Storage::disk('local')->exists('images/'.$user->photo_url)){
                        Storage::delete('images/'.$user->photo_url);
                    }
                    
                    $user->photo_url = null;
                }
                
                $filename = Str::uuid();
                //$extension = $request->file('picture')->extension();
                
                /*if (empty($extension)) {
                    $extension = $request->file('picture')->getClientOriginalExtension();
                }*/
                
                //$full_name = $filename . '.' . $extension;
                
                $full_name = $filename;
                
                $path = $request->file('picture')->storeAs('images', $full_name, 'local');
                
                $user->photo_url = $full_name;
                
                $user->save();
            }
        }
        
        return redirect()->route('employees.list')->with('success', 'Електронното досие беше '.($id ? 'обновено!' : 'добавено!'));
    }
    
    public function delete(int $id){
        if($id){
            if(Auth::user()->id != $id){
                $user = User::where('id', '=', $id)->first();
                
                if(!empty($user)){
                    // local administrator get organisations for this user
                    if(session('role_id') == 2){
                        $current_user_organisaton = Auth::user()->organisation_id;
                        
                        $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
                        $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
                        
                        $org_tree = [];
                        foreach($all_child_organisations as $org){
                            array_push($org_tree, $org->id);
                        }
                        
                        if(!in_array($user->organisation_id, $org_tree)){
                            return redirect()->route('employees.list')->with('error', 'Този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                        }
                    }
                    
                    $user->organisation_id = null;
                    
                    $user->save();
                    
                    $user->delete();
                    
                    log_event('employee_deleted', ['user' => $user]);
                    
                    return redirect()->route('employees.list')->with('success', 'Електронното досие беше изтрито!');
                }
                else{
                    return redirect()->route('employees.list')->with('error', 'Електронното досие не беше намерено!');
                }
            }
            else{
                return redirect()->route('employees.list')->with('error', 'Не можете да премахнете собственото си Електронното досие!');
            }
        }
        else{
            return redirect()->route('employees.list')->with('error', 'Електронното досие не беше намерено!');
        }
    }
}

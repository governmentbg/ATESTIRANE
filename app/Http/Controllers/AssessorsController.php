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

class AssessorsController extends Controller
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
        $users_temp = User::orderBy('id', 'asc')->where('only_evaluate', 1);
        
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
            $users_temp = User::whereIn('organisation_id', $org_tree)->where('only_evaluate', 1);
            
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
        
        return view('assessors.list', compact('users', 'html'));
    }

    public function show_search(){
        return view('assessors.show_search');
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
                return redirect()->route('assessors.edit', ['id' => $user->id]);
            }
            else{
                if( !$user->only_evaluate ){
                    return redirect()->route('assessors.show_search')->with('error', 'Има активен служител с това ЕГН в секция Електронни досиета!');
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
                        return redirect()->route('assessors.edit', ['id' => $user->id]);
                    }
                    else{
                        return redirect()->route('assessors.list')->with('error', 'Има активен служител с това ЕГН който е в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                    }
                }
                else{
                    // show restore screen
                    return redirect()->route('assessors.edit', ['id' => $user->id]);
                }
            }
        }
        else{
            // show add form
            return redirect()->route('assessors.edit')->withInput();
        }
    }

    public function edit(int $id = 0)
    {
        if($id){
            $user = User::where('id', '=', $id)->withTrashed()->first();
            if(empty($user)){
                return redirect()->route('assessors.list')->with('error', 'Оценяващият потребител не беше намерен!');
            }
        }
        else{
            $user = null;
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
                    return redirect()->route('assessors.list')->with('error', 'този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
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
        
        return view('assessors.edit', compact('id', 'html', 'user'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'name' => 'required',
            'email' => 'email|required',
            'organisation_id' => 'required',
        ];
        
        $id = $request->input('id');
        
        if($id){
            $user = User::where('id', '=', $id)->withTrashed()->first();
            
            if(empty($user)){
                return redirect()->route('assessors.list')->with('error', 'Оценяващият потребител не беше намерен!');
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
                    return redirect()->route('assessors.list')->with('error', 'Този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
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
        
        $user->only_evaluate = 1;
        $user->name = $request->input('name');
        $user->egn = $request->input('egn');
        $user->email = $request->input('email');
        $user->organisation_id = $request->input('organisation_id');
        $user->digital_attestation = 0;
        
        if($user->trashed()){
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
            $user->roles()->detach(4);
        }
        
        return redirect()->route('assessors.list')->with('success', 'Оценяващият потребител беше '.($id ? 'обновен!' : 'добавен!'));
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
                            return redirect()->route('assessors.list')->with('error', 'Този Служител e в Структура до която вие нямате достъп. Моля свържете се с Централен Администратор!');
                        }
                    }
                    
                    $user->organisation_id = null;
                    
                    $user->save();
                    
                    $user->delete();
                    
                    log_event('employee_deleted', ['user' => $user]);
                    
                    return redirect()->route('assessors.list')->with('success', 'Оценяващият потребител беше изтрит!');
                }
                else{
                    return redirect()->route('assessors.list')->with('error', 'Оценяващият потребител не беше намерен!');
                }
            }
            else{
                return redirect()->route('assessors.list')->with('error', 'Не можете да премахнете собствения си Оценяващ потребител!');
            }
        }
        else{
            return redirect()->route('assessors.list')->with('error', 'Оценяващия потребител не беше намерен!');
        }
    }
}

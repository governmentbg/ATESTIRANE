<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use App\Models\Role;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;

class LocalAdministratorsController extends Controller
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
        // get local administrators role = 2
        $role = Role::where('id', '=', 2)->first();
        $users_temp = $role->local_admin_role();
        
        // search
        if($request->filled('name')){
            $users_temp->where('name', 'like', '%'.$request->input('name').'%');
        }
        
        if($request->filled('organisation_id')){
            $users_temp->where('organisation_id', '=', $request->input('organisation_id'));
        }
        
        $users = $users_temp->paginate(10);
        
        $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        
        $tree = buildTree($orgs, 'parent_id', 'id');
        
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, $request->input('organisation_id') ?? 0, true, 1, false);
        
        return view('local_administrators.list', compact('users', 'html'));
    }

    public function add(Request $request)
    {
        $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        
        $tree = buildTree($orgs, 'parent_id', 'id');
        
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, $request->input('organisation_id') ?? 0, true, 1, false);
        
        return view('local_administrators.add', compact('html'));
    }
    
    public function store(Request $request)
    {
        $fields = [
            'organisation_id' => 'required',
            'user_id' => 'required'
        ];
        
        $validated_data = $request->validate($fields);
        
        $user_id = $request->input('user_id');
        
        $user = User::where('id', '=', $user_id)->first();
        if(!empty($user)){
            $result = $user->roles()->syncWithoutDetaching(2);
            
            if($result['attached']){
                log_event('employee_set_role_2', ['user' => $user, 'role_id' => 2]);
            }
            
            return redirect()->route('local_administrators.list')->with('success', 'Локалният администратор беше добавен!');
        }
        else{
            return redirect()->route('local_administrators.list')->with('error', 'Потребителят не беше намерен!');
        }
    }
    
    public function delete(int $id){
        $user = User::where('id', '=', $id)->first();
        
        if(!empty($user)){
            $result = $user->roles()->detach(2);
            
            if($result){
                log_event('employee_unset_role_2', ['user' => $user, 'role_id' => 2]);
            }
            
            return redirect()->route('local_administrators.list')->with('success', 'Локалният администратор беше изтрит!');
        }
        else{
            return redirect()->route('local_administrators.list')->with('error', 'Потребителят не беше намерен!');
        }
    }
}

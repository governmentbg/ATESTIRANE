<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationsController extends Controller
{
    public function list()
    {
        $orgs = $this->get_organisations_list();
        $tree = buildTree($orgs, 'parent_id', 'id');
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, 0, false, 1, true);
        
        return view('organisations.list', compact('html'));
    }

    public function add_to(int $id)
    {
        $organisation = Organisation::find($id);
        if( !$organisation ){
            return redirect()->route('organisations.list')->with('error', 'Организационната структура не беше намерена!');
        }

        $orgs = $this->get_organisations_list($id);
        if( $orgs == 'no_access' ){
            return redirect()->route('organisations.list')->with('error', 'Нямате достъп до тази Организационна Структура. Моля свържете се с Централен Администратор!');
        }
        
        $tree = buildTree($orgs, 'parent_id', 'id');
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, $organisation->id ?? 0, true, 1, true);
        
        return view('organisations.add_to', compact('html', 'organisation'));
        
    }

    public function edit(int $id = 0)
    {
        if( $id ){
            $organisation = Organisation::find($id);
        } else {
            $organisation = null;
        }

        $orgs = $this->get_organisations_list($id);
        if( $orgs == 'no_access' ){
            return redirect()->route('organisations.list')->with('error', 'Нямате достъп до тази Организационна Структура. Моля свържете се с Централен Администратор!');
        }

        $tree = buildTree($orgs, 'parent_id', 'id');
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, old('organisation_id') ?? ($id ? $organisation->parent_id:0), true, 1, false);

        return view('organisations.edit', compact('id', 'organisation', 'html'));
    }

    public function update(Request $request)
    {   
        $fields = [
            'name' => 'required'
        ];
        
        $validated_data = $request->validate($fields);
        
        $id = $request->input('id');
        
        if($id){
            $organisation = Organisation::find($id);
            if( !$organisation ){
                return redirect()->route('organisations.list')->with('error', 'Организационната структура не беше намерена!');
            }

            $has_access = $this->check_local_admin_access($id);
            if( !$has_access ){
                return redirect()->route('organisations.list')->with('error', 'Нямате достъп до тази Организационна Структура. Моля свържете се с Централен Администратор!');
            }
        } else {
            $has_access = $this->check_local_admin_access($request->parent_id);
            if( !$has_access ){
                return redirect()->route('organisations.list')->with('error', 'Нямате достъп до тази Организационна Структура. Моля свържете се с Централен Администратор!');
            }
            $organisation = new Organisation;
            $organisation->parent_id = $request->parent_id;
        }
        
        // get old status so properly log activate event
        $old_status = $organisation->status;
        
        $organisation->name = $request->input('name');
        $organisation->status = $request->input('status');
        
        $organisation->save();
        
        if($id){
            log_event('organisation_updated', ['organisation' => $organisation]);
        }
        else{
            log_event('organisation_created', ['organisation' => $organisation]);
        }
        
        if($id){
            // deactivate
            if($organisation->status == 0 && $old_status == 1){
                $all_child_organisations = Organisation::get_all_child_organisations($id);
                
                if(!empty($all_child_organisations)){
                    foreach($all_child_organisations as $child_organisation){
                        $organisation = Organisation::where('id', '=', $child_organisation->id)->first();
                        
                        if(!empty($organisation)){
                            $organisation->status = 0;
                            
                            $organisation->save();
                            
                            log_event('organisation_deactivated', ['organisation' => $organisation]);
                        }
                    }
                }
            }
            else if($organisation->status == 1 && $old_status == 0){
                log_event('organisation_activated', ['organisation' => $organisation]);
            }
        }
        
        return redirect()->route('organisations.list')->with('success', 'Организационната структура беше обновена!');
    }
    
    public function change_status(int $id, $status){
        $organisation = Organisation::where('id', '=', $id)->first();
        
        if(!empty($organisation)){
            $has_access = $this->check_local_admin_access($id);
            if( !$has_access ){
                return redirect()->route('organisations.list')->with('error', 'Нямате достъп до тази Организационна Структура. Моля свържете се с Централен Администратор!');
            }

            if( $status == 'activate' ){
                $organisation->status = 1;
                $organisation->save();
                
                log_event('organisation_activated', ['organisation' => $organisation]);
            } else {
                // deactivate all child organisations include this one
                $all_child_organisations = Organisation::get_all_child_organisations($id);
                
                if(!empty($all_child_organisations)){
                    foreach($all_child_organisations as $child_organisation){
                        $organisation = Organisation::where('id', '=', $child_organisation->id)->first();
                        
                        if(!empty($organisation)){
                            $organisation->status = 0;
                            
                            $organisation->save();
                            
                            log_event('organisation_deactivated', ['organisation' => $organisation]);
                        }
                    }
                }
            }
            
            return redirect()->route('organisations.list')->with('success', 'Организационната структура беше '.($status == 'activate' ? 'активирана':'деактивирана').'!');
        }
        else{
            return redirect()->route('organisations.list')->with('error', 'Организационната структура не беше намерена!');
        }
    }
    
    public function list_users(Request $request){
        $data = array();
        
        $organisation_id = $request->input('organisation_id');
        if(!empty($organisation_id)){
            $users = User::where('organisation_id', '=', $organisation_id)->where('only_evaluate', 0)->orderBy('id', 'asc')->get();
            
            $data['results'] = $users;
        }
        
        return response()->json($data);
    }

    public function check_local_admin_access($id){
        if(session('role_id') == 2){
            $current_user_organisaton = Auth::user()->organisation_id;
            
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
            $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
            
            $org_tree = [];
            foreach($all_child_organisations as $org){
                array_push($org_tree, $org->id);
            }
            
            if(!in_array($id, $org_tree)){
                return false;
            }
        }
        
        return true;
    }

    public function get_organisations_list($with_check_id = ''){
        if(session('role_id') == 2){
            $current_user_organisaton = Auth::user()->organisation_id;
            
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
            $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
            
            $org_tree = [];
            foreach($all_child_organisations as $org){
                array_push($org_tree, $org->id);
            }

            if( $with_check_id ){
                if(!in_array($with_check_id, $org_tree)){
                    return 'no_access';
                }
            }
            
            $orgs = Organisation::whereIn('id', $org_tree)->orderBy('parent_id', 'asc')->get()->toArray();
        }
        else{
            $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        }
        
        return $orgs;
    }
}

<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use Validator;
use App\Models\User;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function types()
    {
        return view('positions.types');
    }

    public function list(string $type, Request $request)
    {
        if($type){
            $position_type = '';
            
            switch($type){
                case 'management':
                    $position_type = 'Ръководни длъжности';
                    break;
                
                case 'experts':
                    $position_type = 'Експертни длъжности';
                    break;
                
                case 'general':
                    $position_type = 'Обща администрация';
                    break;
                
                case 'technical':
                    $position_type = 'Технически длъжности';
                    break;
                
                case 'specific':
                    $position_type = 'Специфични длъжности';
                    break;
            }
            
            if(!empty($position_type)){
                $positions_temp = $positions = Position::orderBy('id', 'asc');
                
                // search
                if($request->filled('name')){
                    $positions_temp->where('name', 'like', '%'.$request->input('name').'%');
                }
                
                $positions_temp->where('type', '=', $type);
                
                $positions = $positions_temp->paginate(10);
                
                return view('positions.list', compact('position_type', 'positions', 'type'));
            }
            else{
                return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
            }
            
        }
        else{
            return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
        }
    }
    
    public function edit(int $id = 0)
    {
        if($id){
            $position = Position::where('id', '=', $id)->first();
            
            if(empty($position)){
                return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
            }
        }
        else{
            $position = null;
        }
        
        return view('positions.edit', compact('id', 'position'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'name' => 'required',
            'type' => 'required',
            'nkpd1' => 'required',
            'nkpd2' => 'required',
            'attestation_form_type' => 'required'
        ];
        
        $validated_data = $request->validate($fields);
        
        $id = $request->input('id');
        
        if($id){
            $position = Position::where('id', '=', $id)->first();
            
            if(empty($position)){
                return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
            }
        }
        else{
            $position = new Position;
        }
        
        $position->name = $request->input('name');
        $position->type = $request->input('type');
        $position->attestation_form_type = $request->input('attestation_form_type');
        $position->nkpd1 = $request->input('nkpd1');
        $position->nkpd2 = $request->input('nkpd2');
        
        $position->save();
        
        if($id){
            log_event('position_updated', ['position' => $position]);
        }
        else{
            log_event('position_created', ['position' => $position]);
        }
        
        $message = 'Длъжността беше '.($id ? 'обновена' : 'добавена').'!';
        
        if(in_array($request->input('type'), ['management', 'experts', 'general', 'technical', 'specific'])){
            return redirect()->route('positions.list', ['type' => $request->input('type')])->with('success', $message);
        }
        else{
            return redirect()->route('positions.types')->with('success', $message);
        }
    }
    
    public function delete(int $id){
        if($id){
            $position = Position::where('id', '=', $id)->first();
            
            if(!empty($position)){
                $position->delete();
                
                if(in_array($position->type, ['management', 'experts', 'general', 'technical', 'specific'])){
                    return redirect()->route('positions.list', ['type' => $position->type])->with('success', 'Длъжността беше изтрита!');
                }
                else{
                    return redirect()->route('positions.types')->with('success', 'Длъжността беше изтрита!');
                }
            }
            else{
                return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
            }
        }
        else{
            return redirect()->route('positions.types')->with('error', 'Длъжността не беше намерена!');
        }
    }
}

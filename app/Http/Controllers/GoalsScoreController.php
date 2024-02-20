<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoalsScoreType;

class GoalsScoreController extends Controller
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
        return view('goals_score.types');
    }

    public function list(string $type, Request $request)
    {
        if($type){
            $position_type = '';
            
            switch($type){
                case 'management':
                    $position_type = 'за ръководни длъжности и служители, на които са възложени ръководни функции';
                    break;
                
                case 'experts':
                    $position_type = 'за служители на експертни длъжности';
                    break;
                
                case 'general':
                    $position_type = 'за съдебни и прокурорски помощници';
                    break;
                
                case 'technical':
                    $position_type = 'за служители, заемащи технически и други специфични длъжности';
                    break;
            }
            
            if(!empty($position_type)){
                $goals_score_temp = $positions = GoalsScoreType::orderBy('id', 'asc');
                
                // search
                //if($request->filled('name')){
                //    $positions_temp->where('name', 'like', '%'.$request->input('name').'%');
                //}
                
                $goals_score_temp->where('attestation_form_type', '=', $type);
                
                $goals_scores = $goals_score_temp->paginate(10);
                
                return view('goals_score.list', compact('position_type', 'goals_scores', 'type'));
            }
            else{
                return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
            }
        }
        else{
            return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
        }
    }
    
    public function edit(int $id = 0)
    {
        if($id){
            $goals_score = GoalsScoreType::where('id', '=', $id)->first();
            
            if(empty($goals_score)){
                return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
            }
        }
        else{
            $goals_score = null;
        }
        
        return view('goals_score.edit', compact('id', 'goals_score'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'attestation_form_type' => 'required',
            'text_score' => 'required',
            'points' => 'required|integer|min:0'
        ];
        
        $validated_data = $request->validate($fields);
        
        $id = $request->input('id');
        
        if($id){
            $goals_score = GoalsScoreType::where('id', '=', $id)->first();
            
            if(empty($goals_score)){
                return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
            }
        }
        else{
            $goals_score = new GoalsScoreType;
        }
        
        $goals_score->attestation_form_type = $request->input('attestation_form_type');
        $goals_score->text_score = $request->input('text_score');
        $goals_score->points = $request->input('points');
        
        $goals_score->save();
        
        if($id){
            //log_event('position_updated', ['position' => $position]);
        }
        else{
            //log_event('position_created', ['position' => $position]);
        }
        
        $message = 'Оценка на задължения/цели беше '.($id ? 'обновена' : 'добавена').'!';
        
        if(in_array($request->input('attestation_form_type'), ['management', 'experts', 'general', 'technical'])){
            return redirect()->route('goals_score.list', ['type' => $request->input('attestation_form_type')])->with('success', $message);
        }
        else{
            return redirect()->route('goals_score.types')->with('success', $message);
        }
    }
    
    public function delete(int $id){
        if($id){
            $goals_score = GoalsScoreType::where('id', '=', $id)->first();
            
            if(!empty($goals_score)){
                $goals_score->delete();
                
                if(in_array($goals_score->attestation_form_type, ['management', 'experts', 'general', 'technical'])){
                    return redirect()->route('goals_score.list', ['type' => $goals_score->attestation_form_type])->with('success', 'Оценка на задължения/цели беше изтрита!');
                }
                else{
                    return redirect()->route('goals_score.types')->with('success', 'Оценка на задължения/цели беше изтрита!');
                }
            }
            else{
                return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
            }
        }
        else{
            return redirect()->route('goals_score.types')->with('error', 'Оценка на задължения/цели не беше намерена!');
        }
    }
}
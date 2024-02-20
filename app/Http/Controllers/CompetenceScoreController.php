<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoalsScoreType;
use App\Models\CompetenceScoreType;

class CompetenceScoreController extends Controller
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
        return view('competence_score.types');
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
                $competence_score_temp = CompetenceScoreType::orderBy('id', 'asc');
                
                // search
                //if($request->filled('name')){
                //    $positions_temp->where('name', 'like', '%'.$request->input('name').'%');
                //}
                
                $competence_score_temp->where('attestation_form_type', '=', $type);
                
                $competence_scores = $competence_score_temp->paginate(10);
                
                return view('competence_score.list', compact('position_type', 'competence_scores', 'type'));
            }
            else{
                return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност не беше намерена!');
            }
        }
        else{
            return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност не беше намерена!');
        }
    }
    
    public function edit(int $id = 0)
    {
        if($id){
            $competence_score = CompetenceScoreType::where('id', '=', $id)->first();
            
            if(empty($competence_score)){
                return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност не беше намерена!');
            }
        }
        else{
            $competence_score = null;
        }
        
        return view('competence_score.edit', compact('id', 'competence_score'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'attestation_form_type' => 'required',
            'competence_type' => 'required',
            'text_score' => 'required',
            'points' => 'required|integer|min:0'
        ];
        
        $validated_data = $request->validate($fields);
        
        $id = $request->input('id');
        
        if($id){
            $competence_score = CompetenceScoreType::where('id', '=', $id)->first();
            
            if(empty($competence_score)){
                return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност не беше намерена!');
            }
        }
        else{
            $competence_score = new CompetenceScoreType;
        }
        
        $competence_score->attestation_form_type = $request->input('attestation_form_type');
        $competence_score->competence_type = $request->input('competence_type');
        $competence_score->text_score = $request->input('text_score');
        $competence_score->points = $request->input('points');
        
        $competence_score->save();
        
        if($id){
            //log_event('position_updated', ['position' => $position]);
        }
        else{
            //log_event('position_created', ['position' => $position]);
        }
        
        $message = 'Оценка на компетентност беше '.($id ? 'обновена' : 'добавена').'!';
        
        if(in_array($competence_score->attestation_form_type, ['management', 'experts', 'general', 'technical'])){
            return redirect()->route('competence_score.list', ['type' => $competence_score->attestation_form_type])->with('success', $message);
        }
        else{
            return redirect()->route('competence_score.types')->with('success', $message);
        }
    }
    
    public function delete(int $id){
        if($id){
            $competence_score = CompetenceScoreType::where('id', '=', $id)->first();
            
            if(!empty($competence_score)){
                $competence_score->delete();
                
                if(in_array($competence_score->attestation_form_type, ['management', 'experts', 'general', 'technical'])){
                    return redirect()->route('competence_score.list', ['type' => $competence_score->attestation_form_type])->with('success', 'Оценка на компетентност беше изтрита!');
                }
                else{
                    return redirect()->route('competence_score.types')->with('success', 'Оценка на компетентност беше изтрита!');
                }
            }
            else{
                return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност намерена!');
            }
        }
        else{
            return redirect()->route('competence_score.types')->with('error', 'Оценка на компетентност не беше намерена!');
        }
    }
}
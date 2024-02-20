<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoalsScoreType;
use App\Models\TotalScoreType;
use App\Models\CompetenceScoreType;

class TotalScoreController extends Controller
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
        return view('total_score.types');
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
                $total_score_temp = TotalScoreType::orderBy('id', 'asc');
                
                // search
                //if($request->filled('name')){
                //    $positions_temp->where('name', 'like', '%'.$request->input('name').'%');
                //}
                
                $total_score_temp->where('attestation_form_type', '=', $type);
                
                $total_scores = $total_score_temp->paginate(10);
                
                return view('total_score.list', compact('position_type', 'total_scores', 'type'));
            }
            else{
                return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
            }
        }
        else{
            return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
        }
    }
    
    public function edit(int $id = 0)
    {
        if($id){
            $total_score = TotalScoreType::where('id', '=', $id)->first();
            
            if(empty($total_score)){
                return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
            }
        }
        else{
            $total_score = null;
        }
        
        return view('total_score.edit', compact('id', 'total_score'));
    }
    
    public function update(Request $request)
    {
        //dd($request->all());
        
        $fields = [
            'attestation_form_type' => 'required',
            'type' => 'required',
            'text_score' => 'required',
            'from_points' => 'required|integer|min:0',
            'to_points' => 'required|integer|min:0'
        ];
        
        $validated_data = $request->validate($fields);
        
        $id = $request->input('id');
        
        if($id){
            $total_score = TotalScoreType::where('id', '=', $id)->first();
            
            if(empty($total_score)){
                return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
            }
        }
        else{
            $total_score = new TotalScoreType;
        }
        
        $total_score->attestation_form_type = $request->input('attestation_form_type');
        $total_score->type = $request->input('type');
        $total_score->text_score = $request->input('text_score');
        $total_score->from_points = $request->input('from_points');
        $total_score->to_points = $request->input('to_points');
        
        $total_score->save();
        
        if($id){
            //log_event('position_updated', ['position' => $position]);
        }
        else{
            //log_event('position_created', ['position' => $position]);
        }
        
        $message = 'Обща оценка беше '.($id ? 'обновена' : 'добавена').'!';
        
        if(in_array($total_score->attestation_form_type, ['management', 'experts', 'general', 'technical'])){
            return redirect()->route('total_score.list', ['type' => $total_score->attestation_form_type])->with('success', $message);
        }
        else{
            return redirect()->route('total_score.types')->with('success', $message);
        }
    }
    
    public function delete(int $id){
        if($id){
            $total_score = TotalScoreType::where('id', '=', $id)->first();
            
            if(!empty($total_score)){
                $total_score->delete();
                
                if(in_array($total_score->attestation_form_type, ['management', 'experts', 'general', 'technical'])){
                    return redirect()->route('total_score.list', ['type' => $total_score->attestation_form_type])->with('success', 'Обща оценка беше изтрита!');
                }
                else{
                    return redirect()->route('total_score.types')->with('success', 'Обща оценка беше изтрита!');
                }
            }
            else{
                return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
            }
        }
        else{
            return redirect()->route('total_score.types')->with('error', 'Обща оценка не беше намерена!');
        }
    }
}
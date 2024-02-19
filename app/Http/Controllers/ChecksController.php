<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttestationForm;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\User;
use Session;
use Auth;

class ChecksController extends Controller
{
    public function dashboard(Request $request){
        if(session('role_id') == 2){
            $current_user_organisaton = Auth::user()->organisation_id;
            
            $grand_parent_organisation = Organisation::get_grand_parent_organisation($current_user_organisaton);
            $all_child_organisations = Organisation::get_all_child_organisations($grand_parent_organisation->id);
            
            $org_tree = [];
            foreach($all_child_organisations as $org){
                array_push($org_tree, $org->id);
            }
            $orgs = Organisation::whereIn('id', $org_tree)->orderBy('parent_id', 'asc')->get()->toArray();
        }
        else{
            $orgs = Organisation::orderBy('parent_id', 'asc')->get()->toArray();
        }
        $tree = buildTree($orgs, 'parent_id', 'id');
        
        $html = array();
        $stepper = 0;
        buildTreeHtml($tree, $html, $stepper, $request->input('organisation_id') ?? 0, true, 1, false);

        $attestations = Attestation::where('status', 'completed')->orderBy('year', 'desc')->get()->unique('year');

        return view('checks.dashboard', compact('html', 'attestations'));
    }

    public function show(Request $request){
        $data = [];        
        switch($request->type){
            case 'no_goals':
                $result = $this->report_no_goals($request);
                $data = $result['data'];
                $view = $result['view'];
                break;
            case 'no_score':
                $result = $this->report_no_score($request);
                $data = $result['data'];
                $view = $result['view'];
                break;
            case 'percent_by_organisations':
                $result = $this->report_percent_by_organisations($request);
                $data = $result['data'];
                $view = $result['view'];
                break;
            case 'scores_by_organisation':
                $result = $this->report_scores_by_organisation($request);
                $data = $result['data'];
                $view = $result['view'];
                break;
            case 'attestation_form':
                $attestation_form = $this->report_attestation_form($request);
                if( $attestation_form ){
                    return redirect()->route('attestationforms.preview', $attestation_form->id);
                } else {
                    return redirect()->route('checks.dashboard')->with('error', 'Липсва атестационнен формуляр по зададените от Вас критерии.');
                }
                break;
            case 'rank_upgrade':
                $result = $this->report_rank_upgrade($request);
                $data = $result['data'];
                $view = $result['view'];
                break;
            default:
                return redirect()->route('checks.dashboard');
        }
        return view($view, $data);
    }

    public function report_no_goals($request){
        $attestation = Session::get('attestation');
        $forms = $attestation->forms()->whereHas('active_goals', function($q){
                    $q->where('goals_status', '!=', 'signed');
                })->get();
        $view = 'checks.no_goals';
        $data = [
            'forms' => $forms
        ];
        $result = [
            'view' => $view,
            'data' => $data
        ];
        return $result;
    }

    public function report_no_score($request){
        $attestation = Session::get('attestation');
        $forms = $attestation->forms()->whereNull('final_score')->where('status', '!=', 'completed')->paginate(10);
        $view = 'checks.no_score';
        $data = [
            'forms' => $forms
        ];
        $result = [
            'view' => $view,
            'data' => $data
        ];
        return $result;
    }

    public function report_percent_by_organisations($request){
        $orgs = $this->get_organisations_list();
        $tree = buildTree($orgs, 'parent_id', 'id');
        $html = '';
        $stepper = 0;
        $counter = 1;
        $this->buildTreeHtml($tree, $html, $stepper, $counter);

        $view = 'checks.percent_by_organisations';
        $data = [
            'html' => $html
        ];
        $result = [
            'view' => $view,
            'data' => $data
        ];
        return $result;
    }

    public function report_scores_by_organisation($request){
        $organisation = Organisation::find($request->organisation_id);
        $years = $request->year;
        $year_data = [];
        foreach( $years as $year ){
            $attestations = Attestation::where('year', $year)->get();
            $attestations_arr = [];
            foreach( $attestations as $attestation ){
                $attestations_arr[] = $attestation->id;
            }
            $forms = $organisation->attestation_forms()
                    ->whereIn('attestation_id', $attestations_arr)
                    ->whereNotNull('final_score')
                    ->where('status', 'completed')
                    ->get();
            foreach( $forms as $form ){
                $year_data[$year][$form->user_id] = [
                    'score' => $form->final_score,
                    'director' => $form->director->name
                ];
            }
            $old_scores_data_1 = User::where('organisation_id', $organisation->id)->where('old_attestation_year_1', $year)->get();
            $old_scores_data_2 = User::where('organisation_id', $organisation->id)->where('old_attestation_year_2', $year)->get();
            foreach( $old_scores_data_1 as $old_score ){
                if( !isset($year_data[$year][$old_score->id]) && $old_score->old_attestation_score_1 ){
                    $year_data[$year][$old_score->id] = [
                        'score' => $old_score->old_attestation_score_1,
                        'director' => '-'
                    ];
                }
            }
            foreach( $old_scores_data_2 as $old_score ){
                if( !isset($year_data[$year][$old_score->id]) && $old_score->old_attestation_score_2 ){
                    $year_data[$year][$old_score->id] = [
                        'score' => $old_score->old_attestation_score_2,
                        'director' => '-'
                    ];
                }
            }
        }

        $view = 'checks.scores_by_organisation';
        $data = [
            'organisation' => $organisation,
            'years' => $years,
            'year_data' => $year_data
        ];
        $result = [
            'view' => $view,
            'data' => $data
        ];
        return $result;
    }

    public function report_attestation_form($request){
        $organisation = Organisation::find($request->organisation_id);
        $year = $request->year[0];
        $attestations = Attestation::where('year', $year)->get();
        $attestations_arr = [];
        foreach( $attestations as $attestation ){
            $attestations_arr[] = $attestation->id;
        }
        $attestation_form = $organisation->attestation_forms()
                ->whereIn('attestation_id', $attestations_arr)
                ->where('user_id', $request->user_id)
                ->first();
        return $attestation_form;
    }

    public function report_rank_upgrade($request){
        $user_data = [];
        $last_attestation = Attestation::orderBy('year', 'desc')->first();
        $years = [];
        if( !$last_attestation ){
            $start_year = date('Y');
        } else {
            $start_year = $last_attestation->year;
        }
        for( $i = $start_year; $i >= $start_year-2; $i-- ){
            array_push($years, $i);
        }

        foreach( $years as $year ){
            $attestations = Attestation::where('year', $year)->get();
            $attestations_arr = [];
            foreach( $attestations as $attestation ){
                $attestations_arr[] = $attestation->id;
            }
            // $users = User::whereHas('attestation_forms', function($q) use ($attestations_arr){
            //         $q->whereIn('attestation_id', $attestations_arr);
            //         $q->whereIn('final_score', ['Оценка 1', 'Оценка 2', 'Оценка 3']);
            //         $q->where('status', 'completed');
            // })
            $forms = AttestationForm::whereIn('attestation_id', $attestations_arr)
                    ->whereIn('final_score', ['Оценка 1', 'Оценка 2', 'Оценка 3'])
                    ->where('status', 'completed')
                    ->get();
            foreach( $forms as $form ){
                if( !isset($user_data[$form->user_id]['data']) ){
                    $user_data[$form->user_id]['data'] = $form->user;
                }
                $user_data[$form->user_id]['scores'][$year] = [
                    'score' => $form->final_score,
                    'director' => $form->director->name
                ];
                // $year_data[$year][$form->user_id] = [
                //     'score' => $form->final_score,
                //     'director' => $form->director->name
                // ];
            }
            $old_scores_data_1 = User::where('old_attestation_year_1', $year)
                                    ->whereIn('old_attestation_score_1', ['Оценка 1', 'Оценка 2', 'Оценка 3'])
                                    ->get();
            $old_scores_data_2 = User::where('old_attestation_year_2', $year)
                                    ->whereIn('old_attestation_score_2', ['Оценка 1', 'Оценка 2', 'Оценка 3'])
                                    ->get();
            foreach( $old_scores_data_1 as $old_score ){
                if( !isset($user_data[$old_score->id]['scores'][$year]) ){
                    $user_data[$old_score->id]['scores'][$year] = [
                        'score' => $old_score->old_attestation_score_1,
                        'director' => '-'
                    ];
                }
            }
            foreach( $old_scores_data_2 as $old_score ){
                if( !isset($user_data[$old_score->id]['scores'][$year]) ){
                    $user_data[$old_score->id]['scores'][$year] = [
                        'score' => $old_score->old_attestation_score_2,
                        'director' => '-'
                    ];
                }
            }
        }
        $first_year = $years[0];
        $second_year = $years[1];
        $third_year = $years[2];
        foreach( $user_data as $user_id => $user ){
            if( isset( $user['scores'][$first_year] ) ){
                switch( $user['scores'][$first_year]['score'] ){
                    case 'Оценка 1':
                            continue 2;
                    case 'Оценка 2':
                        if( isset( $user['scores'][$second_year] ) && in_array($user['scores'][$second_year]['score'], ['Оценка 1', 'Оценка 2']) ){
                            continue 2;
                        } else {
                            unset($user_data[$user_id]);
                        }
                        break;
                    case 'Оценка 3':
                        if( 
                            isset( $user['scores'][$second_year] ) && 
                            in_array($user['scores'][$second_year]['score'], ['Оценка 1', 'Оценка 2', 'Оценка 3']) &&
                            isset( $user['scores'][$third_year] ) && 
                            in_array($user['scores'][$third_year]['score'], ['Оценка 1', 'Оценка 2', 'Оценка 3'])
                        ){
                            continue 2;
                        } else {
                            unset($user_data[$user_id]);
                        }
                        break;
                }
                
            } else {
                unset($user_data[$user_id]);
            }
            
        }

        $view = 'checks.rank_upgrade';
        $data = [
            'years' => $years,
            'user_data' => $user_data
        ];
        $result = [
            'view' => $view,
            'data' => $data
        ];
        return $result;
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

    public function buildTreeHtml($tree, &$html, &$stepper, &$counter){
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::PERCENT);
        $attestation_id = Session::get('attestation_id');
        foreach($tree as $children){
            $organisation = Organisation::find($children['id']);
            $total = $organisation->users->count();
            $completed = $organisation->users()->whereHas('attestation_forms', function($q) use($attestation_id){
                    $q->where('attestation_id', $attestation_id);
                    $q->whereNotNull('final_score');
                    $q->where('status', 'completed');
                })->count();
            $_html = '';
            $_html = '<tr class="'.($children['status'] == 0 ? 'disabled_organisation' : '').'">
                        <td>'.$counter.'</td>
                        <td style="padding-left:'.($stepper*30+10).'px;">'.$children['name'].'</td>
                        <td class="text-center">'.($total ? $formatter->format($completed/$total):'0%').'</td>
                        <td class="text-center">'.$completed.'</td>
                        <td class="text-center">'.$total.'</td>
                    </tr>';
            
            $html .= $_html;
            $counter++;
            if(isset($children['children'])){
                $stepper++;
                $this->buildTreeHtml($children['children'], $html, $stepper, $counter);
                $stepper--;
            }
        }
    }
}

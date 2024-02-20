<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use Session;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\AttestationForm;
use App\Models\AttestationFormGoal;
use App\Models\AttestationFormMeeting;
use App\Models\AttestationFormScore;
use App\Models\AttestationFormScoreSignature;
use App\Models\GoalsScoreType;
use App\Models\CompetenceScoreType;
use App\Models\TotalScoreType;

use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingRequested;



class AttestationformsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function list(){
        if( Session::get('attestation_id') ){
            $attestation = Attestation::find( Session::get('attestation_id') );
        } else {
            $attestation = Attestation::where('status', 'active')->orderBy('created_at')->first();
        }
        
        if( Session::get('role_id') == 5 ){
            $valid_evaluated_members = [];
            $commissions = Auth::user()->commissions()->where('attestation_id', $attestation->id)->get();
            foreach( $commissions as $commission ){
                foreach( $commission->evaluated_members as $evaluated_member){
                    $valid_evaluated_members[] = $evaluated_member->id;
                }
            }
            $attestation_forms = AttestationForm::where('attestation_id', $attestation->id)->whereIn('user_id', $valid_evaluated_members)->get();
        } else if( Session::get('role_id') == 3 ){
            $attestation_forms = AttestationForm::where('attestation_id', $attestation->id)->where('director_id', Auth::user()->id)->get();
        } else {
            $attestation_forms = $attestation->forms;
        }
        
        $data = [
            'attestation_forms' => $attestation_forms
        ];
        return view('attestationforms.list', $data);
    }

    public function start(){
        $attestation_form = AttestationForm::where('user_id', Auth::user()->id)->where('attestation_id', Session::get('attestation_id'))->first(); 
        if( !$attestation_form ){
            // $attestation = Attestation::where('status', 'active')->orderBy('created_at')->first();

            // $user = Auth::user();
            // $grand_parent_organisation = Organisation::get_grand_parent_organisation(Auth::user()->organisation_id);

            // if( $user->appointment_date >= date('Y-01-01') ){
            //     $from_date = date('d.m.Y', strtotime($user->appointment_date));
            // } else {
            //     $from_date = date('d.m.Y', strtotime($attestation->period_from));
            // }

            // $personal_data = [
            //     'name' => $user->name,
            //     'position' => $user->position->name,
            //     'administration' => ($grand_parent_organisation ? $grand_parent_organisation->name:''),
            //     'organisation' => $user->organisation->name,
            //     'from_date' => $from_date,
            //     'to_date' => date('d.m.Y', strtotime($attestation->period_to))
            // ];

            // $attestation_form = new AttestationForm;
            // $attestation_form->attestation_id = $attestation->id;
            // $attestation_form->user_id = Auth::user()->id;
            // $attestation_form->type = Auth::user()->position->type == 'specific' ? 'technical':Auth::user()->position->type;
            // $attestation_form->personal_data = $personal_data;
            // $attestation_form->save();
            return redirect()->route('attestationforms.not_started');
        }
        return redirect()->route('attestationforms.preview', $attestation_form->id);
    }

    public function preview($id){
        $attestation_form = AttestationForm::find($id);
        $labels = generate_attestation_form_labels($attestation_form);

        $goal_score_types = GoalsScoreType::where('attestation_form_type', $attestation_form->type)->orderBy('points', 'desc')->get();
        $competence_score_types = CompetenceScoreType::where('attestation_form_type', $attestation_form->type)->get();
        $competence_score_types_arr = [];
        foreach( $competence_score_types as $cs_type ){
            $competence_score_types_arr[$cs_type->competence_type][] = $cs_type;
        }
        $total_score_types = TotalScoreType::where('attestation_form_type', $attestation_form->type)->orderBy('to_points', 'desc')->get();
        $data = [
            'attestation_form' => $attestation_form,
            'labels' => $labels,
            'goal_score_types' => $goal_score_types,
            'competence_score_types' => $competence_score_types_arr,
            'total_score_types' => $total_score_types
        ];

        $data['attestation_form_scores'] = new AttestationFormScoreSignature;
        // Не са подписани, всички версии на Раздел 4
        if( $attestation_form->scores && !in_array($attestation_form->scores->status, ['signed', 'agreed']) ){
            $attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('user_id', Auth::user()->id)->first();
            $data['attestation_form_scores'] = $attestation_form_scores;
        } else {
            $attestation_form_scores_multiple = AttestationFormScoreSignature::where('attestation_form_id', $id)->get();
            $data['attestation_form_scores_multiple'] = $attestation_form_scores_multiple;
            $data['attestation_form_scores'] = AttestationFormScore::where('attestation_form_id', $id)->first();
        }

        if( $attestation_form->active_goals && $attestation_form->active_goals->signed_goals ){
            $cert_data = extract_certificate_info($attestation_form->active_goals->signed_goals);
            $data['goals_director_certificate'] = $cert_data['certificate_1'];
            $data['goals_employee_certificate'] = $cert_data['certificate_2']; 
        }

        if( $attestation_form->meeting && $attestation_form->meeting->signed_data ){
            $cert_data = extract_certificate_info($attestation_form->meeting->signed_data);
            $data['meeting_director_certificate'] = $cert_data['certificate_1'];
            $data['meeting_employee_certificate'] = $cert_data['certificate_2'];
        }

        $step_4_signatures = [];
        if( $attestation_form->scores && $attestation_form->scores->signatures ){
            foreach( $attestation_form->scores->signatures as $signature ){
                $step_4_signatures[$signature->id]['signature'] = $signature;
                if( $signature->status == 'signed' ){    
                    $cert_data = extract_certificate_info($signature->signed_score);
                    $step_4_signatures[$signature->id]['certificate'] = $cert_data['certificate_1'];
                }
            }
        }
        $data['score_signatures'] = $step_4_signatures;

        $agree_score = [];
        if( $attestation_form->scores && $attestation_form->scores->status == 'agreed' ){
            $cert_data = extract_certificate_info($attestation_form->scores->agree_score);
            $agree_score['certificate'] = $cert_data['certificate_1'];
        }
        $data['agree_score'] = $agree_score;

        $final_score = [];
        if( $attestation_form->status == 'completed' ){
            $cert_data = extract_certificate_info($attestation_form->final_score_signed);
            $final_score['certificate'] = $cert_data['certificate_1'];
        }
        $data['final_score'] = $final_score;
        
        if( Session::get('attestation_id') ){
            $attestation = Attestation::find( Session::get('attestation_id') );
        } else {
            $attestation = Attestation::where('status', 'active')->orderBy('created_at')->first();
        }
        
        $commissions = [];
        $prev_btn = false;
        $next_btn = false;

        if( Session::get('role_id') == 3 ){
            $attestation_forms = AttestationForm::where('attestation_id', $attestation->id)->where('director_id', Auth::user()->id)->get();
        } else if( Session::get('role_id') == 5 ){
            $commissions = Auth::user()->commissions()->where('attestation_id', $attestation->id)->get();
            $valid_evaluated_members = [];
            foreach( $commissions as $commission ){
                foreach( $commission->evaluated_members as $evaluated_member){
                    $valid_evaluated_members[] = $evaluated_member->id;
                }
            }
            $attestation_forms = AttestationForm::where('attestation_id', $attestation->id)->whereIn('user_id', $valid_evaluated_members)->get();
        }
                
        if( in_array(Session::get('role_id'), [3,5]) && $attestation_forms->isNotEmpty()){
            $ids = array();
            foreach ($attestation_forms as $form) {
                array_push($ids, $form->id);
            }
            $current_attestation_form_key = array_search($id, $ids);
            if( $current_attestation_form_key !== false ){
                if( isset($ids[$current_attestation_form_key-1]) ){
                    $prev_btn = route('attestationforms.preview', $ids[$current_attestation_form_key-1]);
                }
                if( isset($ids[$current_attestation_form_key+1]) ){
                    $next_btn = route('attestationforms.preview', $ids[$current_attestation_form_key+1]);
                }
            }
        }
        
        
        $data['prev_btn'] = $prev_btn;
        $data['next_btn'] = $next_btn;
        
        // template: attestation_form_step_(1|2|3|4)_(preview|new|edit|delete|unlock|completed|sign|request)
        // отворил за преглед
        log_event('attestation_form_step_1_preview', ['attestation_form_id' => $id]);
        
        return view('attestationforms.preview', $data);
    }

    // public function step_1($id)
    // {
    //     $attestation_form = AttestationForm::find($id);
    //     $data = [
    //         'id' => $attestation_form->id,
    //         'personal_data' => $attestation_form->personal_data
    //     ];
    //     return view('attestationforms.step_1', $data);
    // }

    public function step_2($id)
    {
        $attestation_form = AttestationForm::find($id);

        $labels = generate_attestation_form_labels($attestation_form);

        if( !$attestation_form->active_goals ){
            $attestation_form_goals = new AttestationFormGoal;
            $attestation_form_goals->attestation_form_id = $attestation_form->id;
            $attestation_form_goals->save();

            $attestation_form->refresh();
        }

        if( $attestation_form->active_goals->goals_status == 'preview' ||  
            ($attestation_form->active_goals->goals_status == 'edit' && $attestation_form->active_goals->goals_status_by == Auth::user()->id)
        ){
            $can_edit = true;
        } else {
            $can_edit = false;
        }

        $data = [
            'attestation_form' => $attestation_form,
            'labels' => $labels,
            'goals_data' => $attestation_form->active_goals->goals,
            'can_edit' => $can_edit
        ];

        if( $attestation_form->active_goals->signed_goals ){
            $cert_data = extract_certificate_info($attestation_form->active_goals->signed_goals);
            $data['director_certificate'] = $cert_data['certificate_1'];
            $data['employee_certificate'] = $cert_data['certificate_2'];

            // $signed_xml = simplexml_load_string( base64_decode($attestation_form->active_goals->signed_goals) );
            // if( isset($signed_xml->Signature[0]) ){
            //     $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[0]->KeyInfo->X509Data->X509Certificate));
            //     $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
            //     $cert_info = openssl_x509_parse($certificate);
            //     $data['director_certificate'] = $cert_info;
            // }
            // if( isset($signed_xml->Signature[1]) ){
            //     $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[1]->KeyInfo->X509Data->X509Certificate));
            //     $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
            //     $cert_info = openssl_x509_parse($certificate);
            //     $data['employee_certificate'] = $cert_info;
            // } 
        }
        
        // отворил за преглед - раздел 2 на формуляра (id)
        log_event('attestation_form_step_2_preview', ['attestation_form_id' => $id, 'step' => 2]);
        
        return view('attestationforms.step_2', $data);
    }

    public function step_2_edit_mode($id, Request $request){
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();
        if( 
            $attestation_form_goals->goals_status == 'preview' || 
            ($attestation_form_goals->goals_status == 'edit' && $attestation_form_goals->goals_status_by == Auth::user()->id)
        ){
            $attestation_form_goals->goals_status = 'edit';
            $attestation_form_goals->goals_status_by = Auth::user()->id;
            $attestation_form_goals->save();

            // в режим на редакция - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_edit', ['attestation_form_id' => $id, 'step' => 2]);
            
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Формата е заключена за редакция от потребител: '.$attestation_form_goals->goals_status_user->name
            ]);
        }
    }

    public function step_2_save($id, Request $request){
        $validation_rules = [
            'goals.*.goal' => 'required',
            'goals.*.result' => 'required',
            'goals.*.date_from' => 'required',
            'goals.*.date_to' => 'required'
        ];
        $validator = Validator::make($request->all(), $validation_rules);
        if( $validator->fails() ){
            return response()->json([
                'status' => 'error',
                'errors' => $validator->messages()->get('*')
            ]);
        }

        $goal_data = $request->all();
        unset( $goal_data['_token'] );
        
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();

        $attestation_form_goals->goals = $goal_data;
        $attestation_form_goals->goals_status = 'preview';
        $attestation_form_goals->goals_status_by = Auth::user()->id;
        $attestation_form_goals->save();

        // записал промени - раздел 2 на формуляра (id)
        log_event('attestation_form_step_2_updated', ['attestation_form_id' => $id, 'step' => 2]);
        
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function step_2_delete($id, Request $request){
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();
        $goals = $attestation_form_goals->goals;
        unset($goals['goals'][$request->goal_number]);
        $reindexed_goals = array_values($goals['goals']);
        $goals['goals'] = $reindexed_goals;
        $attestation_form_goals->goals = $goals;
        $attestation_form_goals->save();
        
        // изтриване на дейност - раздел 2 на формуляра (id)
        log_event('attestation_form_step_2_deleted', ['attestation_form_id' => $id, 'step' => 2]);
        
        return redirect()->route('attestationforms.step_2.view', $id);
    }

    public function step_2_operation($id, Request $request){
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();
        if( $request->operation == 'unlock' ){
            $attestation_form_goals->goals_status = 'preview';
            $attestation_form_goals->goals_status_by = Auth::user()->id;
            $attestation_form_goals->save();
            
            // отключи - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_unlock', ['attestation_form_id' => $id, 'step' => 2]);
        } else if( $request->operation == 'complete' ){
            $attestation_form_goals->goals_status = 'completed';
            $attestation_form_goals->goals_status_by = Auth::user()->id;
            $attestation_form_goals->save();
            
            // приключи - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_completed', ['attestation_form_id' => $id, 'step' => 2]);
        } else if ( $request->operation == 'new' ){
            $old_goals = $attestation_form_goals->goals;
            $attestation_form_goals->delete();

            $new_goals = new AttestationFormGoal;
            $new_goals->attestation_form_id = $id;
            $new_goals->goals = $old_goals;
            $new_goals->save();
            
            // генерирал нов план - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_new', ['attestation_form_id' => $id, 'step' => 2]);
        }
        
        return redirect()->route('attestationforms.step_2.view', $id);
    }

    public function step_2_presign($id, Request $request){
        $attestation_form = AttestationForm::find($id);
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();

        if( $attestation_form_goals->signed_goals ){
            $xml = base64_decode($attestation_form_goals->signed_goals);
        } else {
            $goals = $attestation_form_goals->goals;
            // Взимаме информацията за атестационния формуляр, който се преглежда и я форматираме като xml, след което я подаваме за подпис
            $xw = xmlwriter_open_memory();
            xmlwriter_set_indent($xw, 1);
            $res = xmlwriter_set_indent_string($xw, ' ');

            xmlwriter_start_document($xw, '1.0', 'UTF-8');

                xmlwriter_start_element($xw, 'goals');
                foreach( $goals['goals'] as $goal ){
                    xmlwriter_start_element($xw, 'activity');

                        xmlwriter_start_element($xw, 'goal');
                        xmlwriter_text($xw, $goal['goal']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'result');
                        xmlwriter_text($xw, $goal['result']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'date_from');
                        xmlwriter_text($xw, $goal['date_from']);
                        xmlwriter_end_element($xw);

                        xmlwriter_start_element($xw, 'date_to');
                        xmlwriter_text($xw, $goal['date_to']);
                        xmlwriter_end_element($xw);

                        if( $attestation_form->type == 'management' && Session::get('attestation')->management_form_version == 2 ){
                            xmlwriter_start_element($xw, 'resources');
                            xmlwriter_text($xw, $goal['resources']);
                            xmlwriter_end_element($xw);
                        }

                    xmlwriter_end_element($xw);
                }
                xmlwriter_end_element($xw);

            xmlwriter_end_document($xw);

            $xml = xmlwriter_output_memory($xw);
        }

        $result = [
            'status' => 'success',
            'xml' => $xml
        ];

        return json_encode($result);
    }

    public function step_2_sign($id, Request $request){
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $id)->first();

        $attestation_form_goals->signed_goals = $request->signed_goals;
        if( Session::get('role_id') == 3 ){
            $attestation_form_goals->signed_goals_director_at = date('Y-m-d H:i:s');
            $attestation_form_goals->goals_status = 'half_signed';
            
            // подписан от оценяващия ръководител - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_sign', ['attestation_form_id' => $id, 'step' => 2]);
        }
        if( Session::get('role_id') == 4 ){
            $attestation_form_goals->signed_goals_employee_at = date('Y-m-d H:i:s');
            $attestation_form_goals->goals_status = 'signed';
            
            // подписан от оценявания - раздел 2 на формуляра (id)
            log_event('attestation_form_step_2_sign', ['attestation_form_id' => $id, 'step' => 2]);
        }
        $attestation_form_goals->goals_status_by = Auth::user()->id;
        $attestation_form_goals->save();
    }

    public function step_3($id)
    {
        $attestation_form = AttestationForm::find($id);

        $director_edit = $employee_edit = $edit_mode = false;

        if( Session::get('role_id') == 3 && $attestation_form->meeting && !$attestation_form->meeting->director_comment ){
            $edit_mode = true;
            $director_edit = true;
        }

        if( Session::get('role_id') == 4 && $attestation_form->meeting && !$attestation_form->meeting->employee_comment ){
            $edit_mode = true;
            $employee_edit = true;
        }

        $data = [
            'attestation_form' => $attestation_form,
            'director_edit' => $director_edit,
            'employee_edit' => $employee_edit,
            'edit_mode' => $edit_mode
        ];

        if( $attestation_form->meeting && $attestation_form->meeting->signed_data ){
            $cert_data = extract_certificate_info($attestation_form->meeting->signed_data);
            $data['director_certificate'] = $cert_data['certificate_1'];
            $data['employee_certificate'] = $cert_data['certificate_2'];

            // $signed_xml = simplexml_load_string( base64_decode($attestation_form->meeting->signed_data) );
            // if( isset($signed_xml->Signature[0]) ){
            //     $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[0]->KeyInfo->X509Data->X509Certificate));
            //     $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
            //     $cert_info = openssl_x509_parse($certificate);
            //     $data['director_certificate'] = $cert_info;
            // }
            // if( isset($signed_xml->Signature[1]) ){
            //     $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[1]->KeyInfo->X509Data->X509Certificate));
            //     $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
            //     $cert_info = openssl_x509_parse($certificate);
            //     $data['employee_certificate'] = $cert_info;
            // }
        }

        // отворил за преглед - раздел 3 на формуляра (id)
        log_event('attestation_form_step_3_preview', ['attestation_form_id' => $id, 'step' => 3]);
        return view('attestationforms.step_3', $data);
    }

    public function step_3_request($id)
    {
        $attestation_form = AttestationForm::find($id);

        if( !$attestation_form->meeting ){
            $attestation_form_meeting = new AttestationFormMeeting;
            $attestation_form_meeting->attestation_form_id = $id;
            $attestation_form_meeting->date = date('Y-m-d');
            $attestation_form_meeting->requested_by = Auth::user()->id;
            $attestation_form_meeting->save();
        }

        // send email to other user
        $commission = $attestation_form->user->evaluated_by_commissions->where('attestation_id', $attestation_form->attestation_id)->first();
        if( Session::get('role_id') == 3 ){
            $email = $attestation_form->user->email;
            $data = [
                'sender_name' => $commission->director->name,
                'sender_role' => 'Оценяващ ръководител',
                'receiver_name' => $attestation_form->user->name,
                'receiver_role' => 'Оценяван'
            ];
        }
        if( Session::get('role_id') == 4 ){
            $email = $commission->director->email;
            $data = [
                'sender_name' => $attestation_form->user->name,
                'sender_role' => 'Оценяван',
                'receiver_name' => $commission->director->name,
                'receiver_role' => 'Оценяващ ръководител'
            ];
        }

        try {
            $mailable = new MeetingRequested($data);        
            Mail::to($email)->send($mailable);   
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            
        }

        // заявил междинна среща - раздел 3 на формуляра (id)
        log_event('attestation_form_step_3_request', ['attestation_form_id' => $id, 'step' => 3]);
        
        return redirect()->route('attestationforms.step_3.view', $id);
    }

    public function step_3_save($id, Request $request)
    {
        $attestation_form_meeting = AttestationFormMeeting::where('attestation_form_id', $id)->first();

        if( Session::get('role_id') == 3){
            $fields = [
                'director_comment' => 'required'
            ];
            $validated_data = $request->validate($fields);

            $attestation_form_meeting->director_comment = $request->director_comment;
            
            // коментар на оценяващия ръководител - раздел 3 на формуляра (id)
            log_event('attestation_form_step_3_director_comment', ['attestation_form_id' => $id, 'step' => 3]);
        } else {
            $attestation_form_meeting->employee_comment = $request->employee_comment ?? '-';
            
            // коментар на оценявания - раздел 3 на формуляра (id)
            log_event('attestation_form_step_3_employee_comment', ['attestation_form_id' => $id, 'step' => 3]);
        }

        $attestation_form_meeting->save();

        return redirect()->route('attestationforms.step_3.view', $id);
    }

    public function step_3_presign($id, Request $request){
        $attestation_form_meeting = AttestationFormMeeting::where('attestation_form_id', $id)->first();

        if( $attestation_form_meeting->signed_data ){
            $xml = base64_decode($attestation_form_meeting->signed_data);
        } else {
            // Взимаме информацията за атестационния формуляр, който се преглежда и я форматираме като xml, след което я подаваме за подпис
            $xw = xmlwriter_open_memory();
            xmlwriter_set_indent($xw, 1);
            $res = xmlwriter_set_indent_string($xw, ' ');

            xmlwriter_start_document($xw, '1.0', 'UTF-8');

                xmlwriter_start_element($xw, 'meeting');

                    xmlwriter_start_element($xw, 'requested_by');
                    xmlwriter_text($xw, $attestation_form_meeting->requested_user->name);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'director_comment');
                    xmlwriter_text($xw, $attestation_form_meeting->director_comment);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'employee_comment');
                    xmlwriter_text($xw, $attestation_form_meeting->employee_comment);
                    xmlwriter_end_element($xw);

                    xmlwriter_start_element($xw, 'date');
                    xmlwriter_text($xw, $attestation_form_meeting->date);
                    xmlwriter_end_element($xw);

                xmlwriter_end_element($xw);

            xmlwriter_end_document($xw);

            $xml = xmlwriter_output_memory($xw);
        }

        $result = [
            'status' => 'success',
            'xml' => $xml
        ];

        return json_encode($result);
    }

    public function step_3_sign($id, Request $request){
        $attestation_form_meeting = AttestationFormMeeting::where('attestation_form_id', $id)->first();
        $attestation_form_meeting->signed_data = $request->signed_data;
        if( Session::get('role_id') == 3 ){
            $attestation_form_meeting->signed_director_at = date('Y-m-d H:i:s');
            
            // подписан от оценяващия ръководител - раздел 3 на формуляра (id)
            log_event('attestation_form_step_3_sign', ['attestation_form_id' => $id, 'step' => 3]);
        }
        if( Session::get('role_id') == 4 ){
            $attestation_form_meeting->signed_employee_at = date('Y-m-d H:i:s');
            
            // подписан от оценявания - раздел 3 на формуляра (id)
            log_event('attestation_form_step_3_sign', ['attestation_form_id' => $id, 'step' => 3]);
        }
        $attestation_form_meeting->save();
    }

    public function step_4($id)
    {
        $attestation_form = AttestationForm::find($id);

        if( !$attestation_form->scores ){
            $attestation_form_scores = new AttestationFormScore;
            $attestation_form_scores->attestation_form_id = $attestation_form->id;
            $attestation_form_scores->save();
            $attestation_form->refresh();
        }

        if( $attestation_form->scores->signatures->isEmpty() ){
            $commission = $attestation_form->user->evaluated_by_commissions->where('attestation_id', $attestation_form->attestation_id)->first();
            if( $commission ){
                foreach ($commission->members as $member){
                    $attestation_form_score_signature = new AttestationFormScoreSignature;
                    $attestation_form_score_signature->attestation_form_score_id = $attestation_form->scores->id;
                    $attestation_form_score_signature->user_id = $member->id;
                    $attestation_form_score_signature->attestation_form_id = $attestation_form->id;
                    $attestation_form_score_signature->save();
                }
            }
            $attestation_form->refresh();
        }

        $goal_score_types = GoalsScoreType::where('attestation_form_type', $attestation_form->type)->orderBy('points', 'desc')->get();
        $competence_score_types = CompetenceScoreType::where('attestation_form_type', $attestation_form->type)->get();
        $competence_score_types_arr = [];
        foreach( $competence_score_types as $cs_type ){
            $competence_score_types_arr[$cs_type->competence_type][] = $cs_type;
        }
        $total_score_types = TotalScoreType::where('attestation_form_type', $attestation_form->type)->orderBy('to_points', 'desc')->get();

        $step_4_signatures = [];
        if( $attestation_form->scores && $attestation_form->scores->signatures ){
            foreach( $attestation_form->scores->signatures as $signature ){
                $step_4_signatures[$signature->id]['signature'] = $signature;
                if( $signature->status == 'signed' ){    
                    $signed_xml = simplexml_load_string( base64_decode($signature->signed_score) );
                    if( isset($signed_xml->Signature[0]) ){
                        $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[0]->KeyInfo->X509Data->X509Certificate));
                        $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
                        $cert_info = openssl_x509_parse($certificate);
                        $step_4_signatures[$signature->id]['certificate'] = $cert_info;
                    }
                }
            }
        }

        $attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('user_id', Auth::user()->id)->first();

        $data = [
            'id' => $attestation_form->id,
            'attestation_form' => $attestation_form,
            'goal_score_types' => $goal_score_types,
            'competence_score_types' => $competence_score_types_arr,
            'total_score_types' => $total_score_types,
            'attestation_form_scores' => $attestation_form_scores,
            'scores_data' => $attestation_form_scores->scores,
            'add_info' => $attestation_form_scores->add_info,
            'score_signatures' => $step_4_signatures
        ];
        
        // отворен за преглед - раздел 4 на формуляра (id)
        log_event('attestation_form_step_4_preview', ['attestation_form_id' => $id, 'step' => 3]);
        
        return view('attestationforms.step_4', $data);
    }

    public function step_4_edit_mode($id, Request $request){
        $attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('user_id', Auth::user()->id)->first();
        if( $attestation_form_scores && $attestation_form_scores != 'signed' ){
            $attestation_form_scores->status = 'edit';
            $attestation_form_scores->save();

            // отворен за редакция - раздел 4 на формуляра (id)
            log_event('attestation_form_step_4_edit', ['attestation_form_id' => $id, 'step' => 4]);
            
            return response()->json([
                'status' => 'success'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Формата е вече подписана и не подлежи на редакция.'
            ]);
        }
        // $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();
        // if( 
        //     !$attestation_form_scores->status || 
        //     $attestation_form_scores->status == 'preview' ||  
        //     ($attestation_form_scores->status == 'edit' && $attestation_form_scores->status_by == Auth::user()->id)
        // ){
        //     $attestation_form_scores->status = 'edit';
        //     $attestation_form_scores->status_by = Auth::user()->id;
        //     $attestation_form_scores->status_at = date('Y-m-d H:i:s');
        //     $attestation_form_scores->save();

        //     // отворен за редакция - раздел 4 на формуляра (id)
        //     log_event('attestation_form_step_4_edit', ['attestation_form_id' => $id, 'step' => 3]);
            
        //     return response()->json([
        //         'status' => 'success'
        //     ]);
        // } else {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Формата е заключена за редакция от потребител: '.$attestation_form_scores->status_user->name
        //     ]);
        // }
    }

    public function step_4_save($id, Request $request){
        $validation_rules = [
            'goals_score' => 'required'
        ];
        $validator = Validator::make($request->all(), $validation_rules);
        if( $validator->fails() ){
            return response()->json([
                'status' => 'error',
                'errors' => $validator->messages()->get('*')
            ]);
        }

        $all_data = $request->all();
        unset( $all_data['_token'] );

        $scores_data['goals_score'] = $all_data['goals_score'];
        $scores_data['competence_score'] = $all_data['competence_score'] ?? [];
        $goals_score = GoalsScoreType::find($scores_data['goals_score']);
        $total_score = $goals_score->points + sizeof($scores_data['competence_score']);
        $scores_data['total_score'] = $total_score;
        
        // $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();
        $attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('user_id', Auth::user()->id)->first();

        $attestation_form_scores->scores = $scores_data;
        $attestation_form_scores->total_score = $total_score;
        $attestation_form_scores->add_info = $all_data['add_info'];
        $attestation_form_scores->status = 'preview';
        $attestation_form_scores->save();

        // проверяваме дали е първия попълнил, за да дублираме
        $edited_attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('status', '!=', 'none')->count();
        if( $edited_attestation_form_scores == 1 ){
            AttestationFormScoreSignature::where('attestation_form_id', $id)
                                        ->where('status', 'none')
                                        ->update([
                                            'scores' => $scores_data,
                                            'total_score' => $total_score,
                                            'add_info' => $all_data['add_info']
                                        ]);
        }

        // въвеждане на оценки - раздел 4 на формуляра (id)
        log_event('attestation_form_step_4_updated', ['attestation_form_id' => $id, 'step' => 4]);
        
        return response()->json([
            'status' => 'success'
        ]);
    }

    public function step_4_complete($id, Request $request){
        // $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();
        $attestation_form_scores = AttestationFormScoreSignature::where('attestation_form_id', $id)->where('user_id', Auth::user()->id)->first();
        if( $attestation_form_scores ){
            $attestation_form_scores->status = 'completed';
            // $attestation_form_scores->status_by = Auth::user()->id;
            $attestation_form_scores->save();
            // log_event('attestation_form_step_4_completed', ['attestation_form_id' => $id, 'step' => 4]);
        }

        return redirect()->route('attestationforms.step_4.view', $id);
    }

    public function step_4_presign($id, Request $request){
        $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();

        // Взимаме информацията за атестационния формуляр, който се преглежда и я форматираме като xml, след което я подаваме за подпис
        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        $res = xmlwriter_set_indent_string($xw, ' ');

        xmlwriter_start_document($xw, '1.0', 'UTF-8');

            xmlwriter_start_element($xw, 'score_data');

                xmlwriter_start_element($xw, 'attestation_form_id');
                xmlwriter_text($xw, $attestation_form_scores->attestation_form_id);
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'total_score');
                xmlwriter_text($xw, $attestation_form_scores->total_score);
                xmlwriter_end_element($xw);

            xmlwriter_end_element($xw);

        xmlwriter_end_document($xw);

        $xml = xmlwriter_output_memory($xw);
        
        $result = [
            'status' => 'success',
            'xml' => $xml
        ];

        return json_encode($result);
    }

    public function step_4_sign($id, Request $request){
        $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();
        $signature = $attestation_form_scores->signatures->where('user_id', Auth::user()->id)->first();
        $signature->signed_score = $request->signed_score;
        $signature->signed_at = date('Y-m-d H:i:s');
        $signature->status = 'signed'; 
        // подписан от член на атестационна комисия - раздел 4 на формуляра (id)
        // log_event('attestation_form_step_4_sign', ['attestation_form_id' => $id, 'step' => 3]);
        $signature->save();

        $attestation_form_scores->refresh();
        // ако всички са се подписали, пресмятаме средно аритметичн оцена и сменяме статуса на целия формуляр на signed
        $missing_signature = $attestation_form_scores->signatures->where('status', '!=', 'signed')->first();
        if( !$missing_signature ){
            // средноаритметична оценка
            $total_score = 0;
            $members = 0;
            foreach( $attestation_form_scores->signatures as $signature ){
                $total_score += $signature->total_score;
                $members += 1;
            }
            $avg_total_score = number_format( $total_score/$members, 2, '.', '');

            $attestation_form_scores->total_score = $avg_total_score;
            $attestation_form_scores->status = 'signed';
            $attestation_form_scores->status_at = date('Y-m-d H:i:s');
            $attestation_form_scores->status_by = Auth::user()->id;
            $attestation_form_scores->save();
        }
    }

    public function step_4_agree($id, Request $request){
        $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();

        $attestation_form_scores->status = 'agreed';
        $attestation_form_scores->status_at = date('Y-m-d H:i:s');
        $attestation_form_scores->status_by = Auth::user()->id;
        $attestation_form_scores->agree_score = $request->signed_score;
        $attestation_form_scores->agree_at = date('Y-m-d H:i:s');
        $attestation_form_scores->save();

        $attestation_form = AttestationForm::find($id);
        $attestation_form->status = 'wait_final_score';
        $attestation_form->save();
    }

    public function step_5_presign($id, Request $request){
        $attestation_form_scores = AttestationFormScore::where('attestation_form_id', $id)->first();

        // Взимаме информацията за атестационния формуляр, който се преглежда и я форматираме като xml, след което я подаваме за подпис
        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        $res = xmlwriter_set_indent_string($xw, ' ');

        xmlwriter_start_document($xw, '1.0', 'UTF-8');

            xmlwriter_start_element($xw, 'score_data');

                xmlwriter_start_element($xw, 'attestation_form_id');
                xmlwriter_text($xw, $attestation_form_scores->attestation_form_id);
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'total_score');
                xmlwriter_text($xw, $attestation_form_scores->total_score);
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'final_score');
                xmlwriter_text($xw, $request->final_score);
                xmlwriter_end_element($xw);

                xmlwriter_start_element($xw, 'final_score_comment');
                xmlwriter_text($xw, $request->final_score_comment);
                xmlwriter_end_element($xw);

            xmlwriter_end_element($xw);

        xmlwriter_end_document($xw);

        $xml = xmlwriter_output_memory($xw);
        
        $result = [
            'status' => 'success',
            'xml' => $xml
        ];

        return json_encode($result);
    }

    public function step_5_finalize($id, Request $request){
        $attestation_form = AttestationForm::find($id);

        $attestation_form->status = 'completed';
        $attestation_form->final_score = $request->final_score;
        $attestation_form->final_score_signed = $request->signed_score;
        $attestation_form->final_score_signed_at = date('Y-m-d H:i:s');
        $attestation_form->final_score_signed_by = Auth::user()->id;
        $attestation_form->final_score_comment = $request->final_score_comment;
        $attestation_form->save();
    }
    
}

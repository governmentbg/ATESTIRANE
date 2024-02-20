<?php

use App\Models\LogEvent;
use App\Models\Organisation;

function buildTree(array $flatList){
	$grouped = [];
	
	foreach ($flatList as $node){
		$grouped[$node['parent_id']][] = $node;
	}
	
	$fnBuilder = function($siblings) use (&$fnBuilder, $grouped) {
		foreach ($siblings as $k => $sibling) {
			$id = $sibling['id'];
			
			if(isset($grouped[$id])) {
				$sibling['children'] = $fnBuilder($grouped[$id]);
			}
			
			$siblings[$k] = $sibling;
		}
		
		return $siblings;
	};
	
	return $fnBuilder($grouped[0]);
}

function buildTreeHtml($tree, &$html, &$stepper, $organisation_id = 0, $is_options = false, $parent_status = 1, $with_inactive = false){
	foreach($tree as $children){
		$_html = '';
		if($is_options == true){
			$spacer = '';
			
			for ($i=0; $i < $stepper; $i++) { 
				$spacer .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			
			$_html = '<option value="'.$children['id'].'" '.($organisation_id > 0 && $organisation_id == $children['id'] ? 'selected' : '').'>'.$spacer.$children['name'].'</option>';
			
			if($children['status'] == 0 && $with_inactive == false){
				$_html = '';
			}
		}
		else{
			$btn_1_state = '';
			$btn_2_state = '';
			$btn_3_state = '';
			
			// if this is child element
			if($children['parent_id'] > 0){
				$parent = Organisation::where('id', '=', $children['parent_id'])->first();
				
				if($parent){
					$parent_status = $parent->status;
				}
				else{
					$parent_status = 0;
				}
				
				if($parent_status == 0){
					$btn_1_state = 'disabled';
					$btn_3_state = 'disabled';
				}
				
				if($children['status'] == 0){
					$btn_1_state = 'disabled';
					if($parent_status == 1){
						$btn_2_state = '';
					}
					else{
						$btn_2_state = 'disabled';
					}
					$btn_3_state = 'disabled';
				}
				
			}
			
			$_html = '<tr class="'.($children['status'] == 0 ? 'disabled_organisation' : '').'">
						<td style="padding-left:'.($stepper*30+10).'px;">
							<a href="'.route('organisations.edit', ['id' => $children['id']]).'" class="text-decoration-none">'.$children['name'].'</a>
						</td>
						<td class="text-nowrap">
							<a href="'.route('organisations.add_to', ['id' => $children['id']]).'" class="btn btn-success btn-sm me-2 '.$btn_1_state.'"><i class="bi bi-plus-lg"></i></a>'.
							($children['status'] == 1 ? '<a href="'.route('organisations.change_status', ['id' => $children['id'], 'status' => 'deactivate']).'" class="btn btn-success btn-sm me-2 '.$btn_2_state.'">деактивиране</a>' : '<a href="'.route('organisations.change_status', ['id' => $children['id'], 'status' => 'activate']).'" class="btn btn-secondary btn-sm me-2 '.$btn_2_state.'">активиране</a>').'
						</td>
					</tr>';
		}
		
		if($children['parent_id'] == 0){
			$parent_status = $children['status'];
		}
		
		$html[] = $_html;
		
		if(isset($children['children'])){
			$stepper++;
			buildTreeHtml($children['children'], $html, $stepper, $organisation_id, $is_options, $parent_status, $with_inactive);
			$stepper--;
		}
	}
}

function log_event(string $event_type = null, array $attr = null){
	$log = new LogEvent;
	
	$user_name = '-';
	if(Auth::user()){
		$user = Auth::user();
		$log->user_id = $user->id;
		$user_name = $user->name;
	}
	
	$log->type = $event_type;
	
	if(isset($attr['user'])){
		$log->affected_user_id = $attr['user']->id;
	}
	
	if(isset($attr['attestation_form_id'])){
		$log->affected_attestation_form_id = $attr['attestation_form_id'];
	}
	
	$step = '';
	if(isset($attr['step'])){
		$step = ' раздел '.($attr['step'] ?? '-').' в';
	}
	
	$message = 'Служител '.$user_name.' ('.$user->id.') ';
	
	switch($event_type){
		case 'user_logged_in':
			$message .= 'се вписа в системата като '.$attr['role_name'];
			
			break;
		case 'employee_created':
			$message .= 'добави нов служител '.$attr['user']->name.' ('.$attr['user']->id.')';
			
			break;
		case 'employee_updated':
			$message .= 'обнови информация за служител '.$attr['user']->name.' ('.$attr['user']->id.')';
			
			break;
		case 'employee_deleted':
			$message .= 'премахна информация за служител '.$attr['user']->name.' ('.$attr['user']->id.')';
			
			break;
		case 'employee_set_role_1':
		case 'employee_set_role_2':
		case 'employee_set_role_3':
		case 'employee_set_role_4':
		case 'employee_set_role_5':
			$message .= 'постави роля '.$attr['role_id'].' на служител '.$attr['user']->name.' ('.$attr['user']->id.')';
			
			break;
		case 'employee_unset_role_1':
		case 'employee_unset_role_2':
		case 'employee_unset_role_3':
		case 'employee_unset_role_4':
		case 'employee_unset_role_5':
			$message .= 'премахна роля '.$attr['role_id'].' на служител '.$attr['user']->name.' ('.$attr['user']->id.')';
			
			break;
		case 'commisions_created':
			$message .= 'добави нова атестационна комисия ('.$attr['commission']->id.')';
			
			break;
		case 'commisions_updated':
			$message .= 'обнови информация за атестационна комисия ('.$attr['commission']->id.')';
			
			break;
		case 'organisation_created':
			$message .= 'добави нова организационна структура '.$attr['organisation']->name.'('.$attr['organisation']->id.')';
			
			break;
		case 'organisation_updated':
			$message .= 'обнови информация за организационна структура '.$attr['organisation']->name.'('.$attr['organisation']->id.')';
			
			break;
		case 'organisation_activated':
			$message .= 'активира организационна структура '.$attr['organisation']->name.'('.$attr['organisation']->id.')';
			
			break;
		case 'organisation_deactivated':
			$message .= 'деактивира организационна структура '.$attr['organisation']->name.'('.$attr['organisation']->id.')';
			
			break;
		case 'position_created':
			$message .= 'добави нова длъжност '.$attr['position']->name.'('.$attr['position']->id.')';
			
			break;
		case 'position_updated':
			$message .= 'обнови информация за длъжност '.$attr['position']->name.'('.$attr['position']->id.')';
			
			break;
		case 'position_deleted':
			$message .= 'премахна информация за длъжност '.$attr['position']->name.'('.$attr['position']->id.')';
			
			break;
		case 'attestation_created':
			$message .= 'добави нова атестация ('.$attr['attestation']->id.')';
			
			break;
		case 'attestation_form_step_1_preview':
		case 'attestation_form_step_2_preview':
		case 'attestation_form_step_3_preview':
		case 'attestation_form_step_4_preview':
			$message .= 'отвори за преглед'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_edit':
		case 'attestation_form_step_4_edit':
			$message .= 'отвори за редакция'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_updated':
		case 'attestation_form_step_3_updated':
		case 'attestation_form_step_4_updated':
			$message .= 'записа промени по'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_deleted':
			$message .= 'изтри дейност по'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_unlock':
			$message .= 'отключи'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_completed':
			$message .= 'приключи'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_new':
			$message .= 'генерирал нов план'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_2_sign':
		case 'attestation_form_step_3_sign':
			$message .= 'подписа'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_3_request':
			$message .= 'заяви междинна среща'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_3_director_comment':
			$message .= 'постави коментар като оценяващ ръководител'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
		case 'attestation_form_step_3_employee_comment':
			$message .= 'постави коментар като оценяван'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
		case 'attestation_form_step_4_completed':
			$message .= 'приключи'.$step.' Атестационен формуляр ('.$attr['attestation_form_id'].')';
			
			break;
	}
	
	$log->message = $message;
	$log->ip = \Request::getClientIp();
	$log->browser = \Request::userAgent();
	$log->save();
}

function extract_certificate_info($signed_data){
	$cert_data = [
		'certificate_1' => null,
		'certificate_2' => null
	];
	$signed_xml = simplexml_load_string( base64_decode($signed_data) );
    if( isset($signed_xml->Signature[0]) ){
        $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[0]->KeyInfo->X509Data->X509Certificate));
        $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
        $cert_info = openssl_x509_parse($certificate);
        $cert_data['certificate_1'] = $cert_info;
    }
    if( isset($signed_xml->Signature[1]) ){
        $certificate = trim(preg_replace('/\s+/', '', $signed_xml->Signature[1]->KeyInfo->X509Data->X509Certificate));
        $certificate = "-----BEGIN CERTIFICATE-----\n".$certificate."\n-----END CERTIFICATE-----";
        $cert_info = openssl_x509_parse($certificate);
        $cert_data['certificate_2'] = $cert_info;
    } 
    return $cert_data;
}

function generate_attestation_form_labels($attestation_form){
	$attestation = Session::get('attestation');
	$labels = [];
	switch( $attestation_form->type ){
		case 'management':
			if( $attestation->management_form_version == 1 ){
				$labels['goal'] = 'Цел';
				$labels['result'] = 'Очакван резултат';
				$labels['date_from'] = 'Срок за изпълнение от';
				$labels['date_to'] = 'Срок за изпълнение до';
			} else {
				$labels['goal'] = 'Дейност';
				$labels['result'] = 'Изисквания към качеството';
				$labels['date_from'] = 'Срок за изпълнение от';
				$labels['date_to'] = 'Срок за изпълнение до';
				$labels['resources'] = 'Изисквания към използване на ресурсите (икономичност, ефективност, ефикасност)';
			}
			break;
		case 'experts':
			$labels['goal'] = 'Преки задължения, свързани с';
			$labels['result'] = 'Изисквания към качеството';
			$labels['date_from'] = 'Срок за изпълнение от';
			$labels['date_to'] = 'Срок за изпълнение до';
			break;
		case 'general':
			$labels['goal'] = 'Подготовка на правни актове';
			$labels['goal_2'] = 'Подготовка на документи';
			$labels['result'] = 'Изисквания към качеството';
			$labels['date_from'] = 'Срок за изпълнение от';
			$labels['date_to'] = 'Срок за изпълнение до';
			break;
		case 'technical':
			$labels['goal'] = 'Преки задължения, свързани с';
			$labels['result'] = 'Изисквания към качеството при изпълнение на преките задължения';
			$labels['date_from'] = 'Срок за изпълнение от';
			$labels['date_to'] = 'Срок за изпълнение до';
			break;
	}
	return $labels;
}

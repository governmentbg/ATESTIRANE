@extends('layouts.app')

@section('title') Атестационен формуляр @endsection

@section('content')
    <div class="container-fluid">
        @can('view_attestation_form_toolbar')
        <div class="row mb-3 g-4 no-print">
            <div class="col-md-4">
                @if($prev_btn)
                <a href="{{ $prev_btn }}" class="btn btn-primary"><i class="bi bi-caret-left"></i> Предходен</a>
                @else
                <button class="btn btn-primary disabled"><i class="bi bi-caret-left"></i> Предходен</button>
                @endif
            </div>
            <div class="col-md-4 text-center">
                <a href="{{ route('attestationforms.list') }}" class="btn btn-secondary"><i class="bi bi-list-task"></i> Списък за атестиране</a>
            </div>
            <div class="col-md-4 text-end">
                @if($next_btn)
                <a href="{{ $next_btn }}" class="btn btn-primary">Следващ <i class="bi bi-caret-right"></i></a>
                @else
                <button class="btn btn-primary disabled">Следващ <i class="bi bi-caret-right"></i></button>
                @endif
            </div>
        </div>
        @endcan
        <div class="row mb-3 g-4">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="card-title">Раздел 1 - Лична информация на атестирания</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="mb-3 col-md-6">
                                <label for="names" class="form-label">Имена</label>
                                <input type="text" class="form-control" id="names" value="{{ $attestation_form->personal_data->name }}" disabled>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="position" class="form-label">Длъжност</label>
                                <input type="text" class="form-control" id="position" value="{{ $attestation_form->personal_data->position }}" disabled>
                            </div>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="organisation" class="form-label">Администрация</label>
                            <input type="text" class="form-control" value="{{ $attestation_form->personal_data->administration }}" disabled>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="division" class="form-label">Дирекция/отдел/служба</label>
                            <input type="text" class="form-control" value="{{ $attestation_form->personal_data->organisation }}" disabled>
                        </div>
                        <div class="row g-4">
                            <div class="mb-3 col-md-6">
                                <label for="datefrom" class="form-label">Период на оценяване от дата</label>
                                <input type="text" class="form-control" value="{{ $attestation_form->personal_data->from_date }}" disabled>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="dateto" class="form-label">Период на оценяване до дата</label>
                                <input type="text" class="form-control" value="{{ $attestation_form->personal_data->to_date }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card card-primary mt-3 mb-3">
                    <div class="card-header">
                        <div class="card-title">Раздел 2 - Планиране на дейността</div>
                        @can('edit_attestation_form', $attestation_form->id)
                            @if( !$attestation_form->active_goals || !in_array($attestation_form->active_goals->goals_status, ['signed']) )
                                <div class="card-tools">
                                    <a href="{{ route('attestationforms.step_2.view', $attestation_form->id) }}" class="btn btn-light">Редактирай</a>
                                </div>
                            @endif
                        @endcan
                    </div>
                    @if( $attestation_form->active_goals && $attestation_form->active_goals->goals )
                    <div class="card-body plan-section disabled">
                        @foreach( $attestation_form->active_goals->goals['goals'] as $key => $goal )
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="goal1" class="form-label">{{ $labels['goal'] }} *</label>
                                <textarea class="form-control" name="goals[{{$key}}][goal]" id="goal1" disabled>{{ $goal['goal'] }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="eresult1" class="form-label">{{ $labels['result'] }}*</label>
                                <textarea class="form-control" name="goals[{{$key}}][result]" id="eresult1" disabled>{{ $goal['result'] }}</textarea>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ $labels['date_from'] }}*</label>
                                <div class="input-group mt-0 date datepicker">
                                    <input type="text" class="form-control" name="goals[{{$key}}][date_from]" value="{{ $goal['date_from'] }}" disabled>
                                    <div class="input-group-addon">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label">{{ $labels['date_to'] }}*</label>
                                <div class="input-group mt-0 date datepicker">
                                    <input type="text" class="form-control" name="goals[{{$key}}][date_to]" value="{{ $goal['date_to'] }}" disabled>
                                    <div class="input-group-addon">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            @if( $attestation_form->type == 'management' && session('attestation')->management_form_version == '2' )
                                <div class="col-md-12">
                                <label for="" class="form-label">{{ $labels['resources'] }}*</label>
                                <textarea class="form-control" name="goals[{{$key}}][resources]" disabled>{{ $goal['resources'] }}</textarea>
                            </div>
                            @endif
                        </div>
                        <hr/>
                        @endforeach

                        @if( in_array( $attestation_form->active_goals->goals_status, ['half_signed', 'signed'] ) )
                            <div class="accordion" id="goals_signing">
                              <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $goals_director_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->active_goals->signed_goals_director_at)) }}
                                  </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#goals_signing">
                                  <div class="accordion-body">
                                    Издател: <strong>{{ $goals_director_certificate['issuer']['CN'] }}</strong><br/>
                                    Сериен Номер: <strong>{{ $goals_director_certificate['serialNumber'] }}</strong><br/>
                                    Валиден от: <strong>{{ date('d.m.Y H:i:s', $goals_director_certificate['validFrom_time_t']) }}</strong><br/>
                                    Валиден до: <strong>{{ date('d.m.Y H:i:s', $goals_director_certificate['validTo_time_t']) }}</strong><br/>
                                  </div>
                                </div>
                              </div>
                              @if( $attestation_form->active_goals->goals_status == 'signed' )
                                  <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $goals_employee_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->active_goals->signed_goals_employee_at)) }}
                                      </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#goals_signing">
                                      <div class="accordion-body">
                                        Издател: <strong>{{ $goals_employee_certificate['issuer']['CN'] }}</strong><br/>
                                        Сериен Номер: <strong>{{ $goals_employee_certificate['serialNumber'] }}</strong><br/>
                                        Валиден от: <strong>{{ date('d.m.Y H:i:s', $goals_employee_certificate['validFrom_time_t']) }}</strong><br/>
                                        Валиден до: <strong>{{ date('d.m.Y H:i:s', $goals_employee_certificate['validTo_time_t']) }}</strong><br/>
                                      </div>
                                    </div>
                                  </div>
                              @endif
                            </div>
                        @endif
                    </div>
                    @endif
                </div> 

                <div class="card card-primary mt-3 mb-3">
                    <div class="card-header">
                        <div class="card-title">Раздел 3 - Среща в периода на изпълнение на длъжността/Междинна среща</div>
                        @can('edit_attestation_form', $attestation_form->id)
                            @if( !$attestation_form->meeting || !$attestation_form->meeting->signed_employee_at )
                                <div class="card-tools">
                                    <a href="{{ route('attestationforms.step_3.view', $attestation_form->id) }}" class="btn btn-light">Редактирай</a>
                                </div>
                            @endif
                        @endcan
                    </div>      
                    @if( $attestation_form->meeting && $attestation_form->meeting->requested_by )  
                    <div class="card-body">
                        <div class="mb-3 col-md-12">
                            <label for="initiator" class="form-label">По инициатива на</label>
                            <input type="text" class="form-control" id="initiator" value="{{ $attestation_form->meeting->requested_user->name }}" disabled>
                        </div>

                        <div class="mb-3 col-md-12">
                            <label for="comment" class="form-label">Коментар на прекия ръководител относно изпълнението на длъжността и показаните компетентности</label>
                            <textarea class="form-control" name="director_comment" id="comment" rows="6" disabled>{{ $attestation_form->meeting->director_comment }}</textarea>
                        
                            <label for="comment" class="form-label">Коментар на оценявания</label>
                            <textarea class="form-control" name="employee_comment" id="comment" rows="6" disabled>{{ $attestation_form->meeting->employee_comment }}</textarea>
                        </div>

                        @if( $attestation_form->meeting->signed_data && $attestation_form->meeting->signed_director_at )
                            <div class="accordion mt-3" id="meeting_signing">

                              <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $meeting_director_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->meeting->signed_director_at)) }}
                                  </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#meeting_signing">
                                  <div class="accordion-body">
                                    Издател: <strong>{{ $meeting_director_certificate['issuer']['CN'] }}</strong><br/>
                                    Сериен Номер: <strong>{{ $meeting_director_certificate['serialNumber'] }}</strong><br/>
                                    Валиден от: <strong>{{ date('d.m.Y H:i:s', $meeting_director_certificate['validFrom_time_t']) }}</strong><br/>
                                    Валиден до: <strong>{{ date('d.m.Y H:i:s', $meeting_director_certificate['validTo_time_t']) }}</strong><br/>
                                  </div>
                                </div>
                              </div>
                              @if( $attestation_form->meeting->signed_employee_at )
                                  <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $meeting_employee_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->meeting->signed_employee_at)) }}
                                      </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#meeting_signing">
                                      <div class="accordion-body">
                                        Издател: <strong>{{ $meeting_employee_certificate['issuer']['CN'] }}</strong><br/>
                                        Сериен Номер: <strong>{{ $meeting_employee_certificate['serialNumber'] }}</strong><br/>
                                        Валиден от: <strong>{{ date('d.m.Y H:i:s', $meeting_employee_certificate['validFrom_time_t']) }}</strong><br/>
                                        Валиден до: <strong>{{ date('d.m.Y H:i:s', $meeting_employee_certificate['validTo_time_t']) }}</strong><br/>
                                      </div>
                                    </div>
                                  </div>
                              @endif
                            </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="card card-primary">
                    <div class="card-header">
                        <div class="card-title">Раздел 4 - Оценка на изпълнението на длъжността</div>
                        @can('evaluate_attestation_form', $attestation_form->id)
                            <div class="card-tools">
                                <a href="{{ route('attestationforms.step_4.view', $attestation_form->id) }}" class="btn btn-light">Редактирай</a>
                            </div>
                        @endcan
                    </div>     
                    <div class="card-body">
                        @if( $attestation_form->scores && in_array($attestation_form->scores->status, ['signed', 'agreed']) )
                            @include('attestationforms._preview_step_4_multiple')
                        @else
                            @include('attestationforms._preview_step_4_single')
                        @endif

                        @if( $attestation_form->scores && in_array($attestation_form->scores->status, ['completed', 'signed', 'agreed']) && $score_signatures  )
                            <div class="card card-primary card-outline mt-4">
                                <div class="card-header">
                                    <div class="card-title">Атестационна комисия</div>
                                </div>
                                <div class="card-body">
                                    <div class="accordion mt-3" id="score_signing">
                                        @foreach( $score_signatures as $key => $signature )
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading{{ $key }}">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $key }}" aria-expanded="false" aria-controls="collapse{{ $key }}">
                                                        @if( $signature['signature']->status == 'signed' )
                                                            <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $signature['certificate']['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($signature['signature']->signed_at)) }}
                                                        @else
                                                            <i class="bi bi-person-fill-exclamation fs-3 me-2 text-danger"></i> 
                                                            Очаква подпис от: {{ $signature['signature']->user->name }}
                                                        @endif
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ $key }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $key }}" data-bs-parent="#score_signing">
                                                    <div class="accordion-body">
                                                        @if( $signature['signature']->status == 'signed' )
                                                            Издател: <strong>{{ $signature['certificate']['issuer']['CN'] }}</strong><br/>
                                                            Сериен Номер: <strong>{{ $signature['certificate']['serialNumber'] }}</strong><br/>
                                                            Валиден от: <strong>{{ date('d.m.Y H:i:s', $signature['certificate']['validFrom_time_t']) }}</strong><br/>
                                                            Валиден до: <strong>{{ date('d.m.Y H:i:s', $signature['certificate']['validTo_time_t']) }}</strong><br/>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if( $attestation_form->scores && in_array($attestation_form->scores->status, ['signed', 'agreed']) )
                            <div class="card card-primary card-outline mt-4">
                                <div class="card-header">
                                    <div class="card-title">Запознат съм с поставената оценка</div>
                                </div>
                                <div class="card-body">
                                    <div class="accordion mt-3" id="agree_signing">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading_agree">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_agree" aria-expanded="false" aria-controls="collapse_agree">
                                                    @if( $attestation_form->scores->agree_at )
                                                        <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $agree_score['certificate']['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->scores->agree_at)) }}
                                                    @else
                                                        <i class="bi bi-person-fill-exclamation fs-3 me-2 text-danger"></i> 
                                                        Очаква подпис от: {{ $attestation_form->user->name }}
                                                    @endif
                                                </button>
                                            </h2>
                                            <div id="collapse_agree" class="accordion-collapse collapse" aria-labelledby="heading_agree" data-bs-parent="#agree_signing">
                                                <div class="accordion-body">
                                                    @if( $attestation_form->scores->agree_score )
                                                        Издател: <strong>{{ $agree_score['certificate']['issuer']['CN'] }}</strong><br/>
                                                        Сериен Номер: <strong>{{ $agree_score['certificate']['serialNumber'] }}</strong><br/>
                                                        Валиден от: <strong>{{ date('d.m.Y H:i:s', $agree_score['certificate']['validFrom_time_t']) }}</strong><br/>
                                                        Валиден до: <strong>{{ date('d.m.Y H:i:s', $agree_score['certificate']['validTo_time_t']) }}</strong><br/>
                                                    @else
                                                        @if( $attestation_form->user_id == Auth::user()->id )
                                                            <button type="button" class="btn btn-primary sign_btn">Подпиши с КЕП</button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div> 
            </div>

            @can('finalize_attestation_form', $attestation_form->id)
                <div class="card card-primary mt-3">
                    <div class="card-header">
                        <div class="card-title">Раздел 5 -  Крайна оценка на изпълнение на длъжността</div>
                    </div>     
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Крайна оценка</label>
                            <select name="final_score" id="final_score" class="form-control">
                            @foreach( $total_score_types as $score_type )
                                @php
                                    if( 
                                        $attestation_form->scores &&
                                        floor($attestation_form->scores->total_score) >= $score_type->from_points && 
                                        floor($attestation_form->scores->total_score) <= $score_type->to_points 
                                    ){
                                        $is_score = true;
                                    } else {
                                        $is_score = false;
                                    }
                                @endphp
                                @if( floor($attestation_form->scores->total_score) <= $score_type->to_points )
                                <option @selected($is_score) value="{{ $score_type->type }}">{{ $score_type->text_score }}</option>
                                @endif
                            @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label class="form-label">Коментар към крайната оценка</label>
                            <textarea class="form-control" id="final_score_comment" name="final_score_comment"></textarea>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary finalize_btn">Подпиши с КЕП</button>
                        </div>
                    </div>
                </div>
            @endcan

            @if( $attestation_form->status == 'completed' )
                <div class="card card-primary mt-3">
                    <div class="card-header">
                        <div class="card-title">Раздел 5 -  Крайна оценка на изпълнение на длъжността</div>
                    </div>     
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Крайна оценка</label>
                            <input class="form-control" type="text" value="{{ $attestation_form->final_score }}" disabled />
                        </div>
                        <div class="form-group mt-3">
                            <label class="form-label">Коментар към крайната оценка</label>
                            <textarea class="form-control" disabled>{{ $attestation_form->final_score_comment }}</textarea>
                        </div>
                        <div class="accordion mt-3" id="final_signing">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading_final">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_final" aria-expanded="false" aria-controls="collapse_final">
                                        <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $final_score['certificate']['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->final_score_signed_at)) }}
                                    </button>
                                </h2>
                                <div id="collapse_final" class="accordion-collapse collapse" aria-labelledby="heading_final" data-bs-parent="#final_signing">
                                    <div class="accordion-body">
                                        @if( $final_score )
                                            Издател: <strong>{{ $final_score['certificate']['issuer']['CN'] }}</strong><br/>
                                            Сериен Номер: <strong>{{ $final_score['certificate']['serialNumber'] }}</strong><br/>
                                            Валиден от: <strong>{{ date('d.m.Y H:i:s', $final_score['certificate']['validFrom_time_t']) }}</strong><br/>
                                            Валиден до: <strong>{{ date('d.m.Y H:i:s', $final_score['certificate']['validTo_time_t']) }}</strong><br/>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="text-center mt-3">
                <button class="btn btn-success print_btn no-print" onclick="window.print()">Принтирай</button>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script src="{{ asset('assets/scs/polyfill.js') }}"></script>
<script src="{{ asset('assets/scs/scs.js') }}"></script>
<script src="{{ asset('assets/scs/scs.helpers.js') }}"></script>
<script>
    $('.sign_btn').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_4.presign", $attestation_form->id) }}',
            dataType : "json",
            data: { 
              _token: "{{ csrf_token() }}"
            }
        }).done(function(result){
            if( result.status == 'success' ){
                SCS.signXML(result.xml)
                .then(function (json) {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("attestationforms.step_4.agree", $attestation_form->id) }}',
                        dataType : "json",
                        data: { 
                          _token: "{{ csrf_token() }}",
                          signed_score: json.signature
                        }
                    }).always(function(){
                        location.reload();
                    });
                })
                .then(null, function (err) {
                    alert('ERROR:' + "\r\n" + err.message);
                });
            }
        });
    });

    $('.finalize_btn').on('click', function (e) {
        e.preventDefault();
        $('#final_score').attr('disabled', true);
        $('#final_score_comment').attr('disabled', true);
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_5.presign", $attestation_form->id) }}',
            dataType : "json",
            data: { 
              _token: "{{ csrf_token() }}",
              final_score: $('#final_score').val(),
              final_score_comment: $('#final_score_comment').val()
            }
        }).done(function(result){
            if( result.status == 'success' ){
                SCS.signXML(result.xml)
                .then(function (json) {
                    $.ajax({
                        type: "POST",
                        url: '{{ route("attestationforms.step_5.finalize", $attestation_form->id) }}',
                        dataType : "json",
                        data: { 
                            _token: "{{ csrf_token() }}",
                            signed_score: json.signature,
                            final_score: $('#final_score').val(),
                            final_score_comment: $('#final_score_comment').val()
                        }
                    }).always(function(){
                        location.reload();
                    });
                })
                .then(null, function (err) {
                    alert('ERROR:' + "\r\n" + err.message);
                });
            }
        });
    });
</script>
@endsection

@extends('layouts.app')

@section('title') Атестационен формуляр - Раздел 4 <a href="{{ route('attestationforms.preview', ['id' => $attestation_form->id]) }}" class="btn btn-secondary ms-3">Назад</a> @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Оценка
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <form id="scores_form">
            @csrf
            <div class="row mb-3 g-4">
                <div class="col-md-12">
                    <div class="alert alert-danger" id="scores_save_errors" style="display: none;">
                        <ul id="scores_errors_list" class="mb-0 pl-2"></ul>
                    </div>

                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Оценка на поставените цели</div>
                        </div>
                        <div class="card-body score-section disabled">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach( $goal_score_types as $score_type )
                                    <tr>
                                        <td>
                                            <label class="form-label" for="gs_{{ $score_type->id }}">{{ $score_type->text_score }}</label>
                                        </td>
                                        <td>{{ $score_type->points }} точки</td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="goals_score" id="gs_{{ $score_type->id }}" value="{{ $score_type->id }}" @checked($scores_data && $score_type->id == $scores_data->goals_score)>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 g-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Показани компетентности</div>
                        </div>
                        <div class="card-body score-section disabled">
                            <div class="col-md-12">
                                @foreach( $competence_score_types as $group_name => $score_types )
                                    <h5>{{ $group_name }}</h5>
                                    <p class="mb-3">(максимални {{ sizeof( $score_types ) }} точки)</p>
                                    @foreach( $score_types as $score_type )
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" name="competence_score[]" value="{{ $score_type->id }}" id="cs_{{ $score_type->id }}" @checked($scores_data && in_array($score_type->id, $scores_data->competence_score))>
                                        <label class="form-check-label" for="cs_{{ $score_type->id }}">{{ $score_type->text_score }}</label>
                                    </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 g-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Обща оценка на изпълнението на длъжността</div>
                        </div>
                        <div class="card-body disabled">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach( $total_score_types as $score_type )
                                    <tr class="align-middle">
                                        <td>
                                            <label class="form-label" for="fa_50">{{ $score_type->text_score }}</label>
                                        </td>
                                        <td>
                                            @if( $score_type->from_points )
                                                от {{ $score_type->from_points }} 
                                            @endif
                                            до {{ $score_type->to_points }} точки
                                        </td>
                                        <td class="text-center">
                                            @if( $scores_data && $scores_data->total_score >= $score_type->from_points && $scores_data->total_score <= $score_type->to_points )
                                                <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 g-4">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Допълнителна информация</div>
                        </div>
                        <div class="card-body score-section disabled">
                            <div class="mb-3 col-md-12">
                                <label for="arguments" class="form-label">Аргументи за поставената оценка*</label>
                                <textarea class="form-control" name="add_info[arguments]" id="arguments" rows="6" required>{{ $add_info ? $add_info->arguments:'' }}</textarea>
                            </div>
                            <div class="mb-3 col-md-12">
                                <label for="sources" class="form-label">Данни и източници на информация за оценката*</label>
                                <textarea class="form-control" name="add_info[sources]" id="sources" rows="6" required>{{ $add_info ? $add_info->sources:'' }}</textarea>
                            </div>
                            <div class="mb-3 col-md-12">
                                <label for="needs" class="form-label">Идентифицирани потребности от обучение*</label>
                                <textarea class="form-control" name="add_info[needs]" id="needs" rows="6" required>{{ $add_info ? $add_info->needs:'' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="actions_container mt-4 text-center">
            @if( !$attestation_form_scores->status || !in_array($attestation_form_scores->status, ['completed', 'signed']) )
                <button type="button" class="btn btn-warning edit_btn">Редактирай</button>
                <button type="button" class="btn btn-success save_btn" style="display: none;">Запази</button>
            @endif

            @if( $scores_data && $attestation_form_scores->total_score && $attestation_form_scores->status == 'preview' )
                <button type="button" class="btn btn-success complete_btn">Приключи</button>
            @endif
        </div>
        @if( $scores_data && $attestation_form_scores->total_score && in_array($attestation_form_scores->status, ['completed', 'signed']) )
            <div class="card card-primary card-outline">
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
                                        @else
                                            @if( $signature['signature']->user_id == Auth::user()->id )
                                                <button type="button" class="btn btn-primary sign_btn">Подпиши с КЕП</button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <form action="{{ route('attestationforms.step_4.complete', $attestation_form->id) }}" method="POST" id="complete_form">
            @csrf
        </form>
    </div>
@endsection

@section ('scripts')
<script src="{{ asset('assets/scs/polyfill.js') }}"></script>
<script src="{{ asset('assets/scs/scs.js') }}"></script>
<script src="{{ asset('assets/scs/scs.helpers.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.js-basic-multiple').select2({
            theme: 'bootstrap-5'
        });
        $('.datepicker').datepicker({
            language: 'bg'
        });

        fields_status();
    });

    $('.edit_btn').on('click', function(){
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_4.edit_mode", $attestation_form->id) }}',
            dataType : "json",
            data: { 
              _token: "{{ csrf_token() }}"
            }
        }).done(function(result){
            if( result.status == 'success' ){
                $('.score-section').removeClass('disabled');
                fields_status();
                $('.edit_btn').hide();
                $('.save_btn').show();
            } else {
                $('#scores_errors_list').html('');
                $('#scores_errors_list').append('<li>'+result.message+'</li>');
                $('#scores_save_errors').show();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        });
    });

    $('.save_btn').on('click', function(){
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_4.save", $attestation_form->id) }}',
            dataType : "json",
            data: $('#scores_form').serialize()
        }).done(function(result){
            if( result.status == 'success' ){
                $('.score-section').addClass('disabled');
                fields_status();
                $('.edit_btn').show();
                $('.save_btn').hide();
                location.reload();
            } else {
                $('#scores_errors_list').html('');
                $.each( result.errors, function( key, value ) {
                    $.each( value, function( sub_key, sub_value ) {
                        $('#scores_errors_list').append('<li>'+sub_value+'</li>');
                    });
                });
                $('#scores_save_errors').show();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        });
    });

    $('.complete_btn').on('click', function(){
        if( confirm('Сигурни ли сте, че искате да приключите този формуляр? След маркирането като приключен, няма да имате възможност за промяна.') ){
            $('#complete_form').submit();
        }
    });

    function fields_status(){
        $('.score-section').each(function(){
            if( $(this).hasClass('disabled') ){
                $(this).find('textarea').attr('disabled', true);
                $(this).find('input').attr('disabled', true);
            } else {
                $(this).find('textarea').attr('disabled', false);
                $(this).find('input').attr('disabled', false);
            }
        });
    }

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
                        url: '{{ route("attestationforms.step_4.sign", $attestation_form->id) }}',
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
</script>
@endsection

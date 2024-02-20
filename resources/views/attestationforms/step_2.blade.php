@extends('layouts.app')

@section('title') Атестационен формуляр - Раздел 2 <a href="{{ route('attestationforms.preview', ['id' => $attestation_form->id]) }}" class="btn btn-secondary ms-3">Назад</a> @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Планиране
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <form id="goals_form">
            @csrf
            <div class="row mb-3 g-4">
                <div class="col-md-12">
                    @if( (($goals_data && sizeof($goals_data['goals']) < 3) || !$goals_data) && !in_array($attestation_form->active_goals->goals_status, ['completed', 'half_signed', 'signed']) )
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-success" id="add_goal_btn">Нова дейност</button>
                        </div>
                    @endif
                    <div class="alert alert-danger" id="goals_save_errors" style="display: none;">
                    </div>
                    @php $next_goal_id = 0; @endphp
                    @if( $goals_data )
                        @foreach( $goals_data['goals'] as $key => $goal )
                        <div class="card card-primary card-outline mb-3">
                            <div class="card-header">
                                <div class="card-title">Дейност {{ $key+1 }}</div>
                                <div class="card-tools">
                                    @if( $can_edit )
                                        <button type="button" class="btn btn-sm btn-danger delete_btn" data-number="{{ $key }}"><i class="nav-icon bi bi-trash3"></i></button>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body plan-section disabled">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label for="goal1" class="form-label">{{ $labels['goal'] }}*</label>
                                        <textarea class="form-control" name="goals[{{$key}}][goal]" id="goal1" required>{{ $goal['goal'] }}</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="eresult1" class="form-label">{{ $labels['result'] }}*</label>
                                        <textarea class="form-control" name="goals[{{$key}}][result]" id="eresult1" required>{{ $goal['result'] }}</textarea>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">{{ $labels['date_from'] }}*</label>
                                        <div class="input-group mt-0 date datepicker">
                                            <input type="text" class="form-control" name="goals[{{$key}}][date_from]" value="{{ $goal['date_from'] }}" required>
                                            <div class="input-group-addon">
                                                <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">{{ $labels['date_to'] }}*</label>
                                        <div class="input-group mt-0 date datepicker">
                                            <input type="text" class="form-control" name="goals[{{$key}}][date_to]" value="{{ $goal['date_to'] }}" required>
                                            <div class="input-group-addon">
                                                <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    @if( $attestation_form->type == 'management' && session('attestation')->management_form_version == '2' )
                                        <div class="col-md-12">
                                            <label for="" class="form-label">{{ $labels['resources'] }}*</label>
                                            <textarea class="form-control" name="goals[{{$key}}][resources]" required>{{ $goal['resources'] }}</textarea>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @php $next_goal_id++; @endphp
                        @endforeach
                    @endif
                    <div class="goals-container"></div>
                </div>
            </div>

            @if( in_array( $attestation_form->active_goals->goals_status, ['half_signed', 'signed'] ) )
                <div class="accordion" id="goals_signing">

                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $director_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->active_goals->signed_goals_director_at)) }}
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#goals_signing">
                      <div class="accordion-body">
                        Издател: <strong>{{ $director_certificate['issuer']['CN'] }}</strong><br/>
                        Сериен Номер: <strong>{{ $director_certificate['serialNumber'] }}</strong><br/>
                        Валиден от: <strong>{{ date('d.m.Y H:i:s', $director_certificate['validFrom_time_t']) }}</strong><br/>
                        Валиден до: <strong>{{ date('d.m.Y H:i:s', $director_certificate['validTo_time_t']) }}</strong><br/>
                      </div>
                    </div>
                  </div>
                  @if( $attestation_form->active_goals->goals_status == 'signed' )
                      <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $employee_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->active_goals->signed_goals_employee_at)) }}
                          </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#goals_signing">
                          <div class="accordion-body">
                            Издател: <strong>{{ $employee_certificate['issuer']['CN'] }}</strong><br/>
                            Сериен Номер: <strong>{{ $employee_certificate['serialNumber'] }}</strong><br/>
                            Валиден от: <strong>{{ date('d.m.Y H:i:s', $employee_certificate['validFrom_time_t']) }}</strong><br/>
                            Валиден до: <strong>{{ date('d.m.Y H:i:s', $employee_certificate['validTo_time_t']) }}</strong><br/>
                          </div>
                        </div>
                      </div>
                  @endif
                </div>
            @endif

            <div class="actions_container mt-4 text-center">
                @if( $can_edit )
                    <button type="button" class="btn btn-warning edit_btn">Редактирай</button>
                    <button type="button" class="btn btn-success save_btn" style="display: none;">Запази</button>
                @else
                    @if( !in_array($attestation_form->active_goals->goals_status, ['completed', 'half_signed', 'signed'] ) )
                        <div class="alert alert-danger">Формата е заключена за редакция от потребител: {{ $attestation_form->active_goals->goals_status_user->name }}</div>
                    @endif
                @endif

                @if( session('role_id') == 3 && $attestation_form->active_goals->goals_status == 'edit' && $attestation_form->active_goals->goals_status_by != Auth::user()->id )
                    <button type="button" class="btn btn-warning unlock_btn">Отключи</button>
                @endif

                @if( session('role_id') == 3 && !in_array($attestation_form->active_goals->goals_status, ['completed', 'half_signed', 'signed']) )
                    <button type="button" class="btn btn-success complete_btn">Приключи</button>
                @endif


                @if( 
                    (session('role_id') == 4 && $attestation_form->active_goals->signed_goals_director_at && $attestation_form->active_goals->goals_status == 'half_signed') || 
                    (session('role_id') == 3 && !$attestation_form->active_goals->signed_goals_director_at && $attestation_form->active_goals->goals_status == 'completed') )
                    <button type="button" class="btn btn-primary sign_btn">Подпиши с КЕП</button>
                @endif
            </div>
            
        </form>
        <div class="mt-3">
            @if( $attestation_form->active_goals->goals_status == 'signed' )
                @if( session('role_id') == 3 )
                    <button type="submit" class="btn btn-danger new_btn">Нов план</button>
                @endif
                <a href="{{ route('attestationforms.step_3.view', $attestation_form->id) }}" class="btn btn-primary float-end">Към Раздел 3</a>
            @endif
        </div>
        <form action="{{ route('attestationforms.step_2.delete', $attestation_form->id) }}" method="POST" id="delete_form">
            @csrf
            <input type="hidden" name="goal_number" id="delete_goal_number" />
        </form>
        <form action="{{ route('attestationforms.step_2.operation', $attestation_form->id) }}" method="POST" id="operation_form">
            @csrf
            <input type="hidden" name="operation" id="operation_type" value="" />
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
        show_actions();
    });

    $('.edit_btn').on('click', function(){
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_2.edit_mode", $attestation_form->id) }}',
            dataType : "json",
            data: { 
              _token: "{{ csrf_token() }}"
            }
        }).done(function(result){
            if( result.status == 'success' ){
                $('.plan-section').removeClass('disabled');
                $('.delete_btn').hide();
                fields_status();
                $('.edit_btn').hide();
                $('.complete_btn').hide();
                $('.save_btn').show();
            } else {
                $('#goals_errors_list').html('');
                $('#goals_errors_list').append('<li>'+result.message+'</li>');
                $('#goals_save_errors').show();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        });
        
    });

    $('.save_btn').on('click', function(){
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_2.save", $attestation_form->id) }}',
            dataType : "json",
            data: $('#goals_form').serialize()
        }).done(function(result){
            if( result.status == 'success' ){
                $('.plan-section').addClass('disabled');
                $('.delete_btn').show();
                fields_status();
                $('.edit_btn').show();
                $('.complete_btn').show();
                $('.save_btn').hide();
            } else {
                $('#goals_save_errors').html('Моля попълнете всички полета, за да запазите промените си.');
                /*$.each( result.errors, function( key, value ) {
                    $.each( value, function( sub_key, sub_value ) {
                        $('#goals_errors_list').append('<li>'+sub_value+'</li>');
                    });
                });*/
                $('#goals_save_errors').show();
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }
        });
    });

    $(document).on('click', '.delete_btn', function(){
        if( confirm('Сигурни ли сте, че искате да изтриете тази дейност?') ){
            var goal_number = $(this).data('number');
            $('#delete_goal_number').val(goal_number);
            $('#delete_form').submit();
        }
    });

    $('.unlock_btn').on('click', function(){
        if( confirm('Сигурни ли сте, че искате да отключите този формуляр?') ){
            $('#operation_type').val('unlock');
            $('#operation_form').submit();
        }
    });

    $('.complete_btn').on('click', function(){
        if( confirm('Сигурни ли сте, че искате да приключите този формуляр? След маркирането като приключен, няма да имате възможност за промяна.') ){
            $('#operation_type').val('complete');
            $('#operation_form').submit();
        }
    });

    $('.new_btn').on('click', function(){
        if( confirm('Сигурни ли сте, че искате да създадете нов план за този формуляр? Тази операция е необратима.') ){
            $('#operation_type').val('new');
            $('#operation_form').submit();
        }
    });

    function fields_status(){
        $('.plan-section').each(function(){
            if( $(this).hasClass('disabled') ){
                $(this).find('textarea').attr('disabled', true);
                $(this).find('input').attr('disabled', true);
            } else {
                $(this).find('textarea').attr('disabled', false);
                $(this).find('input').attr('disabled', false);
            }
        });
    }

    function show_actions(){
        if( $('.plan-section').length > 0 ){
            $('.actions_container').show();
        } else {
            $('.actions_container').hide();
        }
    }

    var goal_count = {{ $next_goal_id }};

    $('#add_goal_btn').on('click', generate_new_goal);

    function generate_new_goal(){
        if( goal_count <= 3 ){
            if( $('.save_btn').is(":hidden") ){
                var is_disabled = true;
            } else {
                var is_disabled = false;
            }
            var html = '<div class="card card-primary card-outline mb-3">'+
                        '<div class="card-header"><div class="card-title">Дейност '+(goal_count+1)+'</div><div class="card-tools"><button type="button" class="btn btn-sm btn-danger delete_btn" data-number="'+goal_count+'"><i class="nav-icon bi bi-trash3"></i></button></div></div>'+
                        '<div class="card-body plan-section '+(is_disabled ? 'disabled':'')+'">'+
                            '<div class="row g-4">'+
                                '<div class="col-md-12">'+
                                    '<label for="goal2" class="form-label">{{ $labels["goal"] }}*</label>'+
                                    '<textarea class="form-control" id="goal'+goal_count+'" name="goals['+goal_count+'][goal]" required></textarea>'+
                                '</div>'+
                                '<div class="col-md-12">'+
                                    '<label for="eresult2" class="form-label">{{ $labels["result"] }}*</label>'+
                                    '<textarea class="form-control" id="eresult'+goal_count+'" name="goals['+goal_count+'][result]" required></textarea>'+
                                '</div>'+
                                '<div class="mb-3 col-md-6">'+
                                    '<label class="form-label">{{ $labels["date_from"] }}*</label>'+
                                    '<div class="input-group mt-0 date datepicker">'+
                                        '<input type="text" class="form-control" name="goals['+goal_count+'][date_from]" required>'+
                                        '<div class="input-group-addon">'+
                                            '<span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                '<div class="mb-3 col-md-6">'+
                                    '<label class="form-label">{{ $labels["date_to"] }}*</label>'+
                                    '<div class="input-group mt-0 date datepicker">'+
                                        '<input type="text" class="form-control" name="goals['+goal_count+'][date_to]" required>'+
                                        '<div class="input-group-addon">'+
                                            '<span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'+
                                @if( $attestation_form->type == 'management' && session('attestation')->management_form_version == '2' )
                                    '<div class="col-md-12">'+
                                        '<label for="" class="form-label">{{ $labels["resources"] }}*</label>'+
                                        '<textarea class="form-control" name="goals['+goal_count+'][resources]" required></textarea>'+
                                    '</div>'+
                                @endif
                            '</div>'+
                        '</div>'+
                    '</div>';
            $('.goals-container').append(html);
            fields_status();
            show_actions();
            $('.datepicker').datepicker({
                language: 'bg'
            });
            if( goal_count+1 >= 3 ){
                $('#add_goal_btn').hide();
            }
            goal_count++;
        }
    }

    $('.sign_btn').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_2.presign", $attestation_form->id) }}',
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
                        url: '{{ route("attestationforms.step_2.sign", $attestation_form->id) }}',
                        dataType : "json",
                        data: { 
                          _token: "{{ csrf_token() }}",
                          signed_goals: json.signature
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

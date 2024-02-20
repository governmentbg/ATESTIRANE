@extends('layouts.app')

@section('title') Атестационен формуляр - Раздел 3 <a href="{{ route('attestationforms.preview', ['id' => $attestation_form->id]) }}" class="btn btn-secondary ms-3">Назад</a> @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Среща
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3 g-4">
            <div class="col-md-12">
                @if( $attestation_form->meeting && $attestation_form->meeting->requested_by )
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Междинна среща</div>
                    </div>        
                    <div class="card-body">
                        <div class="mb-3 col-md-12">
                            <label for="initiator" class="form-label">По инициатива на</label>
                            <input type="text" class="form-control" id="initiator" value="{{ $attestation_form->meeting->requested_user->name }}" disabled>
                        </div>

                        @if( $edit_mode )
                        <form action="{{ route('attestationforms.step_3.save', $attestation_form->id) }}" method="POST">
                            @csrf
                        @endif
                            <div class="mb-3 col-md-12">
                                <label for="comment" class="form-label">Коментар на прекия ръководител относно изпълнението на длъжността и показаните компетентности</label>
                                <textarea class="form-control" name="director_comment" id="comment" rows="6" {{ $director_edit ? 'required':'disabled' }} >{{ $attestation_form->meeting->director_comment }}</textarea>
                            
                                <label for="comment" class="form-label">Коментар на оценявания</label>
                                <textarea class="form-control" name="employee_comment" id="comment" rows="6" {{ $employee_edit ? '':'disabled' }}>{{ $attestation_form->meeting->employee_comment }}</textarea>
                            </div>
                        @if( $edit_mode )
                            <button type="submit" class="btn btn-primary">Запази</button>
                        </form>
                        @endif
                    </div>
                </div>
                @if( $attestation_form->meeting->signed_data && $attestation_form->meeting->signed_director_at )
                    <div class="accordion mt-3" id="meeting_signing">

                      <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $director_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->meeting->signed_director_at)) }}
                          </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#meeting_signing">
                          <div class="accordion-body">
                            Издател: <strong>{{ $director_certificate['issuer']['CN'] }}</strong><br/>
                            Сериен Номер: <strong>{{ $director_certificate['serialNumber'] }}</strong><br/>
                            Валиден от: <strong>{{ date('d.m.Y H:i:s', $director_certificate['validFrom_time_t']) }}</strong><br/>
                            Валиден до: <strong>{{ date('d.m.Y H:i:s', $director_certificate['validTo_time_t']) }}</strong><br/>
                          </div>
                        </div>
                      </div>
                      @if( $attestation_form->meeting->signed_employee_at )
                          <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <i class="bi bi-person-fill-check fs-3 me-2 text-success"></i> Подписано с КЕП от: {{ $employee_certificate['subject']['CN'] }} на дата: {{ date('d.m.Y H:i:s', strtotime($attestation_form->meeting->signed_employee_at)) }}
                              </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#meeting_signing">
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
                @else
                    <div class="text-center">
                        <form action="{{ route('attestationforms.step_3.request', $attestation_form->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-lg btn-success">Заяви среща</button>
                        </form>  
                    </div>
                @endif

                @if( 
                    (session('role_id') == 4 && $attestation_form->meeting && $attestation_form->meeting->signed_director_at && !$attestation_form->meeting->signed_employee_at && !$employee_edit) || 
                    (session('role_id') == 3 && $attestation_form->meeting && !$attestation_form->meeting->signed_director_at && !$director_edit) )
                    <div class="actions_container mt-4 text-center">
                        <button type="button" class="btn btn-primary sign_btn">Подпиши с КЕП</button>
                    </div>
                @endif
            </div>
        </div>
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
    });
    $('.sign_btn').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '{{ route("attestationforms.step_3.presign", $attestation_form->id) }}',
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
                        url: '{{ route("attestationforms.step_3.sign", $attestation_form->id) }}',
                        dataType : "json",
                        data: { 
                          _token: "{{ csrf_token() }}",
                          signed_data: json.signature
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

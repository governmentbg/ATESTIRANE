@extends('layouts.app')

@section('title') Справки @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Справки
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <form action="{{ route('checks.show') }}" method="GET">
                    <div class="form-group">
                        <label class="form-label">Вид справка:</label>
                        <select class="form-control" name="type" id="report_select">
                            <option value="no_goals">Служители без работен план в срок</option>
                            <option value="no_score">Служители без годишна оценка в срок</option>
                            <option value="percent_by_organisations">% атестирани служители по структурни звена</option>
                            <option value="scores_by_organisation">Атестационни оценки от предходни атестации по структурни звена</option>
                            <option value="rank_upgrade">Служители за повишаване в ранг</option>
                            <option value="attestation_form">Личен атестационен формуляр на служителя</option>
                        </select>
                    </div>
                    <div class="form-group mt-3" id="organisation_select" style="display: none;">
                        <label class="form-label">Структурно звено:</label>
                        <select class="form-select @error('organisation_id') is-invalid @enderror" name="organisation_id" id="organisation_id">
                            @if(!empty($html))
                            @foreach($html as $key => $value)
                            {!! $value !!}
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group mt-3" id="user_select" style="display: none;">
                        <label for="user_id" class="form-label required">Име на служителя</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id">
                            <option value="" default>-- Изберете служител --</option>
                        </select>
                    </div>
                    <div class="form-group mt-3" id="year_select" style="display: none;">
                        <label class="form-label">Година:</label>
                        <select name="year[]" multiple class="form-control" required>
                            @foreach( $attestations as $attestation)
                                <option value="{{ $attestation->year }}">{{ $attestation->year }}</option>
                                @if ($loop->last)
                                    <option value="{{ $attestation->year-1 }}">{{ $attestation->year-1 }}</option>
                                    <option value="{{ $attestation->year-2 }}">{{ $attestation->year-2 }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Генерирай</button>
                </form>
            </div>
        </div>
        @if (session('success'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') ?? 'success' }}
                </div>
            </div>
        </div>
        @endif
        @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? 'error' }}
                </div>
            </div>
        </div>
        @endif
        <div class="row g-4">
            <div class="col-md-12">
                
            </div>
        </div>
    </div>
    @include('employees.delete-dialog')
@endsection

@section ('scripts')
<script>
$(document).ready(function() {
    show_organisation_select();
    show_user_select();
    show_year_select();
});

$('#report_select').on('change', show_organisation_select);
$('#report_select').on('change', show_user_select);
$('#report_select').on('change', show_year_select);

function show_organisation_select(){
    if( $('#report_select').val() == 'scores_by_organisation' || $('#report_select').val() == 'attestation_form' ){
        $('#organisation_select').show();
        $('select[name="organisation_id"]').attr('disabled', false);
        load_users();
    } else {
        $('#organisation_select').hide();
        $('select[name="organisation_id"]').attr('disabled', true);
    }
}

function show_user_select(){
    if( $('#report_select').val() == 'attestation_form' ){
        $('#user_select').show();
        $('select[name="user_id"]').attr('required', true);
        $('select[name="user_id"]').attr('disabled', false);
    } else {
        $('#user_select').hide();
        $('select[name="user_id"]').attr('required', false);
        $('select[name="user_id"]').attr('disabled', true);
    }
}

function show_year_select(){
    if( $('#report_select').val() == 'scores_by_organisation' || $('#report_select').val() == 'attestation_form' ){
        $('#year_select').show();
        $('select[name="year[]"]').attr('required', true);
        $('select[name="year[]"]').attr('disabled', false);
        if( $('#report_select').val() == 'attestation_form' ){
            $('select[name="year[]"]').attr('multiple', false);
        } else {
            $('select[name="year[]"]').attr('multiple', true);
        }
    } else {
        $('#year_select').hide();
        $('select[name="year[]"]').attr('required', false);
        $('select[name="year[]"]').attr('disabled', true);
    }
}

$('#organisation_id').on('change', load_users);

function load_users(){
    var organisation_id = $('#organisation_id').val();
    
    $('#user_id').find('option:not([default])').remove().trigger('select2:change');
    
    if(organisation_id !== ''){
        $.ajax({
            url: '{{ route('organisations.list_users') }}',
            type: 'GET',
            data: {
                'organisation_id': organisation_id
            },
            dataType: 'json',
            success: function(data){
                if(data.results !== undefined){
                    $.each(data.results, function(el, val){
                        var newOption = new Option(val.name, val.id, false, false);
                        
                        // add options
                        $('#user_id').append(newOption);
                    });
                    
                    // force update
                    $('#user_id').trigger('select2:change');
                }
            }
        });
    }
}

</script>
@endsection
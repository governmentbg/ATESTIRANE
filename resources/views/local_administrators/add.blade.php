@extends('layouts.app')

@section('title') Локални администратори @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('local_administrators.list') }}">Локални администратори</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Добавяне
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('local_administrators.store') }}">
                    @csrf
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Добави локален администратор</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="organisation_id" class="form-label required">Организационно звено</label>
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" name="organisation_id" id="organisation_id">
                                        <option value="">-- Изберете Организационно звено --</option>
                                        @if(!empty($html))
                                        @foreach($html as $key => $value)
                                        {!! $value !!}
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Организационно звено
                                    </div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="user_id" class="form-label required">Име на служителя</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" name="user_id" id="user_id">
                                        <option value="" default>-- Изберете служител --</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Служител
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('local_administrators.list') }}" class="btn btn-secondary me-3">Върни се</a>
                            <button type="submit" class="btn btn-primary">Запази</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script>
$(document).ready(function() {
    $('#user_id').select2({
        theme: 'bootstrap-5'
    });
    
    $('.datepicker').datepicker({
        language: 'bg'
    });
    
    $('#organisation_id').on('change', function(e){
        load_users();
    });
    
    load_users();
});

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

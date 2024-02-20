@extends('layouts.app')

@section('title') Атестационни комисии @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('commissions.list') }}">Атестационни комисии</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    @if(!$id)
    Добавяне
    @else
    Редакция
    @endif
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('commissions.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $commission->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif комисия</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label required" for="members">Членове</label>
                                    <select class="js-basic-multiple form-select @error('members') is-invalid @enderror" name="members[]" id="members" multiple="multiple">
                                        @foreach($members as $user)
                                            <option value="{{ $user->id }}" @selected((old('members') !== null && in_array($user->id, old('members'))) || in_array($user->id, $selected_members))>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете членове които да извършат оценяването
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label required" for="director_id">Оценяващ ръководител</label>
                                    <select class="form-select @error('director_id') is-invalid @enderror" name="director_id" id="director_id">
                                        <option value="" default>-- изберете --</option>
                                        @foreach($members as $user)
                                            <option value="{{ $user->id }}" @selected(($id && $user->id == (old('director_id') ?? $commission->director_id)) || (!$id && $user->id == old('director_id')))>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете "Оценяващ ръководител"
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label required" for="valid_until">Срок на валидност до</label>
                                    <div class="input-group @error('valid_until') is-invalid @enderror">
                                        <input type="text" class="form-control datepicker @error('valid_until') is-invalid @enderror" name="valid_until" value="{{ old('valid_until') ?? ($id ? $commission->valid_until : '') }}" id="valid_until" placeholder="Дата на заповед за утвърждение" autocomplete="off">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Моля изберете срок на валидност
                                    </div>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label required" for="approval_order">Номер на заповед за утвърждение</label>
                                    <input type="text" class="form-control @error('approval_order') is-invalid @enderror" name="approval_order" value="{{ old('approval_order') ?? ($id ? $commission->approval_order : '') }}" id="approval_order" autocomplete="off">
                                    <div class="invalid-feedback">
                                        Моля попълнете номер на заповед за утвърждение
                                    </div>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label required" for="approval_date">Дата на заповед за утвърждение</label>
                                    <div class="input-group @error('approval_date') is-invalid @enderror">
                                        <input type="text" class="form-control datepicker @error('approval_date') is-invalid @enderror" name="approval_date" value="{{ old('approval_date') ?? ($id ? $commission->approval_date : '') }}" id="approval_date" autocomplete="off">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Моля изберете дата на заповедта за утвърждение
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label required" for="evaluated_members">Оценявани</label>
                                    <select class="js-basic-multiple form-control @error('evaluated_members') is-invalid @enderror" name="evaluated_members[]" id="evaluated_members" multiple="multiple">
                                        @foreach($evaluated_members as $user)
                                            <option value="{{ $user->id }}" @selected((old('evaluated_members') !== null && in_array($user->id, old('evaluated_members'))) || in_array($user->id, $selected_evaluated_members)) disabled>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете членове които да бъдат оценявани
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('commissions.list') }}" class="btn btn-secondary me-3">Върни се</a>
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
    $('.js-basic-multiple').select2({
        theme: 'bootstrap-5'
    });
    
    $('#members').on('change', function(e){
        update_director();
    });
    
    $('.datepicker').datepicker({
        language: 'bg',
        todayHighlight: true,
        todayBtn: 'linked',
        clearBtn: true,
        daysOfWeekHighlighted: '0, 6',
        orientation: 'bottom auto'
    });
    
    update_director();
});

function update_director(state){
    var ids = $('#members').val(),
        options = $('#director_id').find('option:not([default])');
    
    options.addClass('d-none');
    
    $('#evaluated_members option').attr('disabled', false);
    
    $.each(ids, function(index, value){
        var _director = options.filter('[value="'+value+'"]');
        
        if(_director.length > 0){
            _director.removeClass('d-none');
        }
        
        $('#evaluated_members option[value="'+value+'"]').prop('selected', false).attr('disabled', true);
    });
    
    if($("#director_id option:selected").hasClass('d-none') == true){
        $('#director_id').val('').change();
    }
    
    $('#evaluated_members').select2("destroy").select2({
        theme: 'bootstrap-5'
    });
}
</script>
@endsection

@extends('layouts.app')

@section('title') Електронни досиета @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('employees.list') }}">Електронни досиета</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    @if(!$id)
    Добавяне
    @else
    Редактиране
    @endif
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('employees.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif досие</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label required">Имена</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? ($id ? $user->name : '') }}" id="name" placeholder="Въведете собственото и фамилно име на служителя">
                                    <div class="invalid-feedback">
                                        Моля въведете Имена на служителя
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="egn" class="form-label required">ЕГН</label>
                                    <input type="text" class="form-control @error('egn') is-invalid @enderror" name="egn" value="{{ old('egn') ?? ($id ? $user->egn : '') }}" id="egn" placeholder="Въведете ЕГН">
                                    <div class="invalid-feedback">
                                        Моля въведете валидно ЕГН
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="private_number" class="form-label">Личен номер</label>
                                    <input type="text" class="form-control @error('private_number') is-invalid @enderror" name="private_number" value="{{ $user->id ?? '' }}" id="private_number" placeholder="" disabled>
                                    <div class="invalid-feedback">
                                        Моля въведете Личен номер
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label required">Електронна поща</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ?? ($id ? $user->email : '') }}" id="email" placeholder="Въведете електронна поща">
                                    <div class="invalid-feedback">
                                        Моля въведете Електронна поща
                                    </div>
                                </div>
                                <div class="mb-3 col-md-2">
                                    <label for="rank" class="form-label required">Ранг</label>
                                    <input type="text" class="form-control @error('rank') is-invalid @enderror" name="rank" value="{{ old('rank') ?? ($id ? $user->rank : '') }}" id="rank" placeholder="Въведете ранг">
                                    <div class="invalid-feedback">
                                        Моля въведете Ранг
                                    </div>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="rank" class="form-label">Начин за придобиване на ранга</label>
                                    <select class="form-select @error('rank_acquisition') is-invalid @enderror" name="rank_acquisition" id="rank_acquisition">
                                        <option value="normal" @selected(old('rank_acquisition') == 'normal' || ($id ? $user->rank_acquisition == 'normal' : false))>Повишаване в ранг по общия ред</option>
                                        <option value="early" @selected(old('rank_acquisition') == 'early' || ($id ? $user->rank_acquisition == 'early' : false))>Предсрочно</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля въведете Начин за придобиване на ранга
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="organisation_id" class="form-label required">Организационно звено</label>
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" name="organisation_id" id="organisation_id">
                                        <option value="">-- Изберете структура --</option>
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
                                    <label for="position_id" class="form-label required">Длъжност</label>
                                    <select class="form-select @error('position_id') is-invalid @enderror" name="position_id" id="position_id">
                                        <option value="">-- Изберете длъжност --</option>
                                        @foreach($positions as $category => $category_positions)
                                        <optgroup label="{{ $category }}">
                                            @foreach($category_positions as $position)
                                            <option value="{{ $position->id }}" @selected(old('position_id') == $position->id || ($id ? $user->position_id == $position->id : false))>{{ '('.$position->nkpd1.'-'.$position->nkpd2.') '.$position->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Длъжност
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="col-form-label required">Подлежи на електронна атестация</label>
                                    <div class="form-check">
                                        <input class="form-check-input @error('digital_attestation') is-invalid @enderror" type="radio" name="digital_attestation" id="digital_attestation_yes" value="1" @checked((old('digital_attestation') !== null && old('digital_attestation') == 1) || ($user && $user->digital_attestation == 1))>
                                        <label class="form-check-label" for="digital_attestation_yes">
                                            Да
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('digital_attestation') is-invalid @enderror" type="radio" name="digital_attestation" id="digital_attestation_no" value="0" @checked((old('digital_attestation') !== null && old('digital_attestation') == 0) || ($user && $user->digital_attestation == 0))>
                                        <label class="form-check-label" for="digital_attestation_no">
                                            Не
                                        </label>
                                        <div class="invalid-feedback">
                                            Моля изберете дали служителят подлежи на електронна атестация
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-3">
                                    <label for="appointment_date" class="form-label required">Дата на назначаване</label>
                                    <div class="input-group mt-0 @error('appointment_date') is-invalid @enderror date">
                                        <input type="text" class="form-control @error('appointment_date') is-invalid @enderror datepicker" name="appointment_date" value="{{ old('appointment_date') ?? ($id ? $user->appointment_date : '') }}" id="appointment_date">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Моля изберете Дата на назначаване
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="reassignment_date" class="form-label">Дата на преназначаване</label>
                                    <div class="input-group mt-0 date">
                                        <input type="text" class="form-control datepicker" name="reassignment_date" value="{{ old('reassignment_date') ?? ($id ? $user->reassignment_date : '') }}" id="reassignment_date">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="leaving_date" class="form-label">Дата на освобождаване</label>
                                    <div class="input-group mt-0 date">
                                        <input type="text" class="form-control datepicker" name="leaving_date" value="{{ old('leaving_date') ?? ($id ? $user->leaving_date : '') }}" id="leaving_date">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="returning_date" class="form-label">Дата на завръщане</label>
                                    <div class="input-group mt-0 date">
                                        <input type="text" class="form-control datepicker" name="returning_date" value="{{ old('returning_date') ?? ($id ? $user->returning_date : '') }}" id="returning_date">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                    <div class="form-text text-muted">* след дълъг отпуск/майчинство</div>
                                </div>
                            </div>
                            <div class="input-group @error('picture') is-invalid @enderror">
                                <input type="file" class="form-control @error('picture') is-invalid @enderror" name="picture" id="picture" accept=".jpeg, .png, .jpg, .gif">
                                <label class="input-group-text" for="picture">Качи снимка</label>
                            </div>
                            <div class="form-text text-muted">позволени формати за снимка: .jpeg, .png, .jpg, .gif</div>
                            <div class="invalid-feedback">
                                Моля изберете валиден формат на снимка
                            </div>
                            @if(!empty($user->photo_url))
                            <p class="mt-3">
                                <i class="bi bi-file-image"></i> <a href="{{ route('display_image', ['filename' => $user->photo_url]) }}" target="_blank">снимка</a>
                            </p>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" name="picture_delete" value="1" id="picture_delete">
                                <label class="form-check-label" for="picture_delete">Изтриване!</label>
                            </div>
                            @endif
                            <hr/>
                            <h5>Оценки от предишни 2 атестации</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Година на атестиране 1</label>
                                    <input type="text" class="form-control yearpicker" name="old_attestation_year_1" value="{{ old('old_attestation_year_1') ?? ($id ? $user->old_attestation_year_1 : '') }}" id="old_attestation_year_1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Оценка 1</label>
                                    <select name="old_attestation_score_1" class="form-control">
                                        <option value="">Липсва информация</option>
                                        @foreach( $total_score_types as $score_type )
                                            <option value="{{ $score_type->type }}" @selected($id && $user->old_attestation_score_1 == $score_type->type)>{{ $score_type->text_score }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Година на атестиране 2</label>
                                    <input type="text" class="form-control yearpicker" name="old_attestation_year_2" value="{{ old('old_attestation_year_2') ?? ($id ? $user->old_attestation_year_2 : '') }}" id="old_attestation_year_2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Оценка 2</label>
                                    <select name="old_attestation_score_2" class="form-control">
                                        <option value="">Липсва информация</option>
                                        @foreach( $total_score_types as $score_type )
                                            <option value="{{ $score_type->type }}" @selected($id && $user->old_attestation_score_2 == $score_type->type)>{{ $score_type->text_score }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            
                            
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('employees.list') }}" class="btn btn-secondary me-3">Върни се</a>
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
    $('.input-group.date > .datepicker').datepicker({
        language: 'bg',
        todayHighlight: true,
        todayBtn: 'linked',
        clearBtn: true,
        daysOfWeekHighlighted: '0, 6',
    });
    $('.yearpicker').datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years",
        endDate: "{{ date('Y') }}"
    });
});
</script>
@endsection
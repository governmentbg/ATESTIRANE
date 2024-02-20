@extends('layouts.app')

@section('title') Общи оценки @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('total_score.types') }}">Видове формуляри</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Обща оценка - 
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
                <form method="POST" action="{{ route('total_score.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $total_score->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif Обща оценка</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="attestation_form_type">Вид</label>
                                    <select class="form-select @error('attestation_form_type') is-invalid @enderror" name="attestation_form_type" id="attestation_form_type">
                                        <option value="">-- Изберете вид --</option>
                                        <option value="management" @selected(old('attestation_form_type') == 'management' || ($id ? $total_score->attestation_form_type == 'management' : false))>за ръководни длъжности и служители, на които са възложени ръководни функции</option>
                                        <option value="experts" @selected(old('attestation_form_type') == 'experts' || ($id ? $total_score->attestation_form_type == 'experts' : false))>за служители на експертни длъжности</option>
                                        <option value="general"  @selected(old('attestation_form_type') == 'general' || ($id ? $total_score->attestation_form_type == 'general' : false))>за съдебни и прокурорски помощници</option>
                                        <option value="technical" @selected(old('attestation_form_type') == 'technical' || ($id ? $total_score->attestation_form_type == 'technical' : false))>а служители, заемащи технически и други специфични длъжности</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Вид
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-2">
                                    <label for="text_score" class="form-label required">Оценка</label>
                                    <select class="form-select @error('type') is-invalid @enderror" name="type" id="type">
                                        <option value="">-- Изберете оценка --</option>
                                        <option value="Оценка 1" @selected(old('type') == 'Оценка 1' || ($id ? $total_score->type == 'Оценка 1' : false))>Оценка 1</option>
                                        <option value="Оценка 2" @selected(old('type') == 'Оценка 2' || ($id ? $total_score->type == 'Оценка 2' : false))>Оценка 2</option>
                                        <option value="Оценка 3" @selected(old('type') == 'Оценка 3' || ($id ? $total_score->type == 'Оценка 3' : false))>Оценка 3</option>
                                        <option value="Оценка 4" @selected(old('type') == 'Оценка 4' || ($id ? $total_score->type == 'Оценка 4' : false))>Оценка 4</option>
                                        <option value="Оценка 5" @selected(old('type') == 'Оценка 5' || ($id ? $total_score->type == 'Оценка 5' : false))>Оценка 5</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-8">
                                    <label for="text_score" class="form-label required">Оценка - текст</label>
                                    <input type="text" class="form-control @error('text_score') is-invalid @enderror" name="text_score" value="{{ old('text_score') ?? ($id ? $total_score->text_score : '') }}" id="text_score" placeholder="Въведете Оценка">
                                    <div class="invalid-feedback">
                                        Моля въведете Оценка
                                    </div>
                                </div>
                                <div class="mb-3 col-md-1">
                                    <label for="from_points" class="form-label required">Точки (от)</label>
                                    <input type="number" class="form-control @error('from_points') is-invalid @enderror" name="from_points" value="{{ old('from_points') ?? ($id ? $total_score->from_points : 1) }}" id="from_points" min="0" step="1">
                                    <div class="invalid-feedback">
                                        Въведете Точки
                                    </div>
                                </div>
                                <div class="mb-3 col-md-1">
                                    <label for="to_points" class="form-label required">Точки (до)</label>
                                    <input type="number" class="form-control @error('to_points') is-invalid @enderror" name="to_points" value="{{ old('to_points') ?? ($id ? $total_score->to_points : 1) }}" id="to_points" min="0" step="1">
                                    <div class="invalid-feedback">
                                        Въведете Точки
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($id && in_array($total_score->attestation_form_type, ['management', 'experts', 'general', 'technical']))
                            <a href="{{ route('total_score.list', ['type' => $total_score->attestation_form_type]) }}" class="btn btn-secondary me-3">Върни се</a>
                            @else
                            <a href="{{ route('total_score.types') }}" class="btn btn-secondary me-3">Върни се</a>
                            @endif
                            <button type="submit" class="btn btn-primary">Запази</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script>$(document).ready(function() {
    $('.js-basic-multiple').select2({
        theme: 'bootstrap-5'
    });
    $('.datepicker').datepicker({
        language: 'bg'
    });
});
</script>
@endsection

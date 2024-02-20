@extends('layouts.app')

@section('title') Оценки на задължения/цели @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('goals_score.types') }}">Видове формуляри</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Оценка на задължения/цели - 
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
                <form method="POST" action="{{ route('goals_score.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $goals_score->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif Оценка на задължения/цели</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="attestation_form_type">Вид</label>
                                    <select class="form-select @error('attestation_form_type') is-invalid @enderror" name="attestation_form_type" id="attestation_form_type">
                                        <option value="">-- Изберете вид --</option>
                                        <option value="management" @selected(old('attestation_form_type') == 'management' || ($id ? $goals_score->attestation_form_type == 'management' : false))>за ръководни длъжности и служители, на които са възложени ръководни функции</option>
                                        <option value="experts" @selected(old('attestation_form_type') == 'experts' || ($id ? $goals_score->attestation_form_type == 'experts' : false))>за служители на експертни длъжности</option>
                                        <option value="general"  @selected(old('attestation_form_type') == 'general' || ($id ? $goals_score->attestation_form_type == 'general' : false))>за съдебни и прокурорски помощници</option>
                                        <option value="technical" @selected(old('attestation_form_type') == 'technical' || ($id ? $goals_score->attestation_form_type == 'technical' : false))>а служители, заемащи технически и други специфични длъжности</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Вид
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-11">
                                    <label for="text_score" class="form-label required">Оценка</label>
                                    <input type="text" class="form-control @error('text_score') is-invalid @enderror" name="text_score" value="{{ old('text_score') ?? ($id ? $goals_score->text_score : '') }}" id="text_score" placeholder="Въведете Оценка">
                                    <div class="invalid-feedback">
                                        Моля въведете Оценка
                                    </div>
                                </div>
                                <div class="mb-3 col-md-1">
                                    <label for="points" class="form-label required">Точки</label>
                                    <input type="number" class="form-control @error('points') is-invalid @enderror" name="points" value="{{ old('points') ?? ($id ? $goals_score->points : 0) }}" id="points" min="0" step="1">
                                    <div class="invalid-feedback">
                                        Въведете Точки
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($id && in_array($goals_score->attestation_form_type, ['management', 'experts', 'general', 'technical']))
                            <a href="{{ route('goals_score.list', ['type' => $goals_score->attestation_form_type]) }}" class="btn btn-secondary me-3">Върни се</a>
                            @else
                            <a href="{{ route('goals_score.types') }}" class="btn btn-secondary me-3">Върни се</a>
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

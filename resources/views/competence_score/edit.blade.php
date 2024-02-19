@extends('layouts.app')

@section('title') Оценки на компетентности @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('competence_score.types') }}">Видове формуляри</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Оценкa на компетентности - 
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
                <form method="POST" action="{{ route('competence_score.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $competence_score->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif Оценка на компетентност</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label required" for="attestation_form_type">Вид</label>
                                    <select class="form-select @error('attestation_form_type') is-invalid @enderror" name="attestation_form_type" id="attestation_form_type">
                                        <option value="">-- Изберете вид --</option>
                                        <option value="management" @selected(old('attestation_form_type') == 'management' || ($id ? $competence_score->attestation_form_type == 'management' : false))>за ръководни длъжности и служители, на които са възложени ръководни функции</option>
                                        <option value="experts" @selected(old('attestation_form_type') == 'experts' || ($id ? $competence_score->attestation_form_type == 'experts' : false))>за служители на експертни длъжности</option>
                                        <option value="general"  @selected(old('attestation_form_type') == 'general' || ($id ? $competence_score->attestation_form_type == 'general' : false))>за съдебни и прокурорски помощници</option>
                                        <option value="technical" @selected(old('attestation_form_type') == 'technical' || ($id ? $competence_score->attestation_form_type == 'technical' : false))>а служители, заемащи технически и други специфични длъжности</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Вид
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-4">
                                    <label for="competence_type" class="form-label required">Вид компетентност</label>
                                    <input type="text" class="form-control @error('competence_type') is-invalid @enderror" name="competence_type" value="{{ old('competence_type') ?? ($id ? $competence_score->competence_type : '') }}" id="competence_type" placeholder="Въведете Вид компетентност">
                                    <div class="invalid-feedback">
                                        Моля въведете Вид компетентност
                                    </div>
                                </div>
                                <div class="mb-3 col-md-7">
                                    <label for="text_score" class="form-label required">Оценка</label>
                                    <input type="text" class="form-control @error('text_score') is-invalid @enderror" name="text_score" value="{{ old('text_score') ?? ($id ? $competence_score->text_score : '') }}" id="text_score" placeholder="Въведете Оценка">
                                    <div class="invalid-feedback">
                                        Моля въведете Оценка
                                    </div>
                                </div>
                                <div class="mb-3 col-md-1">
                                    <label for="points" class="form-label required">Точки</label>
                                    <input type="number" class="form-control @error('points') is-invalid @enderror" name="points" value="{{ old('points') ?? ($id ? $competence_score->points : 1) }}" id="points" min="0" step="1">
                                    <div class="invalid-feedback">
                                        Въведете Точки
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($id && in_array($competence_score->attestation_form_type, ['management', 'experts', 'general', 'technical']))
                            <a href="{{ route('competence_score.list', ['type' => $competence_score->attestation_form_type]) }}" class="btn btn-secondary me-3">Върни се</a>
                            @else
                            <a href="{{ route('competence_score.types') }}" class="btn btn-secondary me-3">Върни се</a>
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

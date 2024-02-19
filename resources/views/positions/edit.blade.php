@extends('layouts.app')

@section('title') Длъжности @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('positions.types') }}">Видове длъжности</a>
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
                <form method="POST" action="{{ route('positions.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $position->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif длъжност</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-4">
                                    <label for="name" class="form-label required">Име на длъжността</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? ($id ? $position->name : '') }}" id="name" placeholder="Въведете име">
                                    <div class="invalid-feedback">
                                        Моля въведете Име на длъжността
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label required" for="type">Вид</label>
                                    <select class="form-select @error('type') is-invalid @enderror" name="type" id="type">
                                        <option value="">-- Изберете вид --</option>
                                        <option value="management" @selected(old('type') == 'management' || ($id ? $position->type == 'management' : false))>Ръководни длъжности</option>
                                        <option value="experts" @selected(old('type') == 'experts' || ($id ? $position->type == 'experts' : false))>Експертни длъжности</option>
                                        <option value="general"  @selected(old('type') == 'general' || ($id ? $position->type == 'general' : false))>Обща/специализирана администрация</option>
                                        <option value="technical" @selected(old('type') == 'technical' || ($id ? $position->type == 'technical' : false))>Технически длъжности</option>
                                        <option value="specific" @selected(old('type') == 'specific' || ($id ? $position->type == 'specific' : false))>Специфични длъжности</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Вид на длъжността
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="nkpd1" class="form-label required">НКПД код</label>
                                    <div class="row">
                                        <div class="col">
                                            <input type="text" class="form-control @error('nkpd1') is-invalid @enderror" name="nkpd1" value="{{ old('nkpd1') ?? ($id ? $position->nkpd1 : '') }}" id="nkpd1" placeholder="XXXX">
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control @error('nkpd2') is-invalid @enderror" name="nkpd2" value="{{ old('nkpd2') ?? ($id ? $position->nkpd2 : '') }}" id="nkpd2" placeholder="XXXX">
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">
                                        Моля въведете НКПД код на длъжността
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-10">
                                    <label class="form-label required" for="attestation_form_type">Формуляр</label>
                                    <select class="form-select @error('attestation_form_type') is-invalid @enderror" name="attestation_form_type" id="attestation_form_type">
                                        <option value="management" @selected(old('attestation_form_type') == 'management' || ($id ? $position->attestation_form_type == 'management' : false))>ПРИЛОЖЕНИЕ 1 (за ръководни длъжности и служители, на които са възложени ръководни функции)</option>
                                        <option value="general"  @selected(old('attestation_form_type') == 'general' || ($id ? $position->attestation_form_type == 'general' : false))>ПРИЛОЖЕНИЕ 2 (за съдебни и прокурорски помощници)</option>
                                        <option value="experts" @selected(old('attestation_form_type') == 'experts' || ($id ? $position->attestation_form_type == 'experts' : false))>ПРИЛОЖЕНИЕ 3 (за служители на експертни длъжности)</option>
                                        <option value="technical" @selected(old('attestation_form_type') == 'technical' || ($id ? $position->attestation_form_type == 'technical' : false))>ПРИЛОЖЕНИЕ 4 (за служители, заемащи технически и други специфични длъжности)</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете вид формуляр
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($id && in_array($position->type, ['management', 'experts', 'general', 'technical']))
                            <a href="{{ route('positions.list', ['type' => $position->type]) }}" class="btn btn-secondary me-3">Върни се</a>
                            @else
                            <a href="{{ route('positions.types') }}" class="btn btn-secondary me-3">Върни се</a>
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

@extends('layouts.app')

@section('title') Генериране на нова атестация @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Генериране на нова атестация
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('attestation.update') }}" id="attestation_form">
                    @csrf
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Нова атестация</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <div class="alert alert-primary mt-3 mb-0" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i> В момента има активна атестация с период 
                                        от <b>{{ date('d.m.Y', strtotime($attestation->period_from)) }}</b> до <b>{{ date('d.m.Y', strtotime($attestation->period_to)) }}</b>.<br/>
                                        Към тази атестация има създадени <b>{{ $attestation->forms->count() }}</b> формуляра, от които <b>{{ $attestation->forms->where('status', '!=', 'completed')->count() }}</b> не са приключени. След създаването на новата атестация, тези формуляри няма да имат възможността да се приключат.
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-3">
                                    <label for="period_from" class="form-label">Начална дата</label>
                                    <div class="input-group mt-0 date">
                                        <input type="text" class="form-control @error('period_from') is-invalid @enderror datepicker" name="period_from" value="{{ old('period_from') }}" id="period_from" autocomplete="off">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="period_to" class="form-label">Крайна дата</label>
                                    <div class="input-group mt-0 date">
                                        <input type="text" class="form-control datepicker" name="period_to" value="{{ old('period_to') }}" id="period_to" autocomplete="off">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            
                            @if (session('error'))
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="alert alert-danger">
                                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? 'error' }}
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                                        <i class="bi bi-info-circle-fill"></i> Ако не попълните начална и крайна дата за атестация, периода за следващата атестация ще остане същия като настоящия.
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="col-form-label required">Вариант на атестационния формуляр за ръководни длъжности</label>
                                    <div class="form-check">
                                        <input class="form-check-input @error('management_form_version') is-invalid @enderror" type="radio" name="management_form_version" value="1" id="management_form_version_1" @checked(old('management_form_version') !== null && old('management_form_version') == 1)>
                                        <label class="form-check-label" for="management_form_version_1">
                                            Вариант 1
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input @error('management_form_version') is-invalid @enderror" type="radio" name="management_form_version" value="2" id="management_form_version_2" @checked(old('management_form_version') !== null && old('management_form_version') == 2)>
                                        <label class="form-check-label" for="management_form_version_2">
                                            Вариант 2
                                        </label>
                                        <div class="invalid-feedback">
                                            Моля изберете Вариант на атестационния формуляр за ръководни длъжности
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary submit_btn">Запази</button>
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
        orientation: 'left bottom'
    });
});
$('.submit_btn').on('click', function(event){
    event.preventDefault();
    if( confirm("Сигурни ли сте, че искате да стартирате нова атестация? В момента има активна атестация с период от {{ date('d.m.Y', strtotime($attestation->period_from)) }} до {{ date('d.m.Y', strtotime($attestation->period_to)) }}. Към тази атестация има създадени {{ $attestation->forms->count() }} формуляра, от които {{ $attestation->forms->where('status', '!=', 'completed')->count() }} не са приключени. След създаването на новата атестация, тези формуляри няма да имат възможността да се приключат.") ){
        $('#attestation_form').submit();
    }
});
</script>
@endsection
@extends('layouts.app')

@section('title') Електронни досиета @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('employees.list') }}">Електронни досиета</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    {{ $user->name }}
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Информация за служителя</h3>
                    </div>
                    <div class="card-body">
                        Личен номер: {{ $user->id ?? '' }}
                        <br/>Имена: {{ $user->name ?? '' }}
                        <br/>ЕГН: {{ $user->egn ?? '' }}
                        <br/>Ел. поща: {{ $user->email ?? '' }}
                        <br/>Ранг: {{ $user->rank ?? '' }}
                        <br/>Начин за придобиване на ранга: {{ $user->rank_acquisition ? ($user->rank_acquisition == 'normal' ? 'Повишаване в ранг по общия ред':'Предсрочно'):'-' }}
                        <br/>Длъжност: {{ $user->position->name ?? '' }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Снимка</h3>
                    </div>
                    <div class="card-body">
                        @if(!empty($user->photo_url))
                        <img src="{{ route('display_image', ['filename' => $user->photo_url]) }}" class="img-fluid">
                        @else
                        <h3><i class="nav-icon bi bi-person-bounding-box"></i></h3>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Подлежи на атестация</h3>
                    </div>
                    <div class="card-body">
                        @if($user->digital_attestation == 1)
                        <h3>Да</h3>
                        @else
                        <h3>Не</h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-3">
            <div class="col-md-3">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Дата на назначаване</h3>
                    </div>
                    <div class="card-body">
                        @if($user->appointment_date)
                        {{ $user->appointment_date }} г.
                        @else
                        Неприложимо
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Дата на преназначаване</h3>
                    </div>
                    <div class="card-body">
                        @if($user->reassignment_date)
                        {{ $user->reassignment_date }} г.
                        @else
                        Неприложимо
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Дата на освобождаване</h3>
                    </div>
                    <div class="card-body">
                        @if($user->leaving_date)
                        {{ $user->leaving_date }} г.
                        @else
                        Неприложимо
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Дата на завръщане</h3>
                    </div>
                    <div class="card-body">
                        @if($user->returning_date)
                        {{ $user->returning_date }} г.
                        @else
                        Неприложимо
                        @endif
                    </div>
                </div>
                <div class="form-text text-muted">* след дълъг отпуск/майчинство</div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Оценки</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Година на атестиране</th>
                                            <th>Оценка</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if( $user->old_attestation_year_1 && $user->old_attestation_score_1 )
                                            <tr>
                                                <td>{{ $user->old_attestation_year_1 }}</td>
                                                <td>{{ $user->old_attestation_score_1 }}</td>
                                            </tr>
                                        @endif
                                        @if( $user->old_attestation_year_2 && $user->old_attestation_score_2 )
                                            <tr>
                                                <td>{{ $user->old_attestation_year_2 }}</td>
                                                <td>{{ $user->old_attestation_score_2 }}</td>
                                            </tr>
                                        @endif
                                        @foreach( $user->attestation_forms->where('status', 'completed') as $attestation_form )
                                            <tr>
                                                <td>{{ $attestation_form->attestation->year }}</td>
                                                <td>{{ $attestation_form->final_score }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="{{ route('employees.list') }}" class="btn btn-secondary mt-3">Върни се</a>
    </div>
@endsection
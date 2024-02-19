@extends('layouts.app')

@section('title') Атестационен формуляр - Раздел 1 @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Планиране
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3 g-4">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Лична информация на атестирания</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="mb-3 col-md-6">
                                <label for="names" class="form-label">Имена</label>
                                <input type="text" class="form-control" id="names" value="{{ $personal_data->name }}" disabled>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="position" class="form-label">Длъжност</label>
                                <input type="text" class="form-control" id="position" value="{{ $personal_data->position }}" disabled>
                            </div>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="organisation" class="form-label">Администрация</label>
                            <input type="text" class="form-control" value="{{ $personal_data->administration }}" disabled>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="division" class="form-label">Дирекция/отдел/служба</label>
                            <input type="text" class="form-control" value="{{ $personal_data->organisation }}" disabled>
                        </div>
                        <div class="row g-4">
                            <div class="mb-3 col-md-6">
                                <label for="datefrom" class="form-label">Период на оценяване от дата</label>
                                <input type="text" class="form-control" value="{{ $personal_data->from_date }}" disabled>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="dateto" class="form-label">Период на оценяване до дата</label>
                                <input type="text" class="form-control" value="{{ $personal_data->to_date }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('attestationforms.step_2.view', $id) }}" class="btn btn-primary float-end">Към Раздел 2</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
@endsection

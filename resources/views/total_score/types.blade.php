@extends('layouts.app')

@section('title') Общи оценки @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Видове формуляри
</li>
@endsection

@section('content')
    <div class="container-fluid">
        @if (session('success'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') ?? 'success' }}
                </div>
            </div>
        </div>
        @endif
        @if (session('error'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? 'error' }}
                </div>
            </div>
        </div>
        @endif
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Списък с видове формуляри</div>
                        <div class="card-tools">
                            <a href="{{ route('total_score.edit') }}" class="btn btn-primary">Добави Обща оценка</a>
                        </div>
                    </div>
                    <form>
                        <div class="card-body">
                            <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Вид формуляр</th>
                                    <th width="1%">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>за ръководни длъжности и служители, на които са възложени ръководни функции</td>
                                    <td>
                                        <a href="{{ route('total_score.list', ['type' => 'management']) }}" class="btn btn-primary">Избери</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>за служители на експертни длъжности</td>
                                    <td>
                                        <a href="{{ route('total_score.list', ['type' => 'experts']) }}" class="btn btn-primary">Избери</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>за съдебни и прокурорски помощници</td>
                                    <td>
                                        <a href="{{ route('total_score.list', ['type' => 'general']) }}" class="btn btn-primary">Избери</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>за служители, заемащи технически и други специфични длъжности</td>
                                    <td>
                                        <a href="{{ route('total_score.list', ['type' => 'technical']) }}" class="btn btn-primary">Избери</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script>$(document).ready(function() {
    $('.datepicker').datepicker({
        language: 'bg'
    });
});
</script>
@endsection
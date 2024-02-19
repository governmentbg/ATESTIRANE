@extends('layouts.app')

@section('title') Общи оценки @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('total_score.types') }}">Видове формуляри</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Общи оценки
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
                        <div class="card-title">
                            {{ $position_type }}
                        </div>
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
                                    <th>Оценка</th>
                                    <th width="1%" class="text-nowrap">Точки (от)</th>
                                    <th width="1%" class="text-nowrap">Точки (до)</th>
                                    <th width="1%">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($total_scores->isNotEmpty())
                                @foreach($total_scores as $total_score)
                                <tr>
                                    <td>{{ $total_score->id }}</td>
                                    <td>{{ $total_score->text_score }}</td>
                                    <td>{{ $total_score->from_points }}</td>
                                    <td>{{ $total_score->to_points }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('total_score.edit', ['id' => $total_score->id]) }}" class="btn btn-primary me-2"><i class="nav-icon bi bi-pencil"></i></a>
                                        <a href="{{ route('total_score.delete', ['id' => $total_score->id]) }}" class="btn btn-danger delete" data-bs-toggle="modal" data-bs-target="#deleteTotalScore"><i class="nav-icon bi bi-trash3"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="5" class="text-center">Списъкът е празен</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                        <div class="card-footer clearfix py-2">
                            {{ $total_scores->withQueryString()->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('total_score.delete-dialog')
@endsection

@section ('scripts')
<script>
var delete_url = null,
    can_close = true;

$(document).ready(function() {
    $(document.body).on('click', 'a.delete', function(e){
        e.preventDefault();
        
        delete_url = $(this).attr('href');
    });
    
    $('#deleteTotalScore').on('hide.bs.modal', function(e){
        return can_close;
    });
    
    $('#deleteTotalScore').on('hidden.bs.modal', function(e){
        delete_url = '';
    });
    
    $('.delete_submit', '#deleteTotalScore').on('click', function(e){
        e.preventDefault();
        
        if(delete_url != ''){
            $('#dialog_spinner', '#deleteTotalScore').removeClass('d-none');
            
            can_close = false;
            
            window.location.href = delete_url;
        }
    });
    
    $('.datepicker').datepicker({
        language: 'bg'
    });
});
</script>
@endsection
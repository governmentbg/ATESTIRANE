@extends('layouts.app')

@section('title') Длъжности @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('positions.types') }}">Видове длъжности</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Длъжности от вид "{{ $position_type }}"
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Филтрирай длъжности</div>
                        <div class="card-tools">
                            <a href="{{ route('positions.edit') }}" class="btn btn-primary">Добави длъжност</a>
                        </div>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <input type="search" class="form-control" name="name" value="{{ Request()->name }}" id="name" placeholder="Име на длъжността">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('positions.list', ['type' => $type]) }}" class="btn btn-secondary me-3">Изчисти филтри</a>
                            <button type="submit" class="btn btn-primary">Покажи</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
                        <div class="card-title">Списък с всички длъжности от вид "{{ $position_type }}"</div>
                    </div>
                    <form>
                        <div class="card-body">
                            <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Име</th>
                                    <th width="1%" class="text-nowrap">НКПД код</th>
                                    <th width="1%" class="text-center">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($positions->isNotEmpty())
                                @foreach($positions as $position)
                                <tr>
                                    <td>{{ $position->id }}</td>
                                    <td>{{ $position->name }}</td>
                                    <td>{{ $position->nkpd1 }} {{ $position->nkpd2 }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('positions.edit', ['id' => $position->id]) }}" class="btn btn-primary me-2"><i class="nav-icon bi bi-pencil"></i></a>
                                        <a href="{{ route('positions.delete', ['id' => $position->id]) }}" class="btn btn-danger delete" data-bs-toggle="modal" data-bs-target="#deletePositionModal"><i class="nav-icon bi bi-trash3"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="4" class="text-center">Списъкът е празен</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                        <div class="card-footer clearfix py-2">
                            {{ $positions->withQueryString()->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('positions.delete-dialog')
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
    
    $('#deletePositionModal').on('hide.bs.modal', function(e){
        return can_close;
    });
    
    $('#deletePositionModal').on('hidden.bs.modal', function(e){
        delete_url = '';
    });
    
    $('.delete_submit', '#deletePositionModal').on('click', function(e){
        e.preventDefault();
        
        if(delete_url != ''){
            $('#dialog_spinner', '#deletePositionModal').removeClass('d-none');
            
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
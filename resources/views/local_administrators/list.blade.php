@extends('layouts.app')

@section('title') Локални администратори @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Локални администратори
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Филтрирай администратори</div>
                        <div class="card-tools">
                            <a href="{{ route('local_administrators.add') }}" class="btn btn-primary">Добави нов</a>
                        </div>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <select class="form-select" name="organisation_id" id="organisation_id">
                                        <option value="">-- Изберете Организационно звено --</option>
                                        @if(!empty($html))
                                        @foreach($html as $key => $value)
                                        {!! $value !!}
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <input type="text" class="form-control" name="name" value="{{ Request()->name }}" id="name" placeholder="Име на служителя">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('local_administrators.list') }}" class="btn btn-secondary me-3">Изчисти филтри</a>
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
                        <div class="card-title">Списък с локални администратори</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Име</th>
                                    <th>Структура</th>
                                    <th width="1%">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($users->isNotEmpty())
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->organisation->name }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('local_administrators.delete', ['id' => $user->id]) }}" class="btn btn-danger delete" data-bs-toggle="modal" data-bs-target="#deleteAdministratorModal"><i class="nav-icon bi bi-trash3"></i></a>
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
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('local_administrators.delete-dialog')
@endsection

@section ('scripts')
<script>
var delete_url = null,
    can_close = true;

$(document).ready(function() {
    $('.datepicker').datepicker({
        language: 'bg'
    });
    
    $(document.body).on('click', 'a.delete', function(e){
        e.preventDefault();
        
        delete_url = $(this).attr('href');
    });
    
    $('#deleteAdministratorModal').on('hide.bs.modal', function(e){
        return can_close;
    });
    
    $('#deleteAdministratorModal').on('hidden.bs.modal', function(e){
        delete_url = '';
    });
    
    $('.delete_submit', '#deleteAdministratorModal').on('click', function(e){
        e.preventDefault();
        
        if(delete_url != ''){
            $('#dialog_spinner', '#deleteAdministratorModal').removeClass('d-none');
            
            can_close = false;
            
            window.location.href = delete_url;
        }
    });
});
</script>
@endsection
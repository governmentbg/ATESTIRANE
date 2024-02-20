@extends('layouts.app')

@section('title') Електронни досиета @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Електронни досиета
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Филтрирай досиета</div>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <input type="search" class="form-control" name="name" value="{{ Request()->name }}" id="name" placeholder="Име">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <input type="email" class="form-control" name="email" value="{{ Request()->email }}" id="email" placeholder="Електронна поща">
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" name="organisation_id" id="organisation_id">
                                        <option value="">-- Изберете структура --</option>
                                        @if(!empty($html))
                                        @foreach($html as $key => $value)
                                        {!! $value !!}
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <select class="form-select @error('position_id') is-invalid @enderror" name="position_id" id="position_id">
                                        <option value="">-- Изберете длъжност --</option>
                                        @foreach($positions as $key => $val)
                                        <optgroup label="{{ $key }}">
                                            @foreach($val as $key2 => $val2)
                                            <option value="{{ $val2->id }}" @selected(Request()->position_id == $val2->id)>{{ $val2->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <fieldset class="row mb-3">
                                <legend class="col-form-label col-sm-3 pt-0">Подлежи на електронна атестация</legend>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="digital_attestation" id="digital_attestation_yes" value="1" @checked(!isset(Request()->digital_attestation) || Request()->digital_attestation == 1)>
                                        <label class="form-check-label" for="digital_attestation_yes">
                                            Да
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="digital_attestation" id="digital_attestation_no" value="0" @checked(isset(Request()->digital_attestation) && Request()->digital_attestation == 0)>
                                        <label class="form-check-label" for="digital_attestation_no">
                                            Не
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('employees.list') }}" class="btn btn-secondary me-3">Изчисти филтри</a>
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
                        <div class="card-title">Списък с досиета</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th>Имена</th>
                                    <th>Ел. поща</th>
                                    <th>Длъжност</th>
                                    <th width="1%">Атестиране</th>
                                    <th width="1%" class="text-center">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($users->isNotEmpty())
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->position ? $user->position->name:'-' }}</td>
                                    <td class="text-center">
                                        @if($user->digital_attestation == 1)
                                        <span class="badge text-bg-success">Да</span>
                                        @else
                                        <span class="badge text-bg-danger">Не</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('employees.edit', ['id' => $user->id]) }}" class="btn btn-primary me-2"><i class="nav-icon bi bi-pencil"></i></a>
                                        <a href="{{ route('employees.view', ['id' => $user->id]) }}" class="btn btn-success me-2"><i class="nav-icon bi bi-person-lines-fill"></i></a>
                                        @if(Auth::user()->id != $user->id)
                                        <a href="{{ route('employees.delete', ['id' => $user->id]) }}" class="btn btn-danger delete" data-bs-toggle="modal" data-bs-target="#deleteEmplyeeModal"><i class="nav-icon bi bi-trash3"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center">Списъкът е празен</td>
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
    @include('employees.delete-dialog')
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
    
    $('#deleteEmplyeeModal').on('hide.bs.modal', function(e){
        return can_close;
    });
    
    $('#deleteEmplyeeModal').on('hidden.bs.modal', function(e){
        delete_url = '';
    });
    
    $('.delete_submit', '#deleteEmplyeeModal').on('click', function(e){
        e.preventDefault();
        
        if(delete_url != ''){
            $('#dialog_spinner', '#deleteEmplyeeModal').removeClass('d-none');
            
            can_close = false;
            
            window.location.href = delete_url;
        }
    });
});
</script>
@endsection
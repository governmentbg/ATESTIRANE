@extends('layouts.app')

@section('title') Атестационни комисии @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Атестационни комисии
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Филтрирай комисии</div>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-3">
                                    <input type="search" class="form-control" name="approval_order" value="{{ Request()->approval_order }}" id="approval_order" placeholder="Номер на заповед за утвърждение">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <div class="input-group mt-0">
                                        <input type="text" class="form-control datepicker" name="approval_date" value="{{ Request()->approval_date }}" id="approval_date" placeholder="Дата на заповед за утвърждение">
                                        <span class="input-group-text"><i class="nav-icon bi bi-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('commissions.list') }}" class="btn btn-secondary me-3">Изчисти филтри</a>
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
                        <div class="card-title">Списък с комисии</div>
                    </div>
                    <form>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="1%">#</th>
                                        <th>Атестация</th>
                                        <th>Атестация - период</th>
                                        <th>Заповед No.</th>
                                        <th>Заповед дата</th>
                                        <th>Срок</th>
                                        <th width="1%" class="text-center">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($commissions->isNotEmpty())
                                    @foreach($commissions as $commission)
                                    <tr class="{{ $commission->attestation->status == 'active' ? '':'table-success' }}" >
                                        <td>{{ $commission->id }}</td>
                                        <td>
                                            {{ $commission->attestation->year }}
                                        </td>
                                        <td>
                                            {{ date('d.m.Y', strtotime($commission->attestation->period_from)) }} - 
                                            {{ date('d.m.Y', strtotime($commission->attestation->period_to)) }}
                                        </td>
                                        <td>{{ $commission->approval_order }}</td>
                                        <td>{{ date('d.m.Y', strtotime($commission->approval_date)) }}</td>
                                        <td>{{ date('d.m.Y', strtotime($commission->valid_until)) }}</td>
                                        <td class="text-nowrap">
                                            @if( $commission->attestation->status == 'active' )
                                            {{--<a href="{{ route('commissions.edit', ['id' => $commission->id]) }}" class="btn btn-primary me-2"><i class="nav-icon bi bi-pencil"></i></a>--}}
                                            {{--<a href="{{ route('commissions.delete', ['id' => $commission->id]) }}" class="btn btn-danger delete" data-bs-toggle="modal" data-bs-target="#deleteCommissionModal"><i class="nav-icon bi bi-trash3"></i></a>--}}
                                            @endif
                                            <a href="{{ route('commissions.view', ['id' => $commission->id]) }}" class="btn btn-success"><i class="nav-icon bi bi-person-lines-fill"></i></a>
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
                            {{ $commissions->withQueryString()->links() }}
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
        language: 'bg',
        todayHighlight: true,
        todayBtn: 'linked',
        clearBtn: true,
        daysOfWeekHighlighted: '0, 6',
    });
});
</script>
@endsection
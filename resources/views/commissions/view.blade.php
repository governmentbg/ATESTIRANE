@extends('layouts.app')

@section('title') Атестационни комисии @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('commissions.list') }}">Атестационни комисии</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Информация за комисията
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Номер на заповедта</h3>
                    </div>
                    <div class="card-body">
                        {{ $commission->approval_order }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Дата на заповедта</h3>
                    </div>
                    <div class="card-body">
                        {{ date('d.m.Y', strtotime($commission->approval_date)) }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Срок на заповедта</h3>
                    </div>
                    <div class="card-body">
                        {{ date('d.m.Y', strtotime($commission->valid_until)) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-3">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Членове за комисията</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            @foreach($commission->members as $member)
                            <li>{{ $member->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Оценяващ ръководител</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>{{ $director_user->name }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Оценявани</h3>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            @foreach($commission->evaluated_members as $member)
                            <li>{{ $member->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <a href="{{ route('commissions.list') }}" class="btn btn-secondary mt-3">Върни се</a>
    </div>
@endsection
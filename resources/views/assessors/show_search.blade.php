@extends('layouts.app')

@section('title') Оценяващи потребители @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('assessors.list') }}">Оценяващи потребители</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Добавяне
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                @if (session('error'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') ?? 'error' }}
                        </div>
                    </div>
                </div>
                @endif
                
                <form method="POST" action="{{ route('assessors.search') }}">
                    @csrf
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Добави оценяващ потребител</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('search_egn') is-invalid @enderror" name="search_egn" value="{{ old('search_egn') }}" id="search_egn" placeholder="търси по ЕГН">
                                        <div class="invalid-feedback">
                                            Моля въведете ЕГН
                                        </div>
                                    </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-danger">Търси</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('assessors.list') }}" class="btn btn-secondary me-3">Върни се</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
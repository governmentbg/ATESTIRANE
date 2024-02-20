@extends('layouts.app')

@section('title') Оценяващи потребители @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('assessors.list') }}">Оценяващи потребители</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    @if(!$id)
    Добавяне
    @else
    Редактиране
    @endif
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('assessors.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $user->id ?? 0 }}">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">@if(!$id) Добави @else Редактирай @endif потребител</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label required">Имена</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? ($id ? $user->name : '') }}" id="name" placeholder="Въведете собственото и фамилно име на служителя">
                                    <div class="invalid-feedback">
                                        Моля въведете Имена на служителя
                                    </div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="egn" class="form-label required">ЕГН</label>
                                    <input type="text" class="form-control @error('egn') is-invalid @enderror" name="egn" value="{{ old('egn') ?? ($id ? $user->egn : '') }}" id="egn" placeholder="Въведете ЕГН">
                                    <div class="invalid-feedback">
                                        Моля въведете валидно ЕГН
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label required">Електронна поща</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') ?? ($id ? $user->email : '') }}" id="email" placeholder="Въведете електронна поща">
                                    <div class="invalid-feedback">
                                        Моля въведете Електронна поща
                                    </div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="organisation_id" class="form-label required">Организационно звено</label>
                                    <select class="form-select @error('organisation_id') is-invalid @enderror" name="organisation_id" id="organisation_id">
                                        <option value="">-- Изберете структура --</option>
                                        @if(!empty($html))
                                        @foreach($html as $key => $value)
                                        {!! $value !!}
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback">
                                        Моля изберете Организационно звено
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('assessors.list') }}" class="btn btn-secondary me-3">Върни се</a>
                            <button type="submit" class="btn btn-primary">Запази</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')

@endsection
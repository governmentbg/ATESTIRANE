@extends('layouts.app')

@section('title') Организационни структури @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('organisations.list') }}">Организационни структури</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Добавяне
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <form method="POST" action="{{ route('organisations.update') }}">
                    @csrf
                    <input type="hidden" name="id" value="0">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <div class="card-title">Добави Организационна структура</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label" for="parent_id">Принадлежи на:</label>
                                    <select class="form-select" name="parent_id" id="parent_id" disabled>
                                        <option value="0">-- изберете --</option>
                                        @if(!empty($html))
                                        @foreach($html as $key => $value)
                                        {!! $value !!}
                                        @endforeach
                                        @endif
                                    </select>
                                    <input type="hidden" name="parent_id" value="{{ $organisation->id }}">
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-10 mb-3">
                                    <label class="form-label required" for="name">Име</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name">
                                    <div class="invalid-feedback">
                                        Моля въведете Име на структурата
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label" for="status">Статус</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="1" @selected(old('status') == 1)>Активна</option>
                                        <option value="0" @selected(old('status') !== null && old('status') == 0)>Неактивна</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('organisations.list') }}" class="btn btn-secondary me-3">Върни се</a>
                            <button type="submit" class="btn btn-primary">Запази</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script>
$(document).ready(function() {
    $('#name').focus();
});
</script>
@endsection

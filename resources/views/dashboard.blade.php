@extends('layouts.app')

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
    </div>
@endsection
@extends('layouts.app')

@section('title') Атестационен формуляр @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Атестационен формуляр
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <i class="bi bi-person-fill-exclamation fs-3 me-2 text-danger"></i> В процес на стартиране на атестация.
                </div>
            </div>
        </div>
    </div>
@endsection

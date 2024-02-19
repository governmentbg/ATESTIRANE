@extends('layouts.app')

@section('title') Атестационни формуляри @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Атестационни формуляри
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Списък с Атестационни формуляри</div>
                    </div>
                    <form>
                        <div class="card-body">
                            <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Име</th>
                                    <th>Организационна структура</th>
                                    <th width="1%">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attestation_forms as $attestation_form)
                                <tr>
                                    <td>{{ $attestation_form->user->name }}</td>
                                    <td>{{ $attestation_form->user->organisation->name }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('attestationforms.preview', $attestation_form->id) }}" class="btn btn-primary me-2"><i class="nav-icon bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

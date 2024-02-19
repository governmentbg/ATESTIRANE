@extends('layouts.app')

@section('title') Организационни структури @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Организационни структури
</li>
@endsection

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
                        <div class="card-title">Списък</div>
                        <div class="card-tools">
                            <a href="{{ route('organisations.edit') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добави</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="items_list" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Име</th>
                                    <th width="1%" class="text-center"><i class="bi bi-gear"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($html))
                                @foreach($html as $key => $value)
                                {!! $value !!}
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="2" class="text-center">Списъкът е празен</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('organisations.delete-dialog')
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
    
    $('#deleteOrganisationModal').on('hide.bs.modal', function(e){
        return can_close;
    });
    
    $('#deleteOrganisationModal').on('hidden.bs.modal', function(e){
        delete_url = '';
    });
    
    $('.delete_submit', '#deleteOrganisationModal').on('click', function(e){
        e.preventDefault();
        
        if(delete_url != ''){
            $('#dialog_spinner', '#deleteOrganisationModal').removeClass('d-none');
            
            can_close = false;
            
            window.location.href = delete_url;
        }
    });
});
</script>
@endsection
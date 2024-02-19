@extends('layouts.app')

@section('title') Справки <a href="{{ route('checks.dashboard') }}" class="btn btn-secondary ms-3">Назад</a>@endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    <a href="{{ route('checks.dashboard') }}">Справки</a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    Справка за Служители без годишна оценка в срок
</li>
@endsection

@section('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('theme/plugins/datatables-buttons/css/buttons.bootstrap5.min.css') }}">
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
                        <div class="card-title">Справка за Служители без годишна оценка в срок</div>
                    </div>
                    <div class="card-body">
                        <table id="report_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th width="1%" class="text-nowrap">Личен номер</th>
                                    <th width="1%">ЕГН</th>
                                    <th width="1%">Имена</th>
                                    <th>Структура</th>
                                    <th>Длъжност</th>
                                    <th width="1%" class="text-nowrap">Пряк ръководител</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($forms->isNotEmpty())
                                @foreach($forms as $form)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $form->user->id }}</td>
                                    <td>{{ $form->user->egn }}</td>
                                    <td>{{ $form->user->name }}</td>
                                    <td>{{ $form->user->organisation->name }}</td>
                                    <td>{{ $form->user->position->name }}</td>
                                    <td>{{ $form->director->name }}</td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-center">Списъкът е празен</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix py-2">
                        {{ $forms->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('employees.delete-dialog')
@endsection

@section ('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('theme/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-buttons/js/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('theme/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('theme/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('theme/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('theme/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<script>
    $("#report_table").DataTable({
        "searching": false,
        "responsive": true,
        "autoWidth": false,
        "paging": true,
        "pageLength": 50,
        "language": {
            "processing": "Обработка на резултатите...",
            "lengthMenu": "Показване на _MENU_ резултата",
            "zeroRecords": "Няма намерени резултати",
            "info": "Показване на резултати от _START_ до _END_ от общо _TOTAL_",
            "infoEmpty": "Показване на резултати от 0 до 0 от общо 0",
            "infoFiltered": "(филтрирани от общо _MAX_ резултата)",
            "paginate": {
                "first": "Първа",
                "previous": "Предишна",
                "next": "Следваща",
                "last": "Последна"
            }
        },
        "buttons": [
            {
              extend: 'excel',
              title: 'Справка за Служители без годишна оценка в срок'
            },
            {
              extend: 'print',
              title: 'Справка за Служители без годишна оценка в срок',
              footer: true
            },
            { 
              extend: 'pdfHtml5', 
              title: 'Справка за Служители без годишна оценка в срок',
              footer: true 
            }
        ]
    }).buttons().container().appendTo('#report_table_wrapper .col-md-6:eq(1)');
</script>
@endsection
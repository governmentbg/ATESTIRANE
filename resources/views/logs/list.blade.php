@extends('layouts.app')

@section('title') Системен журнал за проследимост на действията @endsection

@section ('breadcrumbs')
<li class="breadcrumb-item active" aria-current="page">
    Системен журнал за проследимост на действията
</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4 mb-3">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="card-title">Филтрирай записите</div>
                        <div class="card-tools"></div>
                    </div>
                    <form method="GET">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="mb-3 col-md-4">
                                    <select class="form-select" name="type" id="type">
                                        <option value="">-- Изберете вид --</option>
                                        <option value="user_logged_in" @selected(Request()->type == 'user_logged_in')>Вход в системата</option>
                                        <option value="employee_created" @selected(Request()->type == 'employee_created')>Създаване на електронно досие за нов служител</option>
                                        <option value="employee_updated" @selected(Request()->type == 'employee_updated')>Редактиране на служител</option>
                                        <option value="employee_deleted" @selected(Request()->type == 'employee_deleted')>Архивиране на служител</option>
                                        <option value="employee_set_role_1" @selected(Request()->type == 'employee_set_role_1')>Задаване на роля Централен администратор</option>
                                        <option value="employee_set_role_2" @selected(Request()->type == 'employee_set_role_2')>Задаване на роля Локален администратор</option>
                                        <option value="employee_set_role_3" @selected(Request()->type == 'employee_set_role_3')>Задаване на роля Оценяващ ръководител</option>
                                        <option value="employee_set_role_4" @selected(Request()->type == 'employee_set_role_4')>Задаване на роля Оценяван</option>
                                        <option value="employee_set_role_5" @selected(Request()->type == 'employee_set_role_5')>Задаване на роля Член на атестационна комисия</option>
                                        <option value="employee_unset_role_1" @selected(Request()->type == 'employee_unset_role_1')>Архивиране на роля Централен администратор</option>
                                        <option value="employee_unset_role_2" @selected(Request()->type == 'employee_unset_role_2')>Архивиране на роля Локален администратор</option>
                                        <option value="employee_unset_role_3" @selected(Request()->type == 'employee_unset_role_3')>Архивиране на роля Оценяващ ръководител</option>
                                        <option value="employee_unset_role_4" @selected(Request()->type == 'employee_unset_role_4')>Архивиране на роля Оценяван</option>
                                        <option value="employee_unset_role_5" @selected(Request()->type == 'employee_unset_role_5')>Архивиране на роля Член на атестационна комисия</option>
                                        <option value="organisation_created" @selected(Request()->type == 'organisation_created')>Добавяне на структура/звено</option>
                                        <option value="organisation_updated" @selected(Request()->type == 'organisation_updated')>Редактиране на структура/звено</option>
                                        <option value="organisation_activated" @selected(Request()->type == 'organisation_activated')>Активиране на структура/звено</option>
                                        <option value="organisation_deactivated" @selected(Request()->type == 'organisation_deactivated')>Деактивиране на структура/звено</option>
                                        <option value="position_created" @selected(Request()->type == 'position_created')>Добавяне на длъжност</option>
                                        <option value="position_updated" @selected(Request()->type == 'position_updated')>Редактиране на длъжност</option>
                                        <option value="position_deleted" @selected(Request()->type == 'position_deleted')>Архивиране на длъжност</option>
                                        <option value="commisions_created" @selected(Request()->type == 'commisions_created')>Създаване на астестационна комисия</option>
                                        <option value="commisions_updated" @selected(Request()->type == 'commisions_updated')>Редактиране на астестационна комисия</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <select class="form-select" name="user_id" id="user_id">
                                        <option value="">-- Изберете служител --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(Request()->user_id == $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 col-md-2">
                                    <div class="input-group">
                                        <input type="text" name="date_range" value="{{ Request()->date_range }}" class="form-control" id="date-range">
                                        <span class="input-group-text">
                                            <i class="bi bi-calendar"></i>
                                        </span>
                                    </div>
                                    <input type="hidden" value="{{ Request()->from_date }}" id="from_date" />
                                    <input type="hidden" value="{{ Request()->to_date }}" id="to_date" />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('logs.list') }}" class="btn btn-secondary me-3">Изчисти филтри</a>
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
                        <div class="card-title">Списък със записи</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th width="1%">Служител</th>
                                    <th></th>
                                    <th width="1%">Дата</th>
                                    <th width="1%">IP</th>
                                    <th width="20%">Browser</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($logs->isNotEmpty())
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td class="text-nowrap">{{ $log->user->name }}</td>
                                    <td>{{ $log->message }}</td>
                                    <td class="text-nowrap">
                                        {{ date('d.m.Y H:i', strtotime($log->created_at)) }}
                                    </td>
                                    <td>{{ $log->ip }}</td>
                                    <td>{{ $log->browser }}</td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="6" class="text-center">Списъкът е празен</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix py-2">
                        {{ $logs->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section ('scripts')
<script>
    var drp = null;
    
    $(document).ready(function() {
        /*$('.datepicker').datepicker({
            language: 'bg'
        });*/
        
        $('#type,#user_id').select2({
            theme: 'bootstrap-5'
        });
        
        // Date range picker - Initialize
        $('#date-range').daterangepicker(
            {
                //startDate: "",
                //endDate: "",
                minDate: false,
                autoUpdateInput: false,
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: " - ",
                    applyLabel: "Избери",
                    cancelLabel: "Откажи",
                    fromLabel: "От",
                    toLabel: "До",
                    customRangeLabel: "Custom",
                    weekLabel: "W",
                    daysOfWeek: [
                        "Не",
                        "По",
                        "Вт",
                        "Ср",
                        "Че",
                        "Пе",
                        "Съ"
                    ],
                    monthNames: [
                        "Януари",
                        "Февруари",
                        "Март",
                        "Април",
                        "Май",
                        "Юни",
                        "Юли",
                        "Август",
                        "Септември",
                        "Октомври",
                        "Ноември",
                        "Декември"
                    ],
                    firstDay: 1
                },
            },
            function(start, end, label) {
                //$('#from_date').val(start.format('YYYY-MM-DD'));
                //$('#to_date').val(end.format('YYYY-MM-DD'));
                
                //console.log(start.format('YYYY-MM-DD'));
                //console.log(end.format('YYYY-MM-DD'));
                
                //console.log(start.diff(end, 'days'));
            }
        );
        
        // Get instance
        drp = $('#date-range').data('daterangepicker');
        
        // Date range picker - Apply
        $('#date-range').on('apply.daterangepicker', function(e, picker){
            var s_day,
                e_day;
            
            //picker.setStartDate(picker.startDate.format('DD.MM.YYYY'));
            
            //picker.setStartDate(picker.endDate.format('DD.MM.YYYY'));
            
            s_day = picker.startDate.format('YYYY-MM-DD');
            e_day = picker.endDate.format('YYYY-MM-DD');
            
            //console.log('apply event');
            
            //console.log(s_day);
            //console.log(e_day);
            $('#date-range').removeClass('is-invalid');
            
            $('#date-range').val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
            
            //$('#from_date').val(s_day);
            //$('#to_date').val(e_day);
        });
        
        // Date range picker - Cancel
        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $('#date-range').val('');
            $('#from_date').val('');
            $('#to_date').val('');
        });
    });
</script>
@endsection
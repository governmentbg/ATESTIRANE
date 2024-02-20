@extends('layouts.auth')

@section('content')
<div class="login-box">
    @if( config('services.server_env.title') )
    <h1 class="text-center">
        <span class="badge {{ config('services.server_env.color') }}">{{ config('services.server_env.title') }}</span>
    </h1>
    @endif
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h1 class="mb-0 text-center">
                Електронно атестиране
            </h1>
        </div>
        <div class="card-body login-card-body">
            <p class="login-box-msg">
                Здравейте, <b>{{ Auth::user()->name }}</b>
                <br/>Моля, изберете от долните опции
            </p>

            <form action="{{ route('choose.role.post') }}" method="post">
                @csrf
                @if( $user->roles->isNotEmpty() )
                    <div class="mb-3">
                        <label class="form-label pt-0 fw-bold">Влез като:</label>
                        @foreach( $user->roles as $role )
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="role_{{ $role->id }}" value="{{ $role->id }}" @checked($loop->first)>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="form-group mb-3" id="year_select">
                        <label class="form-label fw-bold">Година на атестиране:</label>
                        <select class="form-select" name="attestation_id" id="role_3_year">
                            @foreach( $director_commissions as $director_commission )
                                <option value="{{ $director_commission->attestation->id }}">{{ $director_commission->attestation->year }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" name="attestation_id" id="role_4_year">
                            @foreach( $forms as $form )
                                <option value="{{ $form->attestation->id }}">{{ $form->attestation->year }}</option>
                            @endforeach
                        </select>
                        <select class="form-select" name="attestation_id" id="role_5_year">
                            @foreach( $commissions as $commission )
                                <option value="{{ $commission->attestation->id }}">{{ $commission->attestation->year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center mb-3 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Продължи</a>
                    </div>
                @else
                <div class="text-center mb-3 d-grid gap-2">
                    За съжаление, Вашият акаунт не е асоцииран с роля. Моля обърнете се към Администратор.
                    <br/>
                    <a href="#" class="btn btn-danger" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-power-off mr-2"></i> Изход
                    </a>
                </div>
                @endif
            
            </form>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        show_year_selector();
    });
    $('input[name="role"]').on('change', show_year_selector);

    function show_year_selector(){
        var selected_role = $('input[name="role"]:checked').val();
        switch( selected_role ){
            case '1':
            case '2':
                $('[name="attestation_id"]').attr('disabled', true);
                $('#year_select').hide();
                break;
            case '3':
                $('[name="attestation_id"]').attr('disabled', true);
                $('#role_3_year').show();
                $('#role_4_year').hide();
                $('#role_5_year').hide();
                $('#role_3_year').attr('disabled', false);
                $('#year_select').show();
                break;
            case '4':
                $('[name="attestation_id"]').attr('disabled', true);
                $('#role_3_year').hide();
                $('#role_4_year').show();
                $('#role_5_year').hide();
                $('#role_4_year').attr('disabled', false);
                $('#year_select').show();
                break;
            case '5':
                $('[name="attestation_id"]').attr('disabled', true);
                $('#role_3_year').hide();
                $('#role_4_year').hide();
                $('#role_5_year').show();
                $('#role_5_year').attr('disabled', false);
                $('#year_select').show();
                break;
        }
        // if( selected_role == '1' || selected_role == '2' ){
        //     $('[name="attestation_id"]').attr('disabled', true);
        //     $('#year_select').hide();
        // } else {
        //     $('[name="attestation_id"]').attr('disabled', false);
        //     $('#year_select').show();
        // }
    }
</script>
@endsection
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ config('app.name', 'ВСС - Електронно атестиране') }}</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">
    <!--end::Fonts-->
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('theme/plugins/daterangepicker/daterangepicker.css') }}">
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css" integrity="sha256-LWLZPJ7X1jJLI5OG5695qDemW1qQ7lNdbTfQ64ylbUY=" crossorigin="anonymous">
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" integrity="sha256-BicZsQAhkGHIoR//IB2amPN5SrRb3fHB8tFsnqRAwnk=" crossorigin="anonymous">
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}">
    
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <!--end::Required Plugin(AdminLTE)-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    @yield('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link">
                            <b>Атестация: {{ date('d.m.Y', strtotime(session('attestation')->period_from)) }} - 
                            {{ date('d.m.Y', strtotime(session('attestation')->period_to)) }}</b>
                            @if( session('attestation')->status == 'completed' )
                                <span class="badge bg-danger align-text-top">Архив</span>
                            @else
                                <span class="badge bg-success align-text-top">Активна</span>
                            @endif
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-light">
                                @if(Auth::user()->photo_url)
                                <img src="{{ route('display_image', ['filename' => Auth::user()->photo_url]) }}" class="rounded-circle shadow" alt="User Image">
                                @else
                                <h1><i class="nav-icon bi bi-person-bounding-box"></i></h1>
                                @endif
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>{{ session('role') }}</small>
                                </p>
                            </li>
                            <li class="user-footer text-center d-grid">
                                <a href="#" class="btn btn-danger btn-flat btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-power-off mr-2"></i> Изход
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!--end::Header-->
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!--begin::Sidebar Brand-->
            <div class="sidebar-brand">
                <!--begin::Brand Link-->
                <a href="{{ route('dashboard') }}" class="brand-link">
                    <!--begin::Brand Text-->
                    <span class="brand-text fw-light">ВСС | Ел. Атестиране</span>
                    <!--end::Brand Text-->
                </a>
                <!--end::Brand Link-->
            </div>
            <!--end::Sidebar Brand-->
            <!--begin::Sidebar Wrapper-->
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <!--begin::Sidebar Menu-->
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                        @if( in_array( session('role_id'), [1, 2] ) )
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-person-rolodex"></i>
                                <p>
                                    Ел. Досиета
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-4">
                                <li class="nav-item">
                                    <a href="{{ route('employees.list') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Списък</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('employees.show_search') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Добави</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-person-rolodex"></i>
                                <p>
                                    Оценяващи потребители
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-4">
                                <li class="nav-item">
                                    <a href="{{ route('assessors.list') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Списък</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('assessors.show_search') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Добави</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if( session('role_id') == 2 )
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-people-fill"></i>
                                <p>
                                    Атестационни Комисии
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview ms-4">
                                <li class="nav-item">
                                    <a href="{{ route('commissions.list') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Списък</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('commissions.edit') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Добави</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if( in_array( session('role_id'), [4] ) )
                        <li class="nav-item">
                            <a href="{{ route('attestationforms.start') }}" class="nav-link">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Атестационен формуляр</p>
                            </a>
                        </li>
                        @endif

                        @can('view_attestation_form_toolbar')
                        <li class="nav-item">
                            <a href="{{ route('attestationforms.list') }}" class="nav-link">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Атестационни формуляри</p>
                            </a>
                        </li>
                        @endif


                        @if( in_array( session('role_id'), [1, 2] ) )
                        <li class="nav-item">
                            <a href="{{ route('checks.dashboard') }}" class="nav-link">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Справки</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('logs.list') }}" class="nav-link">
                                <i class="nav-icon bi bi-list-check"></i>
                                <p>Системен журнал</p>
                            </a>
                        </li>
                        
                        <li class="nav-header">НАСТРОЙКИ</li>
                        <li class="nav-item">
                            <a href="{{ route('organisations.list') }}" class="nav-link">
                                <i class="nav-icon bi bi-diagram-3"></i>
                                <p>Организационни структури</p>
                            </a>
                        </li>
                        @endif

                        @if( session('role_id') == 1 )
                        <li class="nav-item">
                            <a href="{{ route('positions.types') }}" class="nav-link">
                                <i class="nav-icon bi bi-person-gear"></i>
                                <p>Длъжности</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-shield-check"></i>
                                <p>
                                    Локални администратори
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('local_administrators.list') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Списък</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('local_administrators.add') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Добави</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-shield-fill-check"></i>
                                <p>
                                    Централни администратори
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('central_administrators.list') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Списък</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('central_administrators.add') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Добави</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-clipboard2-data"></i>
                                <p>
                                    Настройки Оценки
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('goals_score.types') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Оценки на задължения/цели</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('competence_score.types') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Оценки на компетентности</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('total_score.types') }}" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Общи оценки</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('attestation.add') }}" class="nav-link">
                                <i class="nav-icon bi bi-journal-plus"></i>
                                <p>Нова атестация</p>
                            </a>
                        </li>
                        @endif
                        <hr/>
                        <li class="nav-header">ИНФОРМАЦИЯ</li>
                        <li class="nav-item">
                            <a href="{{ asset('assets/files/Наръчник_за_потребителите_на_системата_за_електронно_атестиране_на_съдебни_служители.pdf') }}" class="nav-link" target="_blank">
                                <i class="nav-icon bi bi-info-square"></i>
                                <p>Наръчник за потребители</p>
                            </a>
                        </li>
                    </ul>
                    <!--end::Sidebar Menu-->
                </nav>
            </div>
            <!--end::Sidebar Wrapper-->
        </aside>
        <!--end::Sidebar-->
        <!--begin::App Main-->
        <main class="app-main">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('title')</h3>
                        </div>
                        <div class="col-sm-6 no-print">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Начало</a></li>
                                @yield('breadcrumbs')
                            </ol>
                        </div>
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::App Content Header-->
            <!--begin::App Content-->
            <div class="app-content">
            	@yield('content')
            </div>
            <!--end::App Content-->
        </main>
        <footer class="app-footer">
            <strong>
            	ВСС | Електронна атестация
            </strong>
        </footer>
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-NRZchBuHZWSXldqrtAOeCZpucH/1n1ToJ3C8mSK95NU=" crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };

        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>
    <!--end::Script-->
    <!-- daterangepicker -->
    {{--<script src="{{ asset('theme/plugins/moment/moment.min.js') }}"></script>--}}
    <script src="{{ asset('theme/plugins/moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/daterangepicker/daterangepicker.js') }}"></script>
    @yield('scripts')
    <script>
        $.fn.datepicker.dates['bg'] = {
            days: ["Неделя", "Понеделник", "Вторник", "Сряда", "Четвъртък", "Петък", "Събота"],
            daysShort: ["Нед", "Пон", "Вто", "Сря", "Чет", "Пет", "Съб"],
            daysMin: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            months: ["Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември"],
            monthsShort: ["Яну", "Фев", "Мар", "Апр", "Май", "Юни", "Юли", "Авг", "Сеп", "Окт", "Ное", "Дек"],
            today: "Днес",
            clear: "Изчисти",
            format: "dd.mm.yyyy",
            titleFormat: "MM yyyy", /* Leverages same syntax as 'format' */
            weekStart: 1
        };
    </script>
</body><!--end::Body-->

</html>
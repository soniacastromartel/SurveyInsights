<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Estadísticas ICOT</title>
    <script src="{{asset('js/app.js')}}"></script>

    <link rel="shortcut icon" href="{{ asset('assets/img/LogoIcotEncuestas.png') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/font-google-material-icons.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/material-lite.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/material.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/logged.css') }}" />

    <script src="{{asset('js/bootstrap-selectpicker.js')}}"></script>




    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/jquery-ui-1.12.1/jquery-ui.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/MonthPicker.min.css') }}" />
</head>

<body>
    <div class="wrapper ">

        {{-- @auth --}}
        @include('inc.sidebar')
        {{-- @endauth --}}
        <div class="main-panel">
            <!-- <div class="alert alert-info mt-5" role="alert"></div> -->
            @yield('content')
            <script src="{{asset('js/bootstrap-selectpicker.js')}}"></script>
            <script src="{{asset('js/bootstrap.min.js') }}"></script>
            <script src="{{asset('js/jquery-ui-1.12.1/jquery-ui.min.js')}}"></script>
            <script src="{{asset('js/datepicker-es.js') }}"></script>
            <script src="{{asset('js/MonthPicker.min.js')}}"></script>
            <script src="{{asset('js/bootstrap-selectpicker.js')}}"></script>
            <script src="{{asset('js/bootstrap-material-design.min.js')}}"></script>


            <script src="https://www.google.com/jsapi"></script>


            <script type="text/javascript">
                $(function() {
                    function ocultarAlert(e) {
                        $(e).fadeOut('fast');
                    }

                    function timeOutAlert(e) {
                        if (!$(e).hasClass('alert-timeout')) {
                            setTimeout(
                                ocultarAlert.bind(null, e), 3000);
                        }
                    }
                    $('.alert-success').each(function() {
                        timeOutAlert(this);
                    });

                    $('.alert-danger').each(function() {
                        timeOutAlert(this);
                    });
                });

                $.MonthPicker = {
                    VERSION: '3.0.4', // Added in version 2.4;
                    i18n: {
                        year: 'Año',
                        prevYear: 'Año previo',
                        nextYear: 'Año siguiente',
                        next12Years: 'Próximos 12 años',
                        prev12Years: 'Pasados 12 años',
                        nextLabel: 'Anterior',
                        prevLabel: 'Sigiente',
                        buttonText: '',
                        jumpYears: 'Saltar años',
                        backTo: 'Volver',
                        months: ['Ene.', 'Feb.', 'Mar.', 'Abr.', 'May.', 'Jun.', 'Jul.', 'Ago.', 'Sep.', 'Oct.', 'Nov.', 'Dic.']
                    }
                };

                $.datepicker.regional['es'] = {
                    closeText: 'Cerrar',
                    prevText: '<Ant',
                    nextText: 'Sig>',
                    currentText: 'Hoy',
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
                    dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                    weekHeader: 'Sm',
                    dateFormat: 'yy-mm-dd',
                    firstDay: 1,
                    isRTL: false,
                    showMonthAfterYear: false,
                    yearSuffix: ''
                };
            </script>
            {{-- <script src="{{asset('js/bootstrap-material-design.min.js')}}"></script> --}}
            {{--<script src="{{asset('js/material-dashboard.min.js')}}"></script> --}}

        </div>
    </div>
</body>

</html>
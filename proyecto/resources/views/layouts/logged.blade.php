<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Scripts -->
    <script src="{{asset('js/app.js')}}"></script>
    {{-- <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script> --}}
    <script src="{{asset('js/jquery-ui-1.12.1/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery-migrate-3.0.0.min.js')}}"></script>
    <script src="{{asset('js/bootstrap-material-design.min.js')}}"></script>
    <script src="{{asset('js/material-dashboard.min.js')}}"></script>
    <script src="{{asset('js/MonthPicker.min.js')}}"></script>
    
    {{-- <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script> --}}
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <link rel="shortcut icon" href="{{ asset('assets/img/LogoICOT.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <link rel="stylesheet" href="{{ asset('css/material.css') }}">
    <link rel="stylesheet" href="{{ asset('css/material-lite.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/font-google-material-icons.css') }}" />
    {{-- <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/base/jquery-ui.css"/> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('css/jquery-ui-1.12.1/jquery-ui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/MonthPicker.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.dataTables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/logged.css') }}" />

    <title>{{config('app.name')}}</title>
</head>
<body>
    <div class="wrapper ">
    
        {{-- @auth --}}
        @include('inc.sidebar')
        {{-- @endauth --}}
        <div class="main-panel">
            @yield('content')

            <script type="text/javascript" src="{{ asset('js/charts-loader.js')}}"></script>
            {{-- <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script> --}}
             
            <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
            <script src="{{asset('js/bootstrap-selectpicker.js')}}"></script>
            <script src="{{asset('js/bootstrap-autocomplete.min.js')}}"></script>
            <script src="{{asset('js/moment.min.js')}}"></script>
            
            
            <script type="text/javascript">
                $('div.alert').delay(2000).slideUp(300);

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
                    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
                    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
                    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
                    weekHeader: 'Sm',
                    dateFormat: 'yy-mm-dd',
                    firstDay: 1,
                    isRTL: false,
                    showMonthAfterYear: false,
                    yearSuffix: ''
                };
                </script>
      
            {{-- <script src="{{asset('js/bootstrap-material-design.min.js')}}"></script> --}}
            {{-- <script src="{{asset('js/material-dashboard.min.js')}}"></script> --}}
        </div>
    </div>    
    
</body>
</html>
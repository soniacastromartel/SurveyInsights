<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Encuestas</title>
    <script src="{{asset('js/app.js')}}"></script>
    
    <link rel="shortcut icon" href="{{ asset('assets/img/LogoICOT.png') }}">

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/jquery-ui-1.12.1/jquery-ui.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/MonthPicker.min.css') }}" />
</head>
<body>
    <div class="container">
        <div class="alert alert-info mt-5" role="alert">
            @yield('content')
        </div>
    </div>

    <script src="{{asset('js/bootstrap.min.js') }}"></script>
    <script src="{{asset('js/jquery-ui-1.12.1/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/datepicker-es.js') }}"></script>
    <script src="{{asset('js/MonthPicker.min.js')}}"></script>
    <script src="{{asset('js/bootstrap-selectpicker.js')}}"></script>


    <script type="text/javascript">
        $(function () {
            function ocultarAlert(e) {
                $(e).fadeOut('fast');
            }
            function timeOutAlert(e){
                if (!$(e).hasClass('alert-timeout')) {
                    setTimeout(
                        ocultarAlert.bind(null, e )
                    , 3000);
                }
            }
            $('.alert-success').each(function( ) {
                timeOutAlert(this);
            });

            $('.alert-danger').each(function( ) {
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
    </script>

</body>
</html>

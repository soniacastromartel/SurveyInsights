<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8">

    <script src="https://www.google.com/jsapi"></script>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <script src="{{asset('js/bootstrap.min.js') }}"></script>
    <style>

        .pie-chart {
            width: 500px;
            height:500px;
            margin-left: 70px;
        }

        .column-chart {
            width: 500px;
            height:500px;
            margin-left: 40px;
        }

        .bar-chart {
            width: 1000px;
            height:500px;
            margin-left: 40px;
            margin-bottom:80px;
        }

        /* .text-center{
            text-align: center;
        } */

        .googleChartTitle {
            font: bold 20px Arial;
            text-align: center;
            /* margin-left: 70px; */
            /* position: absolute; */
            /* width: 100%; */
            padding:0;
            text-transform: uppercase;
        }

        .pregunta {
            font-size: 14pt;
            font-weight: bold;
            padding-bottom: 15px;
        }

    </style>

</head>

<body>

<div class="d-flex justify-content-between">
    <div>
        <img src="{{ asset('assets/img/LogoICOT.png') }}" width="100px" id="logoIcot" alt="ICOT Icon" /> <br>
    </div>
    <div>
        <img src="{{ asset('assets/img/iso9001.png') }}" width="100px" id="logoISO" alt="ICOT Icon" /> <br>

    </div>
</div>

<br>

<h1 class="text-center font-weight-bold display-1"><strong>{{$title}}</strong></h1>

<div class="px-4">
    <div class="d-flex flex-row text-left mt-2 pregunta">
        ¿Cuando?
    </div>
    <div class="d-flex flex-row text-left  mt-2">
        {{-- Las encuestas se realizaron desde {{$period}} --}}
        Las encuestas se realizaron desde 01/01/2021 al 01/09/2021
    </div>

    <div class="d-flex flex-row text-left mt-2 pregunta">
        ¿A quien se dirigio?
    </div>

    <!-- TODO.... reflejar filtros provincia -->
    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">
        Pacientes de los centros de rehabilitación de
        @if ( $params['province_id'] == 'TODAS' )
            Tenerife y Gran Canaria
        @else
            @if ( $params['province_id'] == 'Las Palmas' )
                Gran Canaria
            @else
                Tenerife
            @endif
        @endif
    </div>

    <div class="d-flex flex-row text-left mt-2 pregunta" >
        ¿Que preguntamos?
    </div>

    <div class="d-flex flex-row text-left  mt-2">
        @foreach ($preguntas as $pregunta)
        {{$pregunta->n_pregunta}} {{$pregunta->question}} <br>
        @endforeach
    </div>


    <div class="d-flex flex-row text-left mt-2 pregunta">
        ¿Cuantas encuestas hicimos?
    </div>
    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">
        <strong> Totales: </strong>  Se realizaron {{$totalProvincia['LasPalmas'] + $totalProvincia['Tenerife'] }} encuestas.
    </div>
    <div class="d-flex flex-row text-left" style="font-size: 12pt;">
        <strong> Por provincia:</strong> Se realizaron
        @if ( $params['province_id'] == 'TODAS' )
                {{$totalProvincia['Tenerife']}} en Tenerife y {{$totalProvincia['LasPalmas']}} en Gran Canaria
        @else
            @if ( $params['province_id'] == 'Las Palmas' )
                {{$totalProvincia['LasPalmas']}} en Gran Canaria
            @else
                {{$totalProvincia['Tenerife']}} en Tenerife
            @endif
        @endif
    </div>


    <div class="d-flex justify-content-between" style="margin-top:50px">
        <div class="googleChartTitle col-md-4">
            <span style="margin-top:30px;" > Sexo</span>
            <div id="chartSexo" class="pie-chart"></div>
        </div>
        <div class="googleChartTitle col-md-4">
            <span style="margin-top:30px"> Edad</span>
            <div id="chartEdad" class="column-chart"></div>
        </div>
    </div>





    <div class="d-flex flex-row text-left" style="margin-top:350px; ">
        <div class="googleChartTitle">
            <div id ="centres_lpa" class="bar-chart"></div>
        </div>
    </div>



    <div class="d-flex flex-row text-left mt-2 pregunta" style="margin-top:100px">
        Resultados
    </div>
    <div class="d-flex flex-row text-left mt-2 pregunta">
        PORCENTAJE DE PACIENTES QUE HAN RESPONDIDO BUENO Y MUY BUENO
    </div>

    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">

        @foreach ($preguntas as $pregunta)
            @foreach ($porcentPreg as $ppreg)

                @if ( $loop->iteration == $pregunta->pregunta )
                    PREGUNTA {{$pregunta->pregunta}} : {{$ppreg}}%<br>
                    {{$pregunta->question}}  <br>
                @endif
            @endforeach
        @endforeach
    </div>
</div>
<script type="text/javascript">

    window.onload = function() {

        google.load("visualization", "1.1", {

            packages: ["corechart"],

            callback: 'drawChart'

        });
    };

    function drawChart() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Pizza');
        data.addColumn('number', 'Populartiy');
        data.addRows([
            ['Hombre', {{$totalSexo['Hombre']}}],
            ['Mujer', {{$totalSexo['Mujer']}}]
        ]);


        // TODO ... POSITION TITTLE
        var options = {
            titleTextStyle: {bold:true, fontSize:20},
            sliceVisibilityThreshold: .2,
            legend: {position:'bottom'},
            chartArea: {left:"5%"},
        };

        var sexoChart = new google.visualization.PieChart(document.getElementById('chartSexo'));
        sexoChart.draw(data, options);

        var edadData = [];
        var row = ["Edad", "", { role: "style" }, { role: 'annotation' }  ];
        edadData.push(row);
        @foreach ($totalEdad as $te)
            var row = ["{{$te->edad}}", {{$te->total}}, "#4f81bd", {{$te->total}}];
            edadData.push(row);
        @endforeach


        var data = google.visualization.arrayToDataTable(edadData);

        var options = {
            // bar: {groupWidth: "95%"},
            legend: {position:'bottom'},
            vAxis: {
                title: 'NÚMERO DE ENCUESTADOS', minValue:0
            },
            hAxis: {
                title: 'EDAD DE ENCUESTADOS',
                textStyle : {
                    fontSize : 10,
                    minTextSpacing : 10
                },
            }
        };

        var edadChart = new google.visualization.ColumnChart(document.getElementById('chartEdad'));
        edadChart.draw(data, options);


        var centreData = [];
        var row = ['Centro', '', {role:'annotation'}];
        centreData.push(row);


        @foreach ($totalCentreLpa as $c)
            var row = ["{{$c->centro}}", {{$c->total}}, {{$c->total}}];
            centreData.push(row);
        @endforeach

        var data = google.visualization.arrayToDataTable(centreData);
        var options = {title: 'CENTROS ICOT GRAN CANARIA'
                        ,titleTextStyle: {
                            fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                        }
                      ,legend: 'none'
                      ,vAxis: {
                        textStyle : {
							fontSize : 10,
                            minTextSpacing : 10
						},
                      }
        };

        // Instantiate and draw the chart.
        var chart = new google.visualization.BarChart(document.getElementById('centres_lpa'));
        chart.draw(data, options);

        var centreData = [];
        var row = ['Centro', '', {role:'annotation'}];
        centreData.push(row);


        @foreach ($totalCentreTfe as $c)
            var row = ["{{$c->centro}}", {{$c->total}}, {{$c->total}}];
            centreData.push(row);
        @endforeach

        var data = google.visualization.arrayToDataTable(centreData);
        var options = {title: 'CENTROS ICOT TENERIFE'
                        ,titleTextStyle: {
                            fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                        }
                        ,legend: 'none'
                        ,vAxis: {
                            textStyle : {
                                fontSize : 10
                            },
                        }
        };

        // Instantiate and draw the chart.
        var chart = new google.visualization.BarChart(document.getElementById('centres_tfe'));
        chart.draw(data, options);



    }

</script>

</body>

</html>

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
        .googleChartTitle {
            font: bold 20px Arial;
            /* text-align: center; */
            padding-left:20px;
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

<div class="d-flex justify-content-between px-4 py-4">
    <div>
        <img src="{{ asset('assets/img/LogoICOT.png') }}" width="150px" id="logoIcot" alt="ICOT Icon" /> <br>
    </div>
    <div>
        <img src="{{ asset('assets/img/iso9001.png') }}" width="100px" id="logoISO" alt="ICOT Icon" /> <br>
    </div>
</div>

<br>

<h1 class="text-center font-weight-bold" style="font-size: 24pt; font-weight: bold;">{{$title}}</h1>

<div class="px-4">
    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿Cuando?
    </div>
    <div class="d-flex flex-row text-left  mt-2">
        Las encuestas se realizaron desde {{$period}}
    </div>

    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿A quien se dirigio?
    </div>

    <!-- TODO.... reflejar filtros provincia -->
    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">
        Pacientes
        @if ( $params['patient_id'] != '-1' )
            de {{ $params['patient_id'] }}
        @endif
        de los centros de rehabilitación de
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

    <div class="d-flex flex-row text-left mt-4 pregunta" >
        ¿Que preguntamos?
    </div>

    <div class="d-flex flex-row text-left  mt-2">
        @foreach ($preguntas as $pregunta)
        {{$pregunta->n_pregunta}} {{$pregunta->question}} <br>
        @endforeach
    </div>


    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿Cuantas encuestas hicimos?
    </div>
    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">
        <strong> Totales: </strong>  Se realizaron
            {{ $totalEncuestados }}
            encuestas.
    </div>
    <div class="d-flex flex-row text-left" style="font-size: 12pt;">
        <strong> Por provincia:</strong> Se realizaron
        @if ( $params['province_id'] == 'TODAS' )
            @if ( isset($totalProvincia['Provincia de Tenerife']) )
                {{$totalProvincia['Provincia de Tenerife']}} en Tenerife.
            @endif
            @if ( isset($totalProvincia['Provincia de Las Palmas']) )
                 {{$totalProvincia['Provincia de Las Palmas']}} en Gran Canaria.
            @endif
        @else
            @if ( $params['province_id'] == 'Las Palmas' )
                {{$totalProvincia['Provincia de Las Palmas']}} en Gran Canaria.
            @else
                {{$totalProvincia['Provincia de Tenerife']}} en Tenerife.
            @endif
        @endif
    </div>
    <div class="d-flex justify-content-between" style="margin-top:150px">
        <div class="googleChartTitle col-md-4 text-center">
            <span> Sexo</span>
            <div id="chartSexo" class="pie-chart"></div>
        </div>
        <div class="googleChartTitle col-md-4 text-center">
            <span> Edad</span>
            <div id="chartEdad" class="column-chart"></div>
        </div>
    </div>
    <br>
    @if ( $params['province_id'] == 'TODAS')
        <div class="d-flex flex-row text-left" style="margin-top:1200px;top:100px ">
            <div class="googleChartTitle">
                <div id ="centres_lpa" class="bar-chart"></div>
            </div>
        </div>
        <div class="d-flex flex-row text-left">
            <div class="googleChartTitle">
                <div id ="centres_tfe" class="bar-chart"></div>
            </div>
        </div>
    @else
        @if ( $params['province_id'] == 'Las Palmas')
            <div class="d-flex flex-row text-left" style="margin-top:600px; ">
                <div class="googleChartTitle">
                    <div id ="centres_lpa" class="bar-chart"></div>
                </div>
            </div>
        @else
            <div class="d-flex flex-row text-left" style="margin-top:600px; ">
                <div class="googleChartTitle">
                    <div id ="centres_tfe" class="bar-chart"></div>
                </div>
            </div>
        @endif
    @endif

    @if ( $params['patient_id'] != '-1' )
    <div class="d-flex flex-row text-left mt-2 text-center pregunta" style="margin-top:100px"> 
    {{ $params['patient_id'] }}
    </div>     
    @endif

    <div class="d-flex flex-row text-left mt-2 text-center pregunta" style="margin-top:100px">
        RESULTADOS
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
        google.charts.load('current', {'packages':['corechart']});

        // google.charts.load("visualization", "1.1", {

        //     packages: ["corechart"],

        //     callback: 'drawChart'

        // });
        var provincia = "{{$params['province_id'] }}";
        google.charts.setOnLoadCallback(drawSexChar);
        google.charts.setOnLoadCallback(drawAgeChar);
        if (provincia  == 'Las Palmas') {
            google.charts.setOnLoadCallback(drawCentreLPAChart);
        }
        if (provincia  == 'Tenerife') {
            google.charts.setOnLoadCallback(drawCentreTFEChart);
        }
        if (provincia  == 'TODAS') {
            google.charts.setOnLoadCallback(drawCentreLPAChart);
            google.charts.setOnLoadCallback(drawCentreTFEChart);
        }
    };

    function drawSexChar() {
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
    }

    function drawAgeChar() {
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
    }

    function drawCentreLPAChart() {
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
                      },
                      chartArea : {
                        top: '35%'
                      }
        };

        // Instantiate and draw the chart.
        var chart = new google.visualization.BarChart(document.getElementById('centres_lpa'));
        chart.draw(data, options);
    }

    function drawCentreTFEChart() {
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
                        },
                      chartArea : {
                        top: '35%'
                      }
        };

        // Instantiate and draw the chart.
        var chart = new google.visualization.BarChart(document.getElementById('centres_tfe'));
        chart.draw(data, options);
    }

</script>

</body>

</html>

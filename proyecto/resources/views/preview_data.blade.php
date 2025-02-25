<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <script src="http://www.google.com/jsapi"></script>

    <!-- <script type="text/javascript" src="https://www.google.com/jsapi"></script> -->
    {{-- <script src="https://www.gstatic.com/charts/loader.js"></script> --}}

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <style>
        .pie-chart {
            width: 600px;
            height: 600px;
        }

        .column-chart {
            width: 600px;
            height: 500px;
        }

        .bar-chart {
            width: 1200px;
            height: 600px;
            /* margin: 0 auto; */
        }

        .googleChartTitle {
            font: bold 20px Arial;
            text-align: center;
            /* padding-left:20px; */
            text-transform: uppercase;
            margin-bottom: 0px;
            margin-top: 50px;
        }

        .pregunta {
            font-size: 14pt;
            font-weight: bold;
            padding-bottom: 15px;
        }

        #chartSexo {
            float: left;
        }

        #chartEdad {
            float: left;
        }

        body {
            margin: 0;
            padding: 0;
            /* width:      21cm;
        height:     29.7cm; */
        }

        /* Printable area */
        #print-area {
            position: relative;
            top: 1cm;
            left: 1cm;
            width: 19cm;
            height: 27.6cm;
            font-size: 10px;
            font-family: Arial;
        }

        .ir-arriba {
            display: none;
            padding: 20px;
            background: #bc012e;
            font-size: 30px;
            color: #fff;
            cursor: pointer;
            position: fixed;
            bottom: 20px;
            right: 20px;
        }

        h1 {
            font-size: 24pt;
            font-weight: bold;
            text-align: center;
            background-color: #bc012e;
            color: white;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        h2 {
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            background-color: #C0C0C0;
            color: black;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            margin-bottom: 15px;
        }

        .title {
            font-size: 22pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            background-color: #C0C0C0;
            color: black;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            margin-bottom: 15px;
        }

        /* Salto de página */
        .page_break {
            page-break-before: always;
        }

        .centerLabel {
            position: absolute;
            left: 295px;
            right: 1px;
            top: 2px;
            width: 400px;
            line-height: 400px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
        }

        .donutCell {
            position: relative;
        }
    </style>
</head>

<body body onload="init();">
    <br>
    <h1>{{ $title }}</h1>

    <div class="">
        <div class="title">
            @if ($params['company'] != '-1' && $params['company_name'] != 'Otros')
                <strong>{{ $params['company_name'] }} </strong>
            @elseif ($params['company_name'] == 'Otros')
                <strong>OTRAS COMPANÍAS</strong>
            @endif
        </div>
        <hr>

        <div class="d-flex pregunta mt-4 flex-row text-left">
            ¿Cuándo?
        </div>
        <div class="d-flex mt-2 flex-row text-left">
            Las encuestas se realizaron desde {{ $period }}
        </div>

        <div class="d-flex pregunta mt-4 flex-row text-left">
            ¿A quién se dirigio?
        </div>

        <!-- TODO.... reflejar filtros provincia -->
        <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;">
            Pacientes
            @if ($params['patient_id'] != '-1')
                de {{ $params['patient_name'] }}
                @if ($params['company'] != '-1' && $params['company_name'] != 'Otros')
                    de {{ $params['company_name'] }}
                @endif
                @if ($params['company_name'] == 'Otros')
                    de Otras Compañías
                @endif
            @endif

            @if ($params['centre'] != -1)
                de {{ $params['centre_name'] }}
            @else
                de los Centros de Rehabilitación de
            @endif
            @if ($params['province_name'] == 'TODAS')
                Tenerife y Gran Canaria
            @else
                @if ($params['province_name'] == 'Provincia de Las Palmas')
                    Gran Canaria
                @else
                    Tenerife
                @endif
            @endif
        </div>

        <div class="d-flex pregunta mt-4 flex-row text-left">
            ¿Qué preguntamos?
        </div>

        <div class="d-flex mt-2 flex-row text-left">
            <ul>
                @foreach ($preguntas as $i => $pregunta)
                    <li><strong>PREGUNTA {{ $i + 1 }}: </strong> {{ $pregunta->question }}</li>
                @endforeach
            </ul>
        </div>

        <div class="d-flex pregunta mt-4 flex-row text-left">
            ¿Cuántas encuestas hicimos?
        </div>
        <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;">
            <strong> Totales: </strong> Se realizaron
            {{ $totalEncuestados }}
            encuestas.
        </div>
        <div class="d-flex flex-row text-left" style="font-size: 12pt;">
            <strong> Por provincia: </strong> Se realizaron
            @if ($params['province_name'] == 'TODAS')
                @if (isset($totalProvincia['Provincia de Tenerife']))
                    {{ $totalProvincia['Provincia de Tenerife'] }} en Tenerife.
                @endif
                @if (isset($totalProvincia['Provincia de Las Palmas']))
                    {{ $totalProvincia['Provincia de Las Palmas'] }} en Gran Canaria.
                @endif
            @else
                @if ($params['province_name'] == 'Provincia de Las Palmas')
                    {{ $totalProvincia['Provincia de Las Palmas'] }} en Gran Canaria.
                @else
                    {{ $totalProvincia['Provincia de Tenerife'] }} en Tenerife.
                @endif
            @endif
        </div>

        <!-- encuestas por mes -->
        <div class="googleChartTitle">
            <div id="chartMonths" class="column-chart" style="margin-left: 110px;margin-top:50px;"></div>
        </div>

        <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
            <h2>DATOS DE LA MUESTRA</h2>
            <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran los resultados obtenidos
                de la muestra en cuanto a las variables edad, género y experiencia en los centros del grupo. </div>
        </div>

        <div class="googleChartTitle">
            <div id="chartSexo" class="pie-chart"></div>
            <div id="chartEdad" class="column-chart"></div>
        </div>

        <div class="googleChartTitle">
            <div id="chartExperiencia" class="pie-chart" style="margin:0 auto;margin-top: 700px;margin-bottom: 50px;">
            </div>
        </div>
        <br>

        <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
            <h2>RESULTADOS POR SERVICIOS REQUERIDOS</h2>
            <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran los resultados obtenidos
                en cuanto a servicios solicitados. </div>
        </div>

        @if ($params['centre'] != '-1')
            <div class="googleChartTitle">
                <strong>{{ $params['centre_name'] }} </strong>
            </div>
        @endif

        @if ($params['province_name'] == 'TODAS')
            @if (isset($totalProvincia['Provincia de Las Palmas']))
                <div>
                    <div class="googleChartTitle">
                        <div id="services_lpa" class="bar-chart"></div>
                    </div>
                </div>
            @endif
            @if (isset($totalProvincia['Provincia de Tenerife']))
                <div>
                    <div class="googleChartTitle">
                        <div id ="services_tfe" class="bar-chart"></div>
                    </div>
                </div>
            @endif
        @elseif ($params['province_name'] == 'Provincia de Las Palmas')
            @if (isset($totalProvincia['Provincia de Las Palmas']))
                <div>
                    <div class="googleChartTitle">
                        <div id="services_lpa" class="bar-chart"></div>
                    </div>
                </div>
            @endif
        @endif

        @if ($params['province_name'] == 'Provincia de Tenerife')
            @if (isset($totalProvincia['Provincia de Tenerife']))
                <div>
                    <div class="googleChartTitle">
                        <div id ="services_tfe" class="bar-chart"></div>
                    </div>
                </div>
            @endif
        @endif

        @if ($params['province_name'] == 'TODAS')
            <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
            @else
                <div style="margin-top: 50px;margin-bottom: 50px;">
        @endif

        @if ($params['centre_name'] == 'TODOS')
            <h2>RESULTADOS POR CENTROS</h2>
            <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran los resultados obtenidos
                por centro. </div>
    </div>
    @endif

    @if ($params['province_name'] == 'TODAS' && $params['centre_name'] == 'TODOS')
        @if (isset($totalProvincia['Provincia de Las Palmas']))
            <div>
                <div class="googleChartTitle">
                    <div id ="centres_lpa" class="bar-chart"></div>
                </div>
            </div>
            <div>
                <div class="googleChartTitle">
                    <div id ="satisfaction_lpa" class="bar-chart"></div>
                </div>
            </div>
        @endif
        @if (isset($totalProvincia['Provincia de Tenerife']))
            <div>
                <div class="googleChartTitle">
                    <div id ="centres_tfe" class="bar-chart"></div>
                </div>
            </div>
            <div>
                <div class="googleChartTitle">
                    <div id ="satisfaction_tfe" class="bar-chart"></div>
                </div>
            </div>
        @endif
    @elseif ($params['province_name'] == 'Provincia de Las Palmas' && $params['centre_name'] == 'TODOS')
        <div>
            <div class="googleChartTitle">
                <div id ="centres_lpa" class="bar-chart"></div>
            </div>
        </div>
        <div>
            <div class="googleChartTitle">
                <div id ="satisfaction_lpa" class="bar-chart"></div>
            </div>
        </div>
    @elseif ($params['province_name'] == 'Provincia de Tenerife' && $params['centre_name'] == 'TODOS')
        <div>
            <div class="googleChartTitle">
                <div id ="centres_tfe" class="bar-chart"></div>
            </div>
        </div>
        <div>
            <div class="googleChartTitle">
                <div id ="satisfaction_tfe" class="bar-chart"></div>
            </div>
        </div>
    @endif

    @unless ($params['patient_id'] != '-1')
        <div class="d-flex pregunta mt-2 flex-row text-left text-center" style="margin-top:100px"></div>
        <div style="margin-top: 60px;margin-bottom: 60px;" class="page_break">
            <h2>RESULTADOS POR PLANES DE ASISTENCIA</h2>
            <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran los resultados obtenidos por
                plan de asistencia. </div>
            <div id="plansChart" class="googleChartTitle" style="margin-left: 100px;"> </div>
        </div>
    @endunless

    @if ($params['patient_id'] != '-1' && $params['company'] != '0')
        @if ($params['patient_id'] == 'T1' || $params['patient_id'] == 'T2' || $params['patient_id'] == 'T3')
            @if ($params['company'] == '-1')
                @if (isset($totalCompanies))
                    <div class="d-flex pregunta mt-2 flex-row text-left text-center" style="margin-top:100px"></div>
                    <div style="margin-top: 60px;margin-bottom: 60px;" class="page_break">
                        <h2>RESULTADOS POR COMPAÑÍA</h2>
                        <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran las compañías
                            con mayor número de pacientes encuestados. </div>
                        <div id="companiesChart" class="googleChartTitle bar-chart" style="margin-left: 100px;"></div>
                @endif
            @endif
        @endif
    @endif

    @if ($params['company'] == '0')
        @if (isset($totalOtherCompanies))
            <div class="d-flex pregunta mt-2 flex-row text-left text-center" style="margin-top:100px"></div>
            <div style="margin-top: 60px;margin-bottom: 60px;" class="page_break">
                <h2>RESULTADOS PARA OTRAS COMPAÑÍAS</h2>
                <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran las compañías de los
                    pacientes que han seleccionado la opción 'otros'. </div>
                <div id="otherCompaniesChart" class="googleChartTitle bar-chart" style="margin-left: 100px;"></div>
        @endif
        </div>
    @endif

    <div style="margin-top: 50px;margin-bottom: 50px;">
        <h2>NET PROMOTER SCORE</h2>
        <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> Se muestran los resultados del Net
            Promoter Score. </div>
    </div>
    <div class="donutCell">
        <div id ="question5" class="bar-chart" style="margin-left: 100px;"></div>
        <div class="centerLabel"><strong>NPS = {{ $totalSatisfaccion['nps'] }}</strong></div>
    </div>

    <div style="margin-top: 60px;margin-bottom: 30px;" class="page_break">
        <h2>RESULTADOS DE SATISFACCIÓN</h2>
        <div class="d-flex mt-2 flex-row text-left" style="font-size: 12pt;"> </div>

    </div>
    <div class="d-flex pregunta mt-2 flex-row text-center">
        PORCENTAJE DE PACIENTES QUE HAN RESPONDIDO BUENO Y MUY BUENO
    </div>

    <div class="margin-bottom: 0; padding-bottom: 0;" style="font-size: 12pt;">

        @foreach ($preguntas as $i => $pregunta)
            @foreach ($porcentPreg as $ppreg)
                @if ($loop->iteration == $i + 1 && $loop->iteration < 6)
                    <strong>Pregunta {{ $i + 1 }} => {{ $ppreg }}% </strong><br>
                    {{ $pregunta->question }} <br>
                    <br>
                    @if ($loop->iteration == 5)
                    @endif
                @endif
            @endforeach
        @endforeach

        <div id="chartSatisfaccion" class="googleChartTitle" style="margin-left: 320px;margin-top: 100px;"></div>
    </div>
    </div>

    <script type="text/javascript">
        var GC = '#bc012e';
        var TF = '#1A73E8';
        var colors = ['#AAFFAA', '#BBFFAA', '#CCFFAA', '#DDFFAA', '#EEFFAA', '#FFFFAA', '#FFDDAA', '#FFCCAA', '#FFBBAA',
            '#FFAAAA'
        ];
        var colours = ['#FF7F50', '#FFD700', '#00FFFF', '#FFA500', '#EE82EE'];
        var mycolors = ['#C9C365', '#21CCAD', '#FAC559', '#F86569', '#8197AF', '#B1A9B1'];
        var colorGradient = [
            '#BC012E', 
            '#D11C3A',
            '#E02D49',
            '#F03E57',
            '#FF4E65',
            '#FF677D',
            '#FF7993',
            '#FF8BAA',
            '#FF9CC1',
            '#FFAED7',
            '#FFC1EC',
        ];

        var blueGradient = [
            '#1A73E8', 
            '#3385FF',
            '#4D97FF',
            '#66AAFF',
            '#80BCFF',
            '#99CEFF',
            '#B3E1FF',
            '#CCEFFF',
            '#E0F5FF',
            '#F0F9FF',
        ];
        $totalCompanies = '';
        console.log(params['company']);
        init();


        // window.onload = function() {
        function init() {
            google.load('visualization', '44', {
                packages: ['corechart']
            });

            var interval = setInterval(function() {
                if (google.visualization !== undefined && google.visualization.DataTable !== undefined && google
                    .visualization.PieChart !== undefined) {

                    clearInterval(interval);
                    window.status = 'ready';

                    var provincia = "{{ $params['province_name'] }}";
                    var patient = "{{ $params['patient_id'] }}";
                    var encuesta = "{{ $params['survey_id'] }}";
                    var company = "{{ $params['company'] }}";
                    var centre = "{{ $params['centre_name'] }}";
                    var centre_id = "{{ $params['centre'] }}";

                    drawMonthsChar()
                    drawSexChar();
                    drawAgeChar();

                    if (provincia == 'Provincia de Las Palmas' || provincia == 'TODAS') {
                        @if (isset($totalProvincia['Provincia de Las Palmas']))
                            if (centre == 'TODOS') {
                                drawCentreLPAChart();
                                drawLPASatisfactionChart();
                                drawServicesCharLPA();

                            }
                            drawServicesCharLPA();
                        @endif
                    }
                    if (provincia == 'Provincia de Tenerife' || provincia == 'TODAS') {
                        @if (isset($totalProvincia['Provincia de Tenerife']))
                            if (centre == 'TODOS') {
                                drawCentreTFEChart();
                                drawTFESatisfactionChart();
                            }
                            drawServicesCharTF();

                        @endif
                    }

                    drawExperienceChart();
                    drawQuestionsChart();
                    drawNPSChart();

                    if (patient == 'T1' || patient == 'T2' || patient == 'T3') {
                        @if ($params['company'] == '-1')
                            drawCompaniesTotal();
                        @endif
                        @if ($params['company'] == '0')
                            drawOtherCompanies();
                        @endif
                    } else if ((patient == '-1' && company == '-1') || (patient == '-1' && company != '0')) {
                        drawCarePlansChart();
                    } else if (company == '0') {
                        drawOtherCompanies();

                    }

                    // drawCompaniesTotal();
                    // drawCarePlansChart();

                    window.status = 'ready';
                }
            }, 100);
        }
        // var provincia = "{{ $params['province_id'] }}";
        // google.charts.setOnLoadCallback(drawSexChar);
        // google.charts.setOnLoadCallback(drawAgeChar);
        // if (provincia  == 'Las Palmas') {
        //     google.charts.setOnLoadCallback(drawCentreLPAChart);
        // }
        // if (provincia  == 'Tenerife') {
        //     google.charts.setOnLoadCallback(drawCentreTFEChart);
        // }
        // if (provincia  == 'TODAS') {
        //     google.charts.setOnLoadCallback(drawCentreLPAChart);
        //     google.charts.setOnLoadCallback(drawCentreTFEChart);
        // }
        //};

        $(document).ready(function() {

            $('.ir-arriba').click(function() {
                $('body, html').animate({
                    scrollTop: '0px'
                }, 300);
            });

            $(window).scroll(function() {
                if ($(this).scrollTop() > 0) {
                    $('.ir-arriba').slideDown(300);
                } else {
                    $('.ir-arriba').slideUp(300);
                }
            });

        });



        /**
         * Gráfica de Género
         */
        function drawSexChar() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Pizza');
            data.addColumn('number', 'Populartiy');
            @foreach ($totalSexo as $i => $te)
                data.addRows([
                    ['{{ $te->sexo }}', {{ $te->total }}],
                ]);
            @endforeach

            var options = {
                title: 'GÉNERO',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20
                },
                sliceVisibilityThreshold: .2,
                legend: {
                    position: 'bottom'
                },
                chartArea: {
                    left: "5%"
                },
                is3D: true,
                colors: ['#bc012e', '#1A73E8']
            };

            var sexoChart = new google.visualization.PieChart(document.getElementById('chartSexo'));
            sexoChart.draw(data, options);
        }

        /**
         * Gráfica de Antigüedad de pacientes
         */
        function drawExperienceChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Pizza');
            data.addColumn('number', 'Populartiy');
            data.addRows([
                ['1ª Vez', {{ $totalExp['1º vez'] }}],
                ['Ya ha estado', {{ $totalExp['Ya ha estado'] }}]
            ]);

            var options = {
                title: 'EXPERIENCIA',
                pieHole: 0.46,
                width: 600,
                height: 600,
                titleTextStyle: {
                    bold: true,
                    fontSize: 20
                },
                // sliceVisibilityThreshold: .2,
                legend: {
                    position: 'bottom'
                },
                chartArea: {
                    left: "5%"
                },
                //  is3D: true, 
                colors: [GC, TF]

            };
            var experienceChart = new google.visualization.PieChart(document.getElementById('chartExperiencia'));
            experienceChart.draw(data, options);
        }

        /**
         * Gráfica de Encuestas/Mes
         */
        function drawMonthsChar() {
            var monthsData = [];
            var row = ["Year", "TOTAL", {
                role: "style"
            }, {
                role: 'annotation'
            }];
            monthsData.push(row);
            @foreach ($totalEncuestas as $te)
                var row = ["{{ $te->mes }}", {{ $te->total }}, "#1A73E8", {{ $te->total }}];
                monthsData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(monthsData);
            var options = {
                title: 'ENCUESTAS POR MES',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20,
                    margin: 15
                },
                width: 680,
                height: 300,
                // bar: {groupWidth: "95%"},
                legend: {
                    position: 'bottom'
                },
                vAxis: {
                    title: 'NÚMERO DE ENCUESTAS',
                    minValue: 0,
                    format: '0'

                },
                hAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                }
            };

            var monthsChart = new google.visualization.LineChart(document.getElementById('chartMonths'));
            monthsChart.draw(data, options);
        }

        /**
         * Gráfica de Edades pacientes
         */
        function drawAgeChar() {
            var edadData = [];
            var row = ["Year", "EDAD", {
                role: "style"
            }, {
                role: 'annotation'
            }];
            edadData.push(row);
            @foreach ($totalEdad as $te)
                var row = ["{{ $te->edad }}", {{ $te->total }}, "#1A73E8", {{ $te->total }}];
                edadData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(edadData);

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);

            var options = {
                title: 'EDAD',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20,
                    margin: 15
                },
                // bar: {groupWidth: "95%"},
                legend: {
                    position: 'bottom'
                },
                vAxis: {
                    title: 'NÚMERO DE ENCUESTADOS',
                    minValue: 0,
                    format: '0'

                },
                hAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                }
            };

            var edadChart = new google.visualization.ColumnChart(document.getElementById('chartEdad'));
            edadChart.draw(view, options);
        }

        /**
         * Gráfica de Servicios LPA
         */
        function drawServicesCharLPA() {
            var serviceData = [];
            var row = ["Servicio", "SERVICIO", {
                role: "style"
            }, {
                role: 'annotation'
            }];
            serviceData.push(row);
            @foreach ($totalServiciosLPA as $ts)
                var row = [
                    "{{ $ts->servicio }}",
                    {{ $ts->total }},
                    "#bc012e",
                    {{ $ts->total }}
                ];
                serviceData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(serviceData);
            data.sort([{
                column: 1,
                desc: true
            }]);

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);

            var options = {
                title: 'SERVICIOS GRAN CANARIA',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri', // i.e. 'Times New Roman'
                    fontSize: 20, // 12, 18 whatever you want (don't specify px)
                    bold: true, // true or false
                    italic: false // true of false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'
                },
                hAxis: {
                    // format: '0'

                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "70%"
                },
                colors: [GC]
            };

            var serviceChart = new google.visualization.BarChart(document.getElementById('services_lpa'));
            serviceChart.draw(view, options);
        }



        /**
         * Gráfica de Servicios TF
         */
        function drawServicesCharTF() {
            var serviceData = [];
            var row = ["Servicio", "SERVICIOS", {
                role: 'annotation'
            }];
            serviceData.push(row);
            @foreach ($totalServiciosTF as $ts)
                var row = [
                    "{{ $ts->servicio }}",
                    {{ $ts->total }},
                    {{ $ts->total }}
                ];
                serviceData.push(row);
            @endforeach


            var data = google.visualization.arrayToDataTable(serviceData);
            data.sort([{
                column: 1,
                desc: true
            }]);

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);

            var options = {
                title: 'SERVICIOS TENERIFE',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri', // i.e. 'Times New Roman'
                    fontSize: 20, // 12, 18 whatever you want (don't specify px)
                    bold: true, // true or false
                    italic: false // true of false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'


                },
                hAxis: {
                    // format: '0'

                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "70%"
                },
                colors: [TF]

            };
            var serviceChart = new google.visualization.BarChart(document.getElementById('services_tfe'));
            serviceChart.draw(view, options);
        }

        /**
         * Gráfica de Encuestados x Centros LPA
         */
        function drawCentreLPAChart() {
            var centreData = [];
            var maxRange = 0;
            var row = ['Centro', '', {
                role: 'annotation'
            }];
            centreData.push(row);


            @foreach ($totalCentreLpa as $i => $c)
                var row = ["{{ $c->centro }}", {{ $c->total }}, {{ $c->total }}];
                centreData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(centreData);
            data.sort([{
                column: 1,
                desc: true
            }]);

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);

            var options = {
                title: 'TOTAL ENCUESTADOS - GRAN CANARIA',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri', // i.e. 'Times New Roman'
                    fontSize: 20, // 12, 18 whatever you want (don't specify px)
                    bold: true, // true or false
                    italic: false // true of false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'

                },
                hAxis: {


                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "80%"
                },
                colors: [GC]

            };

            // Instantiate and draw the chart.
            var chart = new google.visualization.BarChart(document.getElementById('centres_lpa'));
            chart.draw(view, options);
        }

        /**
         * Gráfica de Satisfacción x Centros LPA
         */
        function drawLPASatisfactionChart() {
            var centreData = [];
            var maxRange = 0;
            var row = ['Centro', '', {
                role: "style"
            }, {
                role: 'annotation'
            }];
            centreData.push(row);


            @foreach ($totalCentreLpa as $i => $c)
                var row = ["{{ $c->centro }}", {{ $c->satisfaction }}, colorGradient[{{ $i }}],
                    {{ $c->satisfaction }} + '%'
                ];
                centreData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(centreData);
            data.sort([{
                column: 1,
                desc: true
            }]);

            var options = {
                title: 'SATISFACCIÓN POR CENTRO - GRAN CANARIA',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri',
                    fontSize: 20,
                    bold: true,
                    italic: false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'

                },
                hAxis: {


                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "80%"
                },
                bar: {
                    groupWidth: '99%'
                },
                colors: mycolors

            };

            var chart = new google.visualization.BarChart(document.getElementById('satisfaction_lpa'));
            chart.draw(data, options);
        }

        /**
         * Gráfica de Centros TF
         */
        function drawCentreTFEChart() {
            var centreData = [];
            var row = ['Centro', '', {
                role: 'annotation'
            }];
            centreData.push(row);


            @foreach ($totalCentreTfe as $c)
                var row = ["{{ $c->centro }}", {{ $c->total }}, {{ $c->total }}];
                centreData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(centreData);
            data.sort([{
                column: 1,
                desc: true
            }]);
            var maxim = data.getColumnRange(0);

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);

            var options = {
                title: 'TOTAL ENCUESTADOS - TENERIFE',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri', // i.e. 'Times New Roman'
                    fontSize: 20, // 12, 18 whatever you want (don't specify px)
                    bold: true, // true or false
                    italic: false // true of false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'
                },
                hAxis: {
                    // viewWindow:{
                    //     min:0,
                    //     max: maxim
                    // },
                    // format: '0'

                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "70%"
                },
                colors: [TF]

            };

            var chart = new google.visualization.BarChart(document.getElementById('centres_tfe'));
            chart.draw(view, options);
        }

        /**
         * Gráfica de Satisfacción x Centros TFE
         */
        function drawTFESatisfactionChart() {
            var centreData = [];
            var maxRange = 0;
            var row = ['Centro', '', {
                role: "style"
            }, {
                role: 'annotation'
            }];
            centreData.push(row);


            @foreach ($totalCentreTfe as $i => $c)
                var row = ["{{ $c->centro }}", {{ $c->satisfaction }}, blueGradient[{{ $i }}],
                    {{ $c->satisfaction }} + '%'
                ];
                centreData.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(centreData);
            data.sort([{
                column: 1,
                desc: true
            }]);

            var options = {
                title: 'SATISFACCIÓN POR CENTRO - TENERIFE',
                backgroundColor: '#EAECEE',
                titleTextStyle: {
                    fontName: 'Calibri',
                    fontSize: 20,
                    bold: true,
                    italic: false
                },
                vAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                    format: '0'

                },
                hAxis: {


                },
                legend: 'none',
                chartArea: {
                    width: "65%",
                    height: "80%"
                },
                bar: {
                    groupWidth: '99%'
                },
                colors: mycolors

            };

            var chart = new google.visualization.BarChart(document.getElementById('satisfaction_tfe'));
            chart.draw(data, options);
        }

        /**
         * Gráfica de Cuestionario Satisfacción
         */
        function drawQuestionsChart() {
            var questionData = [];
            var row = ["Edad", "SATISFACCIÓN", {
                role: "style"
            }, {
                role: 'annotation'
            }];
            questionData.push(row);

            @foreach ($preguntas as $j => $pregunta)
                @foreach ($porcentPreg as $i => $ppreg)
                    @if ($loop->iteration == $j + 1)
                        var row = ["P{{ $j + 1 }}", {{ $ppreg }}, mycolors[{{ $i }}],
                            {{ $ppreg }} + '%'
                        ];
                        questionData.push(row);
                    @endif
                @endforeach
            @endforeach

            var data = google.visualization.arrayToDataTable(questionData);
            var options = {
                title: 'SATISFACCIÓN',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20,
                    margin: 15
                },
                width: 800,
                height: 400,
                legend: {
                    position: 'none'
                },
                vAxis: {
                    title: 'PORCENTAJE',
                    minValue: 0,
                    format: '0'
                },
                hAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 10
                    },
                },
                chartArea: {
                    width: "75%",
                    height: "70%"
                },


            };

            var questionChart = new google.visualization.ColumnChart(document.getElementById('chartSatisfaccion'));
            questionChart.draw(data, options);
        }


        /**
         * Gráfica de NetPromoterScore
         */
        function drawNPSChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Pizza');
            data.addColumn('number', 'Populartiy');
            data.addRows([
                ['Promotores', {{ $totalSatisfaccion['promotores'] }}],
                ['Pasivos', {{ $totalSatisfaccion['pasivos'] }}],
                ['Detractores', {{ $totalSatisfaccion['detractores'] }}]
            ]);

            var options = {
                // title: 'NPS : {{ $totalSatisfaccion['nps'] }}',
                pieHole: 0.48,
                width: 600,
                height: 500,
                pieSliceTextStyle: {
                    color: 'black',
                    bold: 'true',
                    fontSize: 16
                },
                // sliceVisibilityThreshold: .2,
                legend: {
                    position: 'bottom'
                },
                chartArea: {
                    width: "75%",
                    left: "5%"
                },
                //  is3D: true, 
                colors: ['#21CCAD', '#FAC559', '#F86569']

            };

            var question5Chart = new google.visualization.PieChart(document.getElementById('question5'));
            question5Chart.draw(data, options);
        }

        function drawCarePlansChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Pizza');
            data.addColumn('number', 'Popularity');
            @foreach ($tiposAsistencia as $i => $te)
                data.addRows([
                    ['{{ $te->tipo }}', {{ $te->total }}],
                ]);
            @endforeach

            var options = {
                title: 'TIPOS DE ASISTENCIA',
                width: 600,
                height: 600,
                titleTextStyle: {
                    bold: true,
                    fontSize: 20
                },
                pieSliceText: 'percentage',
                sliceVisibilityThreshold: 0,
                legend: {
                    position: 'right',
                    textStyle: {
                        fontSize: 12
                    }
                },
                chartArea: {
                    width: '90%',
                    height: '80%'
                },
                is3D: true,
                colors: mycolors,
                pieSliceTextStyle: {
                    fontSize: 12
                }
            };

            var plansChart = new google.visualization.PieChart(document.getElementById('plansChart'));
            plansChart.draw(data, options);
        }


        function drawCompaniesTotal() {
            var data = [];
            var row = ["Year", "COMPAÑÍA", {
                role: 'style'
            }, {
                role: 'annotation'
            }];
            data.push(row);
            @foreach ($totalCompanies as $i => $te)
                var row = ["{{ $te->company }}", {{ $te->total }}, colors[{{ $i }}],
                    {{ $te->total }}
                ];
                data.push(row);
            @endforeach

            var data = google.visualization.arrayToDataTable(data);
            data.sort([{
                column: 1,
                desc: true
            }]); // Sort by the second column in descending order

            var groupData = google.visualization.data.group(
                data,
                [{
                    column: 0,
                    modifier: function() {
                        return 'total'
                    },
                    type: 'string'
                }],
                [{
                    column: 1,
                    aggregation: google.visualization.data.sum,
                    type: 'number'
                }]
            );

            var formatPercent = new google.visualization.NumberFormat({
                pattern: '#,##0.0%'
            });

            var formatShort = new google.visualization.NumberFormat({
                pattern: 'short'
            });

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1, {
                calc: function(dt, row) {
                    var amount = formatShort.formatValue(dt.getValue(row, 1));
                    var percent = formatPercent.formatValue(dt.getValue(row, 1) / groupData.getValue(0, 1));
                    return amount + ' (' + percent + ')';
                },
                type: 'string',
                role: 'annotation'
            }]);


            var options = {
                title: 'COMPAÑÍAS',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20,
                    margin: 15
                },
                legend: {
                    position: 'none'
                },
                vAxis: {
                    title: 'NÚMERO ENCUESTADOS',
                    minValue: 0,
                    format: '0'
                },
                hAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 5
                    },
                },
                chartArea: {
                    width: "80%",
                    height: "50%"
                },

                bar: {
                    groupWidth: "95%"
                },
                //  colors:  colors


            };

            var companiesChart = new google.visualization.ColumnChart(document.getElementById('companiesChart'));
            companiesChart.draw(view, options); // Use the DataView instead of the original DataTable
        }

        function drawOtherCompanies() {
            var data = [];
            var row = ["Year", "COMPAÑÍA", {
                role: 'style'
            }, {
                role: 'annotation'
            }];
            data.push(row);
            @foreach ($totalOtherCompanies as $i => $te)
                var row = ["{{ $te->company }}", {{ $te->total }}, colors[{{ $i }}],
                    {{ $te->total }}
                ];
                data.push(row);
            @endforeach

            var dataTable = google.visualization.arrayToDataTable(data);
            dataTable.sort([{
                column: 1,
                desc: true
            }]); // Sort by the second column in descending order

            var options = {
                title: 'OTRAS COMPAÑÍAS',
                titleTextStyle: {
                    bold: true,
                    fontSize: 20,
                    margin: 15
                },
                legend: {
                    position: 'none'
                },
                vAxis: {
                    title: 'NÚMERO DE ENCUESTADOS',
                    minValue: 0,
                    // format: '0'
                },
                hAxis: {
                    textStyle: {
                        fontSize: 10,
                        minTextSpacing: 5
                    },
                },
                chartArea: {
                    width: "50%",
                    height: "50%"
                },

                //  colors:  colors


            };

            var otherCompaniesChart = new google.visualization.ColumnChart(document.getElementById('otherCompaniesChart'));
            otherCompaniesChart.draw(dataTable, options);
        }
    </script>

</body>

</html>

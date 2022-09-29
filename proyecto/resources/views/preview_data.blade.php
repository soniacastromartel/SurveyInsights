

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <!-- <script type="text/javascript" src="https://www.google.com/jsapi"></script> -->

    <!-- <script src="https://www.google.com/jsapi"></script> -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <script src="{{asset('js/bootstrap.min.js') }}"></script>
    <style>
          .pie-chart {
            width: 400px;
            height:400px;
            /* margin-bottom: 200px;
            margin-top: 200px; */
            /* margin-left: 70px; */
           
        }
        .column-chart {
            width: 450px;
            height:400px;
            /* margin-left: 40px; */
        
            /* margin: 0 auto; */
        }
        .bar-chart {
            width: 700px;
            height:400px;
            margin-left: 40px;
        }
        .googleChartTitle {
            font: bold 20px Arial;
            /* text-align: center; */
            /* padding-left:20px; */
            text-transform: uppercase;
            margin-bottom:0px;
            margin-top:50px;

        }
        .pregunta {
            font-size: 14pt;
            font-weight: bold;
            padding-bottom: 15px;
        }

        #chartSexo{
            float:left;
        }

        #chartEdad{
            float:left;
        }

        /* body {
    margin:     0;
    padding:    0;
    width:      21cm;
    height:     29.7cm;
} */


/* Printable area */
#print-area {
    position:   relative;
    top:        1cm;
    left:       1cm;
    width:      19cm;
    height:     27.6cm;

    font-size:      10px;
    font-family:    Arial;
}


.ir-arriba {
	display:none;
	padding:20px;
	background:#bc012e;
	font-size:30px;
	color:#fff;
	cursor:pointer;
	position: fixed;
	bottom:20px;
	right:20px;
}
h1{
    font-size: 24pt;
    font-weight: bold;
    text-align: center;
    background-color: #bc012e;
  color: white;
  border-top: 1px solid black;
  border-bottom: 1px solid black;

}
h2{
font-size: 20pt;
font-weight: bold;
text-align: center;
background-color: #C0C0C0;
  color: black;
  border-top: 1px solid black;
  border-bottom: 1px solid black;
  margin-bottom:15px;


}

/* Salto de página */
.page_break {
  page-break-before: always;
}

    </style>
</head>

<body>
<br>
<h1>{{$title}}</h1>

<div class="px-4">
    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿Cuándo?
    </div>
    <div class="d-flex flex-row text-left  mt-2">
        Las encuestas se realizaron desde {{$period}}
    </div>

    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿A quién se dirigio?
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
        ¿Qué preguntamos?
    </div>

    <div class="d-flex flex-row text-left  mt-2">
        @foreach ($preguntas as $pregunta)
        {{$pregunta->n_pregunta}} {{$pregunta->question}} <br>
        @endforeach
    </div>


    <div class="d-flex flex-row text-left mt-4 pregunta">
        ¿Cuántas encuestas hicimos?
    </div>
    <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;">
        <strong> Totales: </strong>  Se realizaron
            {{ $totalEncuestados }}
            encuestas.
    </div>
    <div class="d-flex flex-row text-left" style="font-size: 12pt;">
        <strong> Por provincia: </strong> Se realizaron
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

    @if ( $params['province_id'] == 'TODAS')
    <div class="googleChartTitle col-md-4 text-center">
        <div id="chartProvincias" class="column-chart" style="margin: 0 auto;"></div>
     </div>
    @endif


     <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
     <h2>DATOS DE LA MUESTRA</h2>
     <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;"> Se muestran los resultados obtenidos de la muestra en cuanto a las variables edad, género y experiencia en los centros del grupo. </div>
     </div>

     <div class="googleChartTitle">
     <div id="chartSexo" class="pie-chart" ></div>
     <div id="chartEdad" class="column-chart"></div>
     </div>

    <div class="googleChartTitle">
        <div id="chartExperiencia" class="pie-chart" style="margin:0 auto;margin-top: 550px;margin-bottom: 50px;"></div>
     </div>
    <br>


     <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
     <h2>RESULTADOS POR SERVICIOS REQUERIDOS</h2>
     <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;"> Se muestran los resultados obtenidos en cuanto a servicios solicitados. </div>
     </div>

     @if ( $params['province_id'] == 'TODAS')
     <div>
            <div class="googleChartTitle">
            <div id="services_lpa" class="bar-chart"></div>
            </div>
        </div> 
            <div>
                <div class="googleChartTitle">
                    <div id ="services_tfe" class="bar-chart"></div>
                </div>
            </div>

       @elseif  ( $params['province_id'] == 'Las Palmas')
       <div>
            <div class="googleChartTitle">
            <div id="services_lpa" class="bar-chart"></div>
            </div>
        </div>    
        @else
            <div>
                <div class="googleChartTitle">
                    <div id ="services_tfe" class="bar-chart"></div>
                </div>
            </div>
       @endif


     <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
     <h2>RESULTADOS POR CENTRO</h2>
     <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;"> Se muestran los resultados obtenidos por centro. </div>
     </div>
 
    @if ( $params['province_id'] == 'TODAS')
        <div>
            <div class="googleChartTitle">
                <div id ="centres_lpa" class="bar-chart"></div>
            </div>
        </div>
        <div >
            <div class="googleChartTitle">
                <div id ="centres_tfe" class="bar-chart"></div>
            </div>
        </div>
    @elseif ( $params['province_id'] == 'Las Palmas')
            <div>
                <div class="googleChartTitle">
                    <div id ="centres_lpa" class="bar-chart"></div>
                </div>
            </div>
    @else
            <di>
                <div class="googleChartTitle">
                    <div id ="centres_tfe" class="bar-chart"></div>
                </div>
            </div>
        
    @endif
  

    @if ( $params['patient_id'] != '-1' )
    <div class="d-flex flex-row text-left mt-2 text-center pregunta" style="margin-top:100px"> 
    {{ $params['patient_id'] }}
    </div>     
    @endif

    
     <div style="margin-top: 50px;margin-bottom: 50px;" class="page_break">
     <h2>RESULTADOS DE SATISFACCIÓN</h2>
     <div class="d-flex flex-row text-left  mt-2" style="font-size: 12pt;"> Se muestran el porcentaje de la muestra que ha respondido bueno/muy bueno a las preguntas del cuestionario. </div>
     </div>

     <div class="googleChartTitle col-md-4 text-center" >
        <div id="chartSatisfaccion" class="column-chart"></div>
     </div>

    <div class="d-flex flex-row text-center mt-2 pregunta">
        PORCENTAJE DE PACIENTES QUE HAN RESPONDIDO BUENO Y MUY BUENO
    </div>

    <div class="" style="font-size: 12pt;">

        @foreach ($preguntas as $pregunta)
            @foreach ($porcentPreg as $ppreg)

                @if ( $loop->iteration == $pregunta->pregunta )
                    <strong>PREGUNTA {{$pregunta->pregunta}} : {{$ppreg}}% </strong><br>
                    {{$pregunta->question}}  <br>
                    <br>
                    @if ( $loop->iteration == 5 )
                    <div class="googleChartTitle col-md-4 text-center" style="margin: 0;padding: 0;">
                    <div><strong>NPS: </strong>{{$totalSatisfaccion['nps']}}%</div>
                    <div id ="question5" class="bar-chart"></div>
                    </div>
                    @endif
                @endif
            @endforeach
        @endforeach
    </div>

   



</div>





<script type="text/javascript">
    var GC= '#bc012e';
    var TF= '#1A73E8';

    // window.onload = function() {

        google.charts.load('44', {packages: ['corechart']}); 
        var interval = setInterval(function() { 
            if ( google.visualization !== undefined && google.visualization.DataTable !== undefined && google.visualization.PieChart !== undefined ){ 
                clearInterval(interval); 
                var provincia = "{{$params['province_id'] }}";
                console.log( provincia );
                drawSexChar(); 
                drawAgeChar();
                
                if (provincia  == 'Las Palmas' || provincia  == 'TODAS') {
                drawCentreLPAChart();
                drawServicesCharLPA();
                }
                if (provincia  == 'Tenerife' ||provincia  == 'TODAS') {
                drawCentreTFEChart();
                drawServicesCharTF();
                }
               
                drawExperienceChar();
                drawQuestionsChar();
                drawNPSChart();

                window.status = 'ready'; 
            } }, 100);

        // var provincia = "{{$params['province_id'] }}";
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
    // };

    $(document).ready(function(){

$('.ir-arriba').click(function(){
    $('body, html').animate({
        scrollTop: '0px'
    }, 300);
});

$(window).scroll(function(){
    if( $(this).scrollTop() > 0 ){
        $('.ir-arriba').slideDown(300);
    } else {
        $('.ir-arriba').slideUp(300);
    }
});

});

 

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
            title: 'GÉNERO',
            titleTextStyle: {bold:true, fontSize:20},
            sliceVisibilityThreshold: .2,
            legend: {position:'bottom'},
            chartArea: {left:"5%"},
            is3D: true, 
            colors: ['#bc012e', '#1A73E8']
        };

        var sexoChart = new google.visualization.PieChart(document.getElementById('chartSexo'));
        sexoChart.draw(data, options);
    }

    function drawExperienceChar() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Pizza');
        data.addColumn('number', 'Populartiy');
        data.addRows([
            ['1ª Vez', {{$totalExp['1º vez']}}],
            ['Ya ha estado', {{ $totalExp['Ya ha estado']}}]
        ]);

        var options = {
            title: 'EXPERIENCIA',
            pieHole: 0.4,
            width:500,
            height:500,
            titleTextStyle: {bold:true, fontSize:20},
            // sliceVisibilityThreshold: .2,
            legend: {position:'bottom'},
            chartArea: {left:"5%"},
            //  is3D: true, 
            colors: [GC, TF]

        };

        var experienceChart = new google.visualization.PieChart(document.getElementById('chartExperiencia'));
        experienceChart.draw(data, options);
    }

    function drawAgeChar() {
        var edadData = [];
        var row = ["Year", "EDAD", { role: "style" }, { role: 'annotation' } ];
        edadData.push(row);
        @foreach ($totalEdad as $te)
            var row = ["{{$te->edad}}", {{$te->total}}, "#1A73E8", {{$te->total}}];
            edadData.push(row);
        @endforeach

        var data = google.visualization.arrayToDataTable(edadData);
        var options = {
            title: 'EDAD',
            titleTextStyle: {bold:true, fontSize:20, margin:15},
            // bar: {groupWidth: "95%"},
            legend: {position:'bottom'},
            vAxis: {
                title: 'NÚMERO DE ENCUESTADOS', 
                minValue:0,
                format: '0'

            },
            hAxis: {
                textStyle : {
                    fontSize : 10,
                    minTextSpacing : 10
                },
            }
                };

        var edadChart = new google.visualization.ColumnChart(document.getElementById('chartEdad'));
        edadChart.draw(data, options);
    }

    function drawServicesCharLPA() {
        var serviceData = [];
        var row = ["Servicio", "SERVICIO", { role: "style" }, { role: 'annotation' }  ];
        serviceData.push(row);
        @foreach ($totalServiciosLPA as $ts)
            var row = [
            "{{$ts->servicio}}",
            {{$ts->total}}, 
             "#bc012e", 
             {{$ts->total}}];
            serviceData.push(row);
        @endforeach

        var data = google.visualization.arrayToDataTable(serviceData);

        var options = {
            title: 'SERVICIOS GRAN CANARIA',
            backgroundColor: '#EAECEE'
                            ,titleTextStyle: {
                             fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                         },
                        vAxis: {
                            textStyle : {
                            fontSize : 10,
                            minTextSpacing : 10
                            },
                            format: '0'
                        }
                      ,legend: 'none',
                      chartArea : {
                        width: "65%", 
                        height: "70%"
                      },
                      colors: [GC]
         };

        var serviceChart = new google.visualization.BarChart(document.getElementById('services_lpa'));
        serviceChart.draw(data, options);
    }

    function drawServicesCharTF() {
        var serviceData = [];
        var row = ["Servicio", "SERVICIOS", { role: 'annotation' }  ];
        serviceData.push(row);
        @foreach ($totalServiciosTF as $ts)
            var row = [
            "{{$ts->servicio}}",
            {{$ts->total}}, 
             {{$ts->total}}];
            serviceData.push(row);
        @endforeach


        var data = google.visualization.arrayToDataTable(serviceData);

        var options = {
            title: 'SERVICIOS TENERIFE',
            backgroundColor: '#EAECEE'
                            ,titleTextStyle: {
                            fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                        },
                        vAxis: {
                            textStyle : {
                            fontSize : 10,
                            minTextSpacing : 10
                            },
                            format: '0'
                        }
                      ,legend: 'none',
                      chartArea : {
                        width: "65%", 
                        height: "70%"
                      },
                      colors: [TF]

        };

        var serviceChart = new google.visualization.BarChart(document.getElementById('services_tfe'));
        serviceChart.draw(data, options);
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
        var options = {
                            title: 'CENTROS ICOT GRAN CANARIA',
                            backgroundColor: '#EAECEE'
                            ,titleTextStyle: {
                            fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                        },
                        vAxis: {
                            textStyle : {
                            fontSize : 10,
                            minTextSpacing : 10
                            },
                            format: '0'

                        }
                      ,legend: 'none',
                      chartArea : {
                        width: "45%", 
                        height: "70%"
                      },
                      colors: [GC]

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
        var options = {title: 'CENTROS ICOT TENERIFE',
                        backgroundColor: '#EAECEE'
                        ,titleTextStyle: {
                            fontName: 'Calibri', // i.e. 'Times New Roman'
                            fontSize: 20, // 12, 18 whatever you want (don't specify px)
                            bold: true,    // true or false
                            italic: false   // true of false
                        },
                        vAxis: {
                            textStyle : {
                            fontSize : 10,
                            minTextSpacing : 10
                            },
                            format: '0'
                        }
                      ,legend: 'none',
                      chartArea : {
                        width: "45%", 
                        height: "70%"
                      },
                      colors: [TF]

        };

        // Instantiate and draw the chart.
        var chart = new google.visualization.BarChart(document.getElementById('centres_tfe'));
        chart.draw(data, options);
    }

function drawQuestionsChar() {
    var questionData = [];
        var row = ["Edad", "SATISFACCIÓN", { role: "style" }, { role: 'annotation' } ];
        questionData.push(row);

        @foreach ($preguntas as $pregunta)
            @foreach ($porcentPreg as $ppreg)
                @if ( $loop->iteration == $pregunta->pregunta )
            var row = ["PREGUNTA {{$pregunta->pregunta}}", {{$ppreg}}, "#bc012e", {{$ppreg}} +'%'];
            questionData.push(row);
                @endif
            @endforeach
        @endforeach

        var data = google.visualization.arrayToDataTable(questionData);
        var options = {
            title: 'SATISFACCIÓN',
            titleTextStyle: {bold:true, fontSize:20, margin:15},
            width: 800,
            height: 400,
            bar: {groupWidth: "70%"},
            legend: {position:'bottom'},
            vAxis: {
                title: 'PORCENTAJE', 
                minValue:0,
                format: '0'
            },
            hAxis: {
                textStyle : {
                    fontSize : 10,
                    minTextSpacing : 10
                },
            },
            chartArea : {
                        width: "75%", 
                        height: "70%"
                      },
                      colors: ['#bc012e']

        };

        var questionChart = new google.visualization.ColumnChart(document.getElementById('chartSatisfaccion'));
        questionChart.draw(data, options);
}

function drawNPSChart(data, options) {
    var data = google.visualization.arrayToDataTable([
        ['Genre', 
        'Detractores', { role: 'annotation' },
        'Pasivos', { role: 'annotation' },
        'Promotores', { role: 'annotation' } ],
        ['Enero-Junio', {{$totalSatisfaccion['detractores']}}, 'Detractores', {{$totalSatisfaccion['pasivos']}},'Pasivos', {{$totalSatisfaccion['promotores']}}, 'Promotores']
      ]);

      var options_fullStacked = {
          isStacked: 'percent',
          height: 300,
          width: 900,
          legend: {position: 'bottom', maxLines: 3},
          colors: ['#F5B7B1', '#FAD7A0','#ABEBC6'],

          vAxis: {
            minValue: 0,
            ticks: [0, .3, .6, .9, 1]
          },
          chartArea : {
                        width: "65%", 
                        height: "50%"
                      }
        };
        
        var question5Chart = new google.visualization.BarChart(document.getElementById('question5'));
        question5Chart.draw(data, options_fullStacked);
}




</script>

</body>

</html>

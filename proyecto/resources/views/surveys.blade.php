@extends('layouts.app')
@section('content')

<div id="alertError" class="alert alert-danger" role="alert" style="display: none">
</div>

<div class="content" style="margin-top:60px;">
  <div class="container-fluid mt-3">

            <div class="col-lg-12">


                <div class="card ">
                    <div class="card-header card-header-danger card-header-text">
                      <div class="card-text">
                        <h4 class="card-title">{{$title}}</h4>
                      </div>
                    </div>
                    <div class="card-body" style="margin-top: 50px;margin-bottom: 50px;">
                        <form id="surveyResultsForm"  method="POST" >

                            @csrf
                            @method('POST')

                            <div class="row  mt-2 px-5">
                                <div class="form-group col-md-4">
                                    <span class="label">{{ __('Encuesta') }}</span>
                                    <div class="dropdown bootstrap-select">
                                        <select class="selectpicker" name="survey_id" id="survey_id" data-size="7" data-style="btn btn-red-icot btn-round" title=" Seleccione Encuesta" tabindex="-98">
                                            <option value="-1">Seleccione Encuesta</option>
                                            @foreach ($surveys as  $survey)
                                            <option value="{{$survey->sid}}">{{$survey->name}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="survey" id="survey"/> --}}
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <span class="label">{{ __('Provincia') }}</span>
                                    <div class="dropdown bootstrap-select">
                                        <select class="selectpicker" name="provincia_id" id="provincia_id" data-size="7" data-style="btn btn-red-icot btn-round" title=" Seleccione Provincia" tabindex="-98">
                                            <option value="-1">TODAS</option>
                                            @foreach ($provinces as  $province)
                                            <option value="{{$province}}">{{$province}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="province" id="province"/> --}}
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <span class="label">{{ __('Tipo de Paciente') }}</span>
                                    <div class="dropdown bootstrap-select">
                                        <select class="selectpicker" name="patient_id" id="patient_id" data-size="7" data-style="btn btn-red-icot btn-round" title=" Seleccione Tipo de Paciente" tabindex="-98">
                                            <option value="-1">TODOS</option>
                                            @foreach ($patientsType as  $patient)
                                            <option value="{{$patient}}">{{$patient}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="patient" id="patient"/> --}}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row  mt-2 px-5">

                                <div class="form-group col-md-3">
                                    <span class="label">{{ __('Fecha inicio') }}</span>
                                    <div class="input-group date">

                                        <input id="startDatePicker" class='form-control' type="date"  placeholder="yyyy/mm/dd" />
                                        <input type="hidden" name="startDate" id="startDate"/>
                                    </div>
                                </div>

                                <div class="form-group col-md-3">
                                    <span class="label">{{ __('Fecha fin') }}</span>
                                    <div class="input-group date">
                                        <input id="endDatePicker" class='form-control' type="date"  placeholder="yyyy/mm/dd" />
                                        <input type="hidden" name="endDate" id="endDate"/>
                                    </div>
                                </div>

                            </div>
                            <div class="row mt-2 px-5">
                                <div class="col-md-12">
                                    <button id="btnClear" href="#" class="btn btn-fill btn-warning">
                                    <span class="material-icons">clear_all</span> {{ __('Limpiar formulario') }}
                                        </button>
                                    <button id="btnSubmit" value="download" type="submit" class="btn btn-fill btn-success"
                                    formaction="download">
                                    <span class="material-icons">download</span> {{ __('Descargar') }}</button>

                                    <button id="btnSubmitLoad" type="submit" class="btn btn-fill btn-secondary" style="display: none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        {{ __('Cargando...') }}
                                    </button>


                                    <button id="btnPreview" type="submit" value="preview" class="btn btn-fill btn-red-icot" formaction="preview"> 
                                        <span class="material-icons">visibility</span> {{ __('Previsualización') }}</button>

                                    <!-- <button id="btnBack" href="/config" class="btn btn-fill btn-red-icot">
                                    <span class="material-icons">arrow_back</span>
                                    {{ __('Volver') }}
                                    </button> -->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="contenedor" style="margin-top:150px;"> 
        </div>
        </div>
        </div>

        <style>
    .lbl {
        color: black;
        font-weight: 600;
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 12px;
    }
        </style>

   
<!-- TODO: Filtros -->

    <!-- TODO: Fecha -->
    <!-- TODO: Provincia -->
<script type="text/javascript">
    $(function () {
        /** 
         * Obtenemos el elemento activo y en funcion del atributo 'formaction' vemos si se trata de descarga o preview y lanzamos la petición ajax correspondiente
         * */

    $("#surveyResultsForm").submit(function(event){
            event.preventDefault();
            var action = $(document.activeElement).attr('formaction');

            console.log(params);
            console.log(params["startDate"]);

            params["startDate"] = $("#startDatePicker").val();
            params["endDate"] = $("#endDatePicker").val();
            params["province_id"] = $( "#provincia_id option:selected").text();
            params["survey_id"] = $( "#survey_id option:selected").val();
            params["patient_id"] = $( "#patient_id option:selected").val();
    
            if( params["startDate"]== undefined || params["endDate"]== undefined){
                timeOutAlert( $('#alertError'),'DEBE SELECCIONAR FECHA DE INICIO Y FIN' );
            }

            if(action =='download'){
                $('#alertError').hide();
                $('#btnSubmit').hide();
                $('#btnSubmitLoad').show();

                $("#surveyResultsForm").attr('action','{{ route("survey.download") }}');
                event.preventDefault();
                $.ajax({
                url: $("#surveyResultsForm").attr('action'),
                type: 'get',
                data: params,
                dataType: 'binary',
                xhrFields: {
                    'responseType': 'blob'
                },
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 2) {
                            if (xhr.status == 200) {
                                xhr.responseType = "blob";
                            } else {
                                xhr.responseType = "text";
                            }
                        }
                    };
                    return xhr;
                },
                success: function(data, textStatus, jqXHR) {
                    console.log(data);
                    if(textStatus === 'success') {
                        console.log('success');
                        $('#btnSubmitLoad').hide();
                        $('#btnSubmit').show();
                        var link = document.createElement('a'),
                        filename = 'estadisticas.pdf';
                        link.href = URL.createObjectURL(data);
                        link.download = filename;
                        link.click();
                    }

                },
                error: function(xhr, status, error) {
                    console.log('error'+ xhr.responseText);
                    var response = JSON.parse(xhr.responseText);
                    timeOutAlert( $('#alertError'),response.mensaje );

                    $('#btnSubmitLoad').hide();
                    $('#btnSubmit').show();
                }

            }).fail(function(jqXHR, textStatus, error) {
                console.log('fail' + jqXHR.responseText);
                var response = JSON.parse(jqXHR.responseText);
           timeOutAlert( $('#alertError'),response.errors);

            });

            }else{
                $('#alertError').hide();
                $('#btnPreview').hide();
                $('#btnSubmitLoad').show();

                $("#surveyResultsForm").attr('action','{{ route("survey.preview") }}');
                event.preventDefault();
                $.ajax({
                url:  "{{ route('survey.preview') }}",
                type: 'get',
                data: params,   
                success: function(data, textStatus, jqXHR) {
                    if(textStatus === 'success') {
                        $('#btnSubmitLoad').hide();
                        $('#btnPreview').show();
                        $("#contenedor").append('<div>arrow_upward</div>');
                        $("#contenedor").html(data);
                        $("#contenedor").append('<span class="ir-arriba material-icons">arrow_upward</span>');
    
                    }

                },
                error: function(xhr, status, error) {
                    console.log('error'+ xhr.responseText);
                    var response = JSON.parse(xhr.responseText);
                    timeOutAlert( $('#alertError'),' Faltan datos o hay datos incorrectos');

                    $('#btnSubmitLoad').hide();
                    $('#btnPreview').show();
                }

            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log('fail');
                var response = JSON.parse(jqXHR.responseText);
           timeOutAlert( $('#alertError'),response.mensaje);

            });
       
            }
   
   
   
  });


        var d = new Date();
        //var textMonthYear =  d.getDate()  + '/' + (d.getMonth()+1) + '/' + d.getFullYear()   ;
        $('#startDatePicker').datepicker( $.datepicker.regional[ "es" ] );
        $('#endDatePicker').datepicker( $.datepicker.regional[ "es" ] );

        params = {};
        params["_token"] = "{{ csrf_token() }}";

        function clearForms()
        {
            $('select#survey_id').val('-1');
            $('select#provincia_id').val('-1');
            $('select#patient_id').val('-1');
            $('select#survey_id').selectpicker("refresh");
            $('select#provincia_id').selectpicker("refresh");
            $('select#patient_id').selectpicker("refresh");
            $('input').val('');
            $("#contenedor").html("");

        }
        $("#btnClear").on('click', function(e){
            e.preventDefault();
            clearForms();
        });
 
        // function submitForm(){
        //     $("#surveyResultsForm").attr('action','{{ route("survey.download") }}');
        //     $("#surveyResultsForm").attr('action','{{ route("survey.download") }}');

        // }

        // $("#btnPreview").on('click', function(e){
        //     console.log(params);

        //     params["startDate"] = $("#startDatePicker").val();
        //     params["endDate"] = $("#endDatePicker").val();
        //     params["province_id"] = $( "#provincia_id option:selected").text();
        //     params["survey_id"] = $( "#survey_id option:selected").val();
        //     params["patient_id"] = $( "#patient_id option:selected").val();
            
        //      if( params["startDate"]== null || params["endDate"]== null){
        //          timeOutAlert( $('#alertError'),'DEBE SELECCIONAR FECHA DE INICIO Y FIN' );
        //      }
        //     $('#alertError').hide();
        //     $('#btnSubmit').hide();
        //     $('#btnSubmitLoad').show();

        //     $("#surveyResultsForm").attr('action','{{ route("survey.preview") }}');

            
        //     e.preventDefault();
        //     $.ajax({
        //         url: $("#surveyResultsForm").attr('action'),
        //         type: 'post',
        //         data: params,
        //         dataType: 'binary',
        //         xhrFields: {
        //             'responseType': 'blob'
        //         },
        //         xhr: function() {
        //             var xhr = new XMLHttpRequest();
        //             xhr.onreadystatechange = function() {
        //                 if (xhr.readyState == 2) {
        //                     if (xhr.status == 200) {
        //                         xhr.responseType = "blob";
        //                     } else {
        //                         xhr.responseType = "text";
        //                     }
        //                 }
        //             };
        //             return xhr;
        //         },
        //         success: function(data, textStatus, jqXHR) {
        //             console.log(data);
        //             if(textStatus === 'success') {
        //                 console.log('success');
                       
        //             }

        //         },
        //         error: function(xhr, status, error) {
        //             console.log('error'+ xhr.responseText);
        //             var response = JSON.parse(xhr.responseText);
        //             timeOutAlert( $('#alertError'),response.mensaje );

        //             $('#btnSubmitLoad').hide();
        //             $('#btnSubmit').show();
        //         }

        //     }).fail(function(jqXHR, textStatus, errorThrown) {
        //         console.log('fail');
        //         var response = JSON.parse(jqXHR.responseText);
        //    timeOutAlert( $('#alertError'),response.mensaje);

        //     });
       
        // });




        // $("#btnSubmit").on('click', function(e){
        //     console.log(params);
        //     params["startDate"] = $("#startDatePicker").val();
        //     params["endDate"] = $("#endDatePicker").val();
        //     params["province_id"] = $( "#provincia_id option:selected").text();
        //     params["survey_id"] = $( "#survey_id option:selected").val();
        //     params["patient_id"] = $( "#patient_id option:selected").val();
            
        //      if( params["startDate"]== null || params["endDate"]== null){
        //          timeOutAlert( $('#alertError'),'DEBE SELECCIONAR FECHA DE INICIO Y FIN' );
        //      }
        //     $('#alertError').hide();
        //     $('#btnSubmit').hide();
        //     $('#btnSubmitLoad').show();
        //     $("#surveyResultsForm").attr('action','{{ route("survey.download") }}');

        //     e.preventDefault();
        //     $.ajax({
        //         url: $("#surveyResultsForm").attr('action'),
        //         type: 'get',
        //         data: params,
        //         dataType: 'binary',
        //         xhrFields: {
        //             'responseType': 'blob'
        //         },
        //         xhr: function() {
        //             var xhr = new XMLHttpRequest();
        //             xhr.onreadystatechange = function() {
        //                 if (xhr.readyState == 2) {
        //                     if (xhr.status == 200) {
        //                         xhr.responseType = "blob";
        //                     } else {
        //                         xhr.responseType = "text";
        //                     }
        //                 }
        //             };
        //             return xhr;
        //         },
        //         success: function(data, textStatus, jqXHR) {
        //             console.log(data);
        //             if(textStatus === 'success') {
        //                 console.log('success');
        //                 $('#btnSubmitLoad').hide();
        //                 $('#btnSubmit').show();
        //                 var link = document.createElement('a'),
        //                 filename = 'estadisticas.pdf';
        //                 link.href = URL.createObjectURL(data);
        //                 link.download = filename;
        //                 link.click();
        //             }

        //         },
        //         error: function(xhr, status, error) {
        //             console.log('error'+ xhr.responseText);
        //             var response = JSON.parse(xhr.responseText);
        //             timeOutAlert( $('#alertError'),response.mensaje );

        //             $('#btnSubmitLoad').hide();
        //             $('#btnSubmit').show();
        //         }

        //     }).fail(function(jqXHR, textStatus, errorThrown) {
        //         console.log('fail');
        //         var response = JSON.parse(jqXHR.responseText);
        //    timeOutAlert( $('#alertError'),response.mensaje);

        //     });
        // });


    });

    function timeOutAlert($alert, $message) {
        $alert.text($message);
        $alert.show().delay(2000).slideUp(300);
    }
</script>
@endsection

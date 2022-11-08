@extends('layouts.app')
@section('content')

<div id="alertError" class="alert alert-danger" role="alert" style="display: none"></div>
<div id="alertSuccess" class="alert alert-success" role="alert" style="display: none"></div>

<!-- MODAL -->
<div class="modal fade" id="modal-validate" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="background-color: white;background-position: center top;background-repeat: repeat;">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="exampleModalLongTitle">FORMULARIO DE ENVÍO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="contact" class="modal-body">
                <div style="margin-bottom:20px;">
                    Complete los siguientes datos para el envío del informe
                </div>
                <div class="form-group"></div>
                <label for="from">Remitente</label>
                <input class="form-control" id="from" placeholder="Remitente" type="email" tabindex="1" name="name" required autofocus>
                <label for="to">Destinatario *</label>
                <input class="form-control" id="to" placeholder="Destinatario1;Destinatario2;Destinatario3..." type="email" tabindex="2" name="name" required autofocus>
                <label for="subject">Asunto</label>
                <input class="form-control" id="subject" placeholder="Asunto" type="text" tabindex="3" name="name" required autofocus>
                <label for="message">Mensaje</label>
                <textarea class="form-control" id="message" placeholder="Escriba su mensaje aquí...." tabindex="5" name="message" cols="30" rows="10" required></textarea>
                <label class="lbl">* Para varios destinatarios: escriba las direcciones de correo separados por punto y coma y sin espacios.</label>

            </div>
            <div class="modal-footer">
                <button id="btnSendMail" value="mail" class="btn-send btn btn-fill btn-info"> <span class="material-icons">send</span> {{ __(' Enviar') }}</button>
                <button id="btnLoad" type="submit" class="btn btn-fill btn-secondary" style="display: none">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ __('Cargando...') }}
                </button>
                <button type="button" class="btn btn-red-icot" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- PRINCIPIO -->
<div class="content" style="margin-top:60px;">
    <div class="col-md-6" style="margin:0 auto; margin-bottom: 50px;">
        @if ($nDays != "")
        <div class="alert alert-timeout alert-danger animacion custom-alert" style="position:relative; left:10px;" role="alert">
            Entrega de Informe Trimestral en {{$nDays}} días
        </div>
        @endif
    </div>

    <div class="container-fluid mt-3">
        <div class="col-lg-12">
            <div class="card ">
                <div class="card-header card-header-primary card-header-text">
                    <div class="card-text">
                        <h4 class="card-title">{{$title}}</h4>
                    </div>
                </div>
                <div class="card-body" style="margin-top: 50px;margin-bottom: 50px;">
                    <form id="surveyResultsForm" method="POST">

                        @csrf
                        @method('POST')

                        <div class="row  mt-2 px-5">
                            <div class="form-group col-md-4">
                                <span class="label">{{ __('Encuesta') }}</span>
                                <div class="dropdown bootstrap-select">
                                    <select class="selectpicker" name="survey_id" id="survey_id" data-size="7" data-style="btn btn-red-icot btn-round" title=" Seleccione Encuesta" tabindex="-98">
                                        <option value="-1">Seleccione Encuesta</option>
                                        @foreach ($surveys as $survey)
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
                                        @foreach ($provinces as $province)
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
                                        @foreach ($patientsType as $patient)
                                        <option value="{{$patient->code}}">{{$patient->type}}</option>
                                        @endforeach
                                    </select>
                                    {{-- <input type="hidden" name="patient" id="patient"/> --}}
                                </div>
                            </div>
                        </div>

                        <div class="row  mt-2 px-5">

                            <div class="form-group col-md-4">
                                <span class="label">{{ __('Fecha inicio') }}</span>
                                <div class="input-group date">

                                    <input id="startDatePicker" class='form-control' type="date" placeholder="yyyy/mm/dd" />
                                    <input type="hidden" name="startDate" id="startDate" />
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <span class="label">{{ __('Fecha fin') }}</span>
                                <div class="input-group date">
                                    <input id="endDatePicker" class='form-control' type="date" placeholder="yyyy/mm/dd" />
                                    <input type="hidden" name="endDate" id="endDate" />
                                </div>
                            </div>

                            <div class="form-group col-md-4 company_picker">
                                <span class="label">{{ __('Compañía') }}</span>
                                <div class="dropdown bootstrap-select">
                                    <select class="selectpicker" name="company_id" id="company_id" data-size="7" data-style="btn btn-red-icot btn-round" title=" Seleccione Compañía" tabindex="-98">
                                        <option value="-1">TODAS</option>
                                    </select>
                                    {{-- <input type="hidden" name="patient" id="patient"/> --}}
                                </div>
                            </div>


                        </div>
                        <div class="row mt-2 px-5">
                            <div class="col-md-12">

                                <button id="btnClear" href="#" class="btn btn-fill btn-warning">
                                    <span class="material-icons">clear_all</span> {{ __('Limpiar formulario') }}
                                </button>
                                <button id="btnSubmit" value="download" type="submit" class="btn btn-fill btn-success" formaction="download">
                                    <span class="material-icons">download</span> {{ __('Descargar') }}</button>
                                <button id="btnSubmitLoad" type="submit" class="btn btn-fill btn-secondary" style="display: none">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ __('Cargando...') }}
                                </button>


                                <button id="btnPreview" type="submit" value="preview" class="btn btn-fill btn-red-icot" formaction="preview">
                                    <span class="material-icons">visibility</span> {{ __('Previsualización') }}</button>
                                    <button id="btnPreviewLoad" type="submit" class="btn btn-fill btn-secondary" style="display: none">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ __('Cargando...') }}
                                </button>

                                <!-- <button  id="launchModal" type="submit" value="launch" class="btn btn-fill btn-info" formaction="launch">
                                    <span class="material-icons">email</span> {{ __('Enviar') }}</button> -->

                                <button data-toggle="modal" id="btnSendMail" data-target="#modal-validate" class="btn btn-fill btn-info">
                                    <span class="material-icons">email</span> {{ __('Enviar') }}</button>

                                <!-- <a href="mailto:email?subject=example?body=example">Send Email</a> -->

                                <!-- <a data-toggle="modal" id="btnSend" class="btn btn-fill btn-info" href="#modal-validate"></a> -->

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
        <div id="contenedor">
        </div>

    </div>
</div>

<!-- @include('inc.modal') -->

<style>
    label {
        color: black;
        font-weight: 600;
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 16px;
    }

    .lbl {
        color: black;
        font-weight: 600;
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 12px;
    }

    .custom-alert {
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        background-color: #bc012e !important;

    }

    /* style del Modal */
    fieldset {
        border: medium none !important;
        margin: 0 0 10px;
        min-width: 100%;
        padding: 0;
        width: 100%;
    }

    #contact input[type="text"],
    #contact input[type="email"],
    #contact textarea {
        width: 100%;
        border: 1px solid #ccc;
        background: #FFF;
        margin: 0 0 5px;
        padding: 10px;
    }

    #contact input[type="text"]:hover,
    #contact input[type="email"]:hover,
    #contact textarea:hover {
        -webkit-transition: border-color 0.3s ease-in-out;
        -moz-transition: border-color 0.3s ease-in-out;
        transition: border-color 0.3s ease-in-out;
        border: 1px solid #aaa;
    }

    #contact textarea {
        height: 100px;
        max-width: 100%;
        resize: none;
    }

    #contact input:focus,
    #contact textarea:focus {
        outline: 0;
        border: 1px solid #aaa;
    }

    ::-webkit-input-placeholder {
        color: #888;
    }

    :-moz-placeholder {
        color: #888;
    }

    ::-moz-placeholder {
        color: #888;
    }

    :-ms-input-placeholder {
        color: #888;
    }
</style>


<!-- TODO: Filtros -->

<!-- TODO: Fecha -->
<!-- TODO: Provincia -->
<script type="text/javascript">
    $(function() {
        $('.company_picker').hide();

        /** 
         * Obtenemos el elemento activo y en funcion del atributo 'formaction' vemos si se trata de descarga o preview y lanzamos la petición ajax correspondiente
         * */

        $("#surveyResultsForm").submit(function(event) {
            event.preventDefault();
            var action = $(document.activeElement).attr('formaction');

            console.log(params);
            console.log($("#company_id option:selected").text());

            params["startDate"] = $("#startDatePicker").val();
            params["endDate"] = $("#endDatePicker").val();
            params["province_id"] = $("#provincia_id option:selected").text();
            params["survey_id"] = $("#survey_id option:selected").val();
            params["patient_id"] = $("#patient_id option:selected").val();
            params["patient_name"]=$("#patient_id option:selected").text();
            params["company"]=$("#company_id option:selected").text();

            if (params["startDate"] == undefined || params["endDate"] == undefined) {
                timeOutAlert($('#alertError'), 'DEBE SELECCIONAR FECHA DE INICIO Y FIN');
            }

            if (action == 'download') {
                $('#alertError').hide();
                $('#btnSubmit').hide();
                $('#btnSubmitLoad').show();

                $("#surveyResultsForm").attr('action', '{{ route("survey.download") }}');
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
                        if (textStatus === 'success') {
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
                        var response = JSON.parse(xhr.responseText);
                        timeOutAlert($('#alertError'), response.mensaje);

                        $('#btnSubmitLoad').hide();
                        $('#btnSubmit').show();
                    }

                }).fail(function(jqXHR, textStatus, error) {
                    var response = JSON.parse(jqXHR.responseText);
                    timeOutAlert($('#alertError'), response.errors);
                    $('#btnSubmitLoad').hide();
                    $('#btnSubmit').show();

                });

            } else if (action == 'preview') {
                $('#alertError').hide();
                $('#btnPreview').hide();
                $('#btnPreviewLoad').show();

                $("#surveyResultsForm").attr('action', '{{ route("survey.preview") }}');
                event.preventDefault();
                $.ajax({
                    url: "{{ route('survey.preview') }}",
                    type: 'get',
                    data: params,
                    success: function(data, textStatus, jqXHR) {
                        if (textStatus === 'success') {
                            $('#btnPreviewLoad').hide();
                            $('#btnPreview').show();
                            $("#contenedor").append('<div>arrow_upward</div>');
                            $("#contenedor").html(data);
                            $("#contenedor").append('<span class="ir-arriba material-icons">arrow_upward</span>');

                        }

                    },
                    error: function(xhr, status, error) {
                        var response = JSON.parse(xhr.responseText);
                        timeOutAlert($('#alertError'), ' No hay datos que mostrar. Compruebe si hay datos incorrectos o faltan datos.');

                        $('#btnPreviewLoad').hide();
                        $('#btnPreview').show();
                    }

                }).fail(function(jqXHR, textStatus, errorThrown) {
                    var response = JSON.parse(jqXHR.responseText);
                    timeOutAlert($('#alertError'), response.mensaje);
                    $('#btnPreviewLoad').hide();
                    $('#btnPreview').show();

                });

            }

        });


        $("#btnSendMail").on('click', function(event) {
            $('#alertError').hide();
            $('#btnSendMail').hide();
            $('#btnLoad').show();
            url = "{{ route('survey.mail') }}";
            sendMail(url);
        });

        function sendMail(url) {
            params["from"] = $("#from").val();
            params["to"] = $("#to").val();
            params["subject"] = $("#subject").val();
            params["message"] = $("#message").val();

            $.ajax({
                url: url,
                type: 'get',
                data: params,
                success: function(data, textStatus, jqXHR) {
                    if (textStatus === 'success') {
                        $('#btnLoad').hide();
                        $('#btnSendMail').show();
                        $('#modal-validate .close').click();
                        timeOutAlert($('#alertSuccess'), 'Correo Enviado');


                    }

                },
                error: function(xhr, status, error) {
                    var response = JSON.parse(xhr.responseText);
                    timeOutAlert($('#alertError'), 'No hay datos que mostrar. Compruebe si hay datos incorrectos o faltan datos.');
                    $('#btnLoad').hide();
                    $('#btnSendMail').show();
                }

            }).fail(function(jqXHR, textStatus, errorThrown) {
                var response = JSON.parse(jqXHR.responseText);
                timeOutAlert($('#alertError'), response.mensaje);

            });
        }



        var d = new Date();
        //var textMonthYear =  d.getDate()  + '/' + (d.getMonth()+1) + '/' + d.getFullYear()   ;
        $('#startDatePicker').datepicker($.datepicker.regional["es"]);
        $('#endDatePicker').datepicker($.datepicker.regional["es"]);

        params = {};
        params["_token"] = "{{ csrf_token() }}";

        function clearForms() {
            $('select#survey_id').val('-1');
            $('select#provincia_id').val('-1');
            $('select#patient_id').val('-1');
            $('select#company_id').val('-1');
            $('select#survey_id').selectpicker("refresh");
            $('select#provincia_id').selectpicker("refresh");
            $('select#patient_id').selectpicker("refresh");
            $('select#company_id').selectpicker("refresh");
            $('input').val('');
            $("#contenedor").html("");
            $('.company_picker').hide();


        }

        $("#btnClear").on('click', function(e) {
            e.preventDefault();
            clearForms();
        });

        $("#survey_id").on('change', function(e) {
            $('select#provincia_id').val('-1');
            $('select#patient_id').val('-1');
            $('select#company_id').val('-1');
            $('select#provincia_id').selectpicker("refresh");
            $('select#patient_id').selectpicker("refresh");
            $('select#company_id').selectpicker("refresh");
            $('input').val('');
            $("#contenedor").html("");        
        });

        $("#patient_id").on('change', function(e) {
             if($("#survey_id option:selected").val()!=285213){

                var code = $(this).val();
            var param = {};
            if (code != -1 && code != 'T4' && code != 'T5') {
                if (code == 'T1') {
                    param["code"] = '323';
                } else if (code == 'T2') {
                    param["code"] = '325';
                } else if (code == 'T3') {
                    param["code"] = '324';
                }
                $.ajax({
                    url: '{{route("survey.getCompanies")}}',
                    type: 'get',
                    data: param,
                    success: function(data, textStatus, jqXHR) {
                        if (textStatus === 'success') {
                            $('.company_picker').show();
                            $("#company_id").empty();
                            $("#company_id").append('<option value="-1">TODAS </option>');
                            $.each(data, function(index, value) {
                                $("#company_id").append('<option value="' + index + '">' + value + '</option>');
                            });
                            $('#company_id').selectpicker('refresh');
                        }
                    },
                    error: function(xhr, status, error) {
                        var response = JSON.parse(xhr.responseText);
                        timeOutAlert($('#alertError'), response.message);
                    }

                }).fail(function(jqXHR, textStatus, errorThrown) {
                    var response = JSON.parse(jqXHR.responseText);
                    timeOutAlert($('#alertError'), response.message);
                });
            } else {
                $('.company_picker').hide();
            }


             }
            

        });


    });

    function timeOutAlert($alert, $message) {
        $alert.text($message);
        $alert.show().delay(3500).slideUp(300);
    }
</script>
@endsection
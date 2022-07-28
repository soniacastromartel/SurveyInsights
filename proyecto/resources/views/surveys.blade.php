@extends('layouts.app')
@section('content')

<div id="alertError" class="alert alert-danger" role="alert" style="display: none">
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="card ">
                    <div class="card-header card-header-info card-header-text">
                      <div class="card-text">
                        <h4 class="card-title">{{$title}}</h4>
                      </div>
                    </div>
                    <div class="card-body">
                        <form id="surveyResultsForm"  method="POST" >

                            @csrf
                            @method('POST')

                            <div class="row  mt-2 px-5">
                                <div class="form-group col-md-4">
                                    <span>{{ __('Encuesta') }}</span>
                                    <div class="dropdown bootstrap-select">
                                        <select  name="survey_id" id="survey_id" data-size="7" data-style="btn btn-primary btn-round" title=" Seleccione Encuesta" tabindex="-98">
                                            <option value="-1">Seleccione Encuesta</option>
                                            @foreach ($surveys as  $survey)
                                            <option value="{{$survey->sid}}">{{$survey->name}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="province" id="province"/> --}}
                                    </div>
                                </div>

                                <div class="form-group col-md-2">
                                    <span>{{ __('Provincia') }}</span>
                                    <div class="dropdown bootstrap-select">
                                        <select name="provincia_id" id="provincia_id" data-size="7" data-style="btn btn-primary btn-round" title=" Seleccione Provincia" tabindex="-98">
                                            <option value="-1">TODAS</option>
                                            @foreach ($provinces as  $province)
                                            <option value="{{$province}}">{{$province}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="province" id="province"/> --}}
                                    </div>
                                </div>

                                <div class="form-group col-md-2">
                                    <span>{{ __('Tipo de Paciente') }}</span>
                                    <div class="dropdown bootstrap-select">

                                        <select name="patient_id" id="patient_id" data-size="7" data-style="btn btn-primary btn-round" title=" Seleccione Tipo de Paciente" tabindex="-98">
                                            <option value="-1">TODOS</option>
                                            @foreach ($patientsType as  $patient)
                                            <option value="{{$patient}}">{{$patient}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <input type="hidden" name="province" id="province"/> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="row  mt-2 px-5">

                                <div class="form-group col-md-2">
                                    <span>{{ __('Fecha inicio') }}</span>
                                    <div class="input-group date">

                                        <input id="startDatePicker" class='form-control' type="text"  placeholder="yyyy/mm/dd" />
                                        <input type="hidden" name="startDate" id="startDate"/>
                                    </div>
                                </div>

                                <div class="form-group col-md-2  ">
                                    <span>{{ __('Fecha fin') }}</span>
                                    <div class="input-group date">
                                        <input id="endDatePicker" class='form-control' type="text"  placeholder="yyyy/mm/dd" />
                                        <input type="hidden" name="endDate" id="endDate"/>
                                    </div>
                                </div>

                            </div>
                            <div class="row mt-2 px-5">
                                <div class="col-md-7">
                                    <button id="btnClear" href="#" class="btn btn-fill btn-warning">
                                        {{ __('Limpiar formulario') }}
                                        </button>
                                    <button id="btnSubmit" type="submit" class="btn btn-fill btn-success">{{ __('Calcular') }}</button>
                                    <button id="btnSubmitLoad" type="submit" class="btn btn-success" style="display: none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        {{ __('Obteniendo datos...') }}
                                    </button>

                                    <button id="btnPreview" type="submit" class="btn btn-fill btn-primary">{{ __('Previsualización') }}</button>

                                    <button id="btnBack" href="/config" class="btn btn-fill btn-danger">
                                    {{ __('Volver') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- TODO: Filtros -->

    <!-- TODO: Fecha -->
    <!-- TODO: Provincia -->
<script type="text/javascript">
    $(function () {
        var d = new Date();
        //var textMonthYear =  d.getDate()  + '/' + (d.getMonth()+1) + '/' + d.getFullYear()   ;
        $('#startDatePicker').datepicker( $.datepicker.regional[ "es" ] );
        $('#endDatePicker').datepicker( $.datepicker.regional[ "es" ] );

        params = {};
        params["_token"] = "{{ csrf_token() }}";

        function clearForms()
        {
            $('select').val('-1');
            //$('select').selectpicker("refresh");
            $('input').val('');
        }
        $("#btnClear").on('click', function(e){
            e.preventDefault();
            clearForms();
        });


        $("#btnSubmit").on('click', function(e){

            // console.log( $('#startDatePicker').val());
            // console.log( $('#endDatePicker').val());
            params["startDate"] = $("#startDatePicker").val();
            params["endDate"] = $("#endDatePicker").val();
            params["province_id"] = $( "#provincia_id option:selected").text();
            params["survey_id"] = $( "#survey_id option:selected").val();
            params["patient_id"] = $( "#patient_id option:selected").val();

            $('#alertError').hide();
            $('#btnSubmit').hide();
            $('#btnSubmitLoad').show();
            $("#surveyResultsForm").attr('action','{{ route("survey.download") }}');

            e.preventDefault();
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
                    if(textStatus === 'success') {
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
                    //alert(response.errors);
                    timeout( $('#alertError'),response.errors );

                    $('#btnSubmitLoad').hide();
                    $('#btnSubmit').show();
                }

            }).fail(function(jqXHR, textStatus, errorThrown) {
           timeout( $('#alertError'),errorThrown );

            });
        });
        $("#btnPreview").on('click', function(e){

        });

    });

    function timeOutAlert($alert, $message) {
        $alert.text($message);
        $alert.show().delay(2000).slideUp(300);
    }
</script>
@endsection

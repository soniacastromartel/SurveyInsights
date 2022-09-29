<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use mikehaertl\wkhtmlto\Pdf;
use mikehaertl\tmp\File;

class SurveyController extends Controller
{
    protected $icotSurvey;
    protected $surveyName;
    protected $periodTime;
    protected $fields;
    protected $whereProvincias;
    protected $whereProvinciasLpa;
    protected $whereProvinciasTfe;
    protected $whereTipoPaciente;
    protected $preguntas;

    public function index(Request $request)
    {
        $this->icotSurvey = DB::connection('icotsurvey');
        $surveys = $this->icotSurvey->select('select sid, name
                                    from lime_surveys
                                    where expires is null and name is not null');
        $patientsType = [
            "Servicio Canario de la Salud", "Laboral / diversos", "Seguro médico", "Accidente tráfico", "Privado"
        ];

        $servicesType = $this->getServices();

        return view('surveys', [
            'title'        => 'ENCUESTAS DE SATISFACCIÓN', 'provinces'    => ['Las Palmas', 'Tenerife'], 'surveys'      => $surveys, 'patientsType' => $patientsType, 'servicesType' => $servicesType
        ]);
    }

    /**Function que coge parametros de la encuesta de icotSurvey */
    public function getFieldsSurvey($surveyFields)
    {
        $fields = [];
        foreach ($surveyFields as $key => $sf) {
            if (!empty($sf->type)) {
                $fields[$sf->name][] = ['name' =>  $sf->field, 'type' => [$sf->type => $sf->value]];
            } else {
                $fields[$sf->name]   = ['name' =>  $sf->field];
            }
        }

        return $fields;
    }

    public function getServices()
    {
        $servicesNames = [];
        $service = $this->icotSurvey->select(
            'select lime_answers.answer as servicio from  lime_answers where lime_answers.qid = ' . '72'
        );
        foreach ($service as $s) {
            $servicesNames[] = $s->servicio;
        }

        return $servicesNames;
    }

    public function getWhere()
    {
        $where = '';

        if (!empty($this->whereTipoPaciente) && !empty($this->whereProvincias)) { // si,si
            $where .= " and " . $this->whereTipoPaciente;
            if (strpos(substr($where, 0, 5), 'and') === false || strpos(substr($this->whereProvincias, 0, 5), 'and') === false) {
                $where .= " and " .$this->whereProvincias;
            } else {
                $where .=  $this->whereProvincias;
            }
        } else if (empty($this->whereTipoPaciente) && empty($this->whereProvincias)) { // no, no
            $where .=  " ";
        }
        // else if (!empty($this->whereTipoPaciente) && empty($this->whereProvincias)){//si,no
        //     $where .= " and " . $this->whereTipoPaciente;
        // }
         else if (empty($this->whereTipoPaciente) && !empty($this->whereProvincias)){//si,no
             if (strpos(substr($where, 0, 5), 'and') === false && strpos(substr($this->whereProvincias, 0, 5), 'and') === false) {
                 $where .=  " and " . $this->whereProvincias;
             } 
            }
       // else {
        //         $where .=  $this->whereProvincias;
        //     }
        //         }

        //FIXME Se recogen si/si, no/no y faltaría si/no, no/si

        return $where;
    }

    function queryProvincias($params)
    {
        $campoProvincia = $this->fields[env('PARAM_PROVINCE')][0]['name'];
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')][0]['name'];
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')][0]['name'];
        if ($params['province_id'] == 'Tenerife' || $params['province_id'] == 'Las Palmas') {
            foreach ($this->fields[env('PARAM_PROVINCE')] as $pProv) {
                foreach (array_keys($pProv['type']) as $prov) {
                    if ($prov == $params['province_id']) {
                        $this->whereProvincias =   $this->surveyName . '.'
                            . $pProv['name']
                            . ' = \'' . $pProv['type'][$prov] . '\'';

                        if ($params['province_id'] == 'Las Palmas') {
                            $this->whereProvincias .= " and " . $this->surveyName . '.' . $campoCentroLpa . ' is not null';
                        }
                        if ($params['province_id'] == 'Tenerife') {
                            $this->whereProvincias .= " and " . $this->surveyName . '.' . $campoCentroTfe . ' is not null';
                        }
                    }
                }
            }
        }
        $qid = substr($campoProvincia, -2);
        $whereCond = $this->getWhere();

        if ($params['province_id'] != 'TODAS') {

            $totalProvincias = $this->icotSurvey->select(
                'select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total' .
                    ' from ' . $this->surveyName .
                    ' join lime_answers on lime_answers.code = ' . $this->surveyName . '.' . $campoProvincia . ' and lime_answers.qid = ' . $qid .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                    . $whereCond .
                    ' group by 1',
                $this->periodTime
            );
        } else {
            $auxWhere = $this->whereProvincias;
            $whereLpa = ' and ' . $this->surveyName . '.' . $campoCentroLpa . ' is not null';

            $this->whereProvinciasLpa = $auxWhere . $whereLpa;

            $this->whereProvincias = $auxWhere .  $this->whereProvinciasLpa;
            $whereCond = $this->getWhere();

            $totalProvinciasLpa = $this->icotSurvey->select(
                'select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total ' .
                    'from ' . $this->surveyName .
                    ' join lime_answers on lime_answers.code = ' . $this->surveyName . '.' . $campoProvincia . ' and lime_answers.qid  = ' . $qid .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                    . $whereCond .
                    ' group by 1',
                $this->periodTime
            );


            $whereTfe  = ' and ' . $this->surveyName . '.' . $campoCentroTfe . ' is not null';
            $this->whereProvinciasTfe = $auxWhere . $whereTfe;
            $this->whereProvincias = $auxWhere .  $this->whereProvinciasTfe;

            $whereCond = $this->getWhere();
            $totalProvinciasTfe = $this->icotSurvey->select(
                'select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total ' .
                    'from ' . $this->surveyName .
                    ' join lime_answers on lime_answers.code = ' . $this->surveyName . '.' . $campoProvincia . ' and lime_answers.qid  = ' . $qid .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                    . $whereCond .
                    ' group by 1',
                $this->periodTime
            );

            $this->whereProvincias =  $this->surveyName . '.' . $campoProvincia . ' is not null';

            $totalProvincias[] = $totalProvinciasLpa;
            $totalProvincias[] = $totalProvinciasTfe;
        }
        return $totalProvincias;
    }

    function querySexos($where)
    {
        $whereCond = $where;
        $campoSexo = $this->fields[env('PARAM_SEX')]['name'];
        $qid = substr($campoSexo, -2);


        $totalSexos = $this->icotSurvey->select(
            'select distinct lime_answers.answer as sexo, count(' . $this->surveyName . '.' . $campoSexo . ') as total ' .
                'from ' . $this->surveyName .
                ' join lime_answers on lime_answers.code = ' . $this->surveyName . '.' . $campoSexo . ' and lime_answers.qid = ' . $qid .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' group by 1',
            $this->periodTime
        );
        $totalSexo['Hombre'] = 0;
        $totalSexo['Mujer'] = 0;
        if (!empty($totalSexos)) {
            $totalSexo['Hombre'] = $totalSexos[0]->total;
            $totalSexo['Mujer']  = $totalSexos[1]->total;
        }
        return $totalSexo;
    }

    function queryEdades($where)
    {
        $whereCond = $where;
        $campoEdad = $this->fields[env('PARAM_AGE')]['name'];
        $qid = substr($campoEdad, -2);

        $totalEdades = $this->icotSurvey->select(
            'select distinct lime_answers.answer as edad, count(' . $this->surveyName . '.' . $campoEdad . ') as total
                                                    from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . $campoEdad . ' and lime_answers.qid = ' . $qid .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' group by 1',
            $this->periodTime
        );

        $tEdades = [];
        foreach ($totalEdades as $toEdad) {
            $colEdad = htmlentities($toEdad->edad);
            if (strpos($toEdad->edad, '<') !== false) {
                $colEdad =  'menor de ' . substr($toEdad->edad, 1);
            }
            if (strpos($toEdad->edad, '>') !== false) {
                $colEdad =  'mayor de ' . substr($toEdad->edad, 1);
            }
            $tEdades[] = (object) ['edad' => $colEdad, 'total' => $toEdad->total];
        }
        return $tEdades;
    }


    function queryServicios($where)
    {
        $servicesType = $this->getServices();
        $codetf = '72';
        // $codelp = '79';
        $codelp =[ '77', '78','79'];


        $whereCond = $where;
        $totalServiciosTF = $this->icotSurvey->select(
            'select distinct lime_answers.answer as servicio, count(' . $this->surveyName . '.' . '285213X7X' . $codetf . ') as total
                                                    from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . '285213X7X' . $codetf . ' and lime_answers.qid = ' . $codetf .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ?'  . $whereCond
                . ' group by 1',
            $this->periodTime
        );


        $totalServicesTF = [];
        foreach ($servicesType as $service) {
            $e = 0;
            foreach ($totalServiciosTF as $tservice) {
                if ($service == $tservice->servicio) {
                    $e = 1;
                    $totalServicesTF[] = (object) array('servicio' => $tservice->servicio, 'total' => $tservice->total);
                }
            }
            if ($e == 0) {
                $totalServicesTF[] = (object) array('servicio' => $service, 'total' => 0);
            }
        }

        $totalServiciosLP = $this->icotSurvey->select(
            'select distinct lime_answers.answer as servicio, count(' . $this->surveyName . '.' . '285213X7X' . $codelp[2] . ') as total
                                                    from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . '285213X7X' . $codelp[2] . ' and lime_answers.qid = ' . $codelp[2] .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '  . $whereCond
                . ' group by 1',
            $this->periodTime
        );

        $totalOtrosCentros =$this->icotSurvey->select(
            'select(select count(' . $this->surveyName . '.' . '285213X7X' . $codelp[0] . ')
            from  ' .  $this->surveyName .
' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . '  between ? and ?)+(select count(' . $this->surveyName . '.' . '285213X7X' . $codelp[1] . ')
from  ' .  $this->surveyName .
' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . '  between ? and ?)as otros',  $this->periodTime
        );


        $totalServicesLP = [];
        foreach ($servicesType as $service) {
            $e = 0;
            foreach ($totalServiciosLP as $tservice) {
                if ($service == $tservice->servicio) {
                    $e = 1;
                    $totalServicesLP[] = (object) array('servicio' => $tservice->servicio, 'total' => $tservice->total);
                }
            }
            if ($e == 0) {
                $totalServicesLP[] = (object) array('servicio' => $service, 'total' => 0);
            }
        }

        foreach ($totalServicesLP as $tservicelp) {
            if ($tservicelp->servicio== 'Otros'){
                $tservicelp-> total +=  $totalOtrosCentros[0]->otros;
            }

        }

        // $totalServicesLP[] = (object) array('servicio' => 'Otros', 'total' => $totalOtrosCentros[0]->otros);



        return ['Tenerife' => $totalServicesTF, 'Las Palmas' => $totalServicesLP];
    }

    function queryExperiencia($where)
    {
        $whereCond = $where;

        $totalExperiencia = $this->icotSurvey->select(
            'select distinct lime_answers.answer as experiencia, count(' . $this->surveyName . '.' . '285213X7X70' . ') as total
                                                            from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . '285213X7X70' . ' and lime_answers.qid = ' . '70' .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' group by 1',
            $this->periodTime
        );

        $totalExp['1º vez'] = 0;
        $totalExp['Ya ha estado'] = 0;
        if (!empty($totalExperiencia)) {
            $totalExp['1º vez'] = $totalExperiencia[0]->total;
            $totalExp['Ya ha estado']  = $totalExperiencia[1]->total;
        }
        return $totalExp;
    }

    function queryNetPromoterScore($where)
    {
        $whereCond = $where;
        $respuestas= ['A3','A2','A1'];
        $totalSatisfaccion['promotores'] =0;
        $totalSatisfaccion['pasivos'] =0;
        $totalSatisfaccion['detractores'] =0;
        $totalSatisfaccion['nps'] =0;

        $promotores = $this->icotSurvey->select(
            'select count(' . $this->surveyName . '.' . '285213X8X76SQ005' . ') as promotores
                                                            from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' and 285213X8X76SQ005="A5"',
            $this->periodTime
        );
        $pasivos = $this->icotSurvey->select(
            'select count(' . $this->surveyName . '.' . '285213X8X76SQ005' . ') as pasivos
                from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' and 285213X8X76SQ005="A4"',
            $this->periodTime
        );

        $detractores=0;
        foreach($respuestas as $rp){
            $totaldetractores =  $this->icotSurvey->select(
                'select count(' . $this->surveyName . '.' . '285213X8X76SQ005' . ') as detractores
                    from  ' .  $this->surveyName .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between :date1 and :date2 '
                    . $whereCond .
                    ' and 285213X8X76SQ005= :respuesta', ["date1"=>$this->periodTime[0],"date2"=>$this->periodTime[1],"respuesta"=> $rp]
            )
            ;

            $detractores+=$totaldetractores[0]->detractores;
            
        }


        $totalSatisfaccion['promotores'] = $promotores[0]->promotores;
        $totalSatisfaccion['pasivos'] = $pasivos[0]->pasivos;
        $totalSatisfaccion['detractores'] = $detractores;
        $totalSatisfaccion['nps'] =round(($promotores[0]->promotores- $detractores)/($promotores[0]->promotores+$pasivos[0]->pasivos+$detractores)*100,1);

        return $totalSatisfaccion;
    }

    function queryCentros($where)
    {
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')][0]['name'];
        $qid = substr($campoCentroLpa, -2);

        $centreLpa =  $this->icotSurvey->select('select distinct lime_answers.answer as centro
                                                    from lime_answers
                                                    where qid = ' . $qid);

        $auxWhere = $this->whereProvincias;
        $this->whereProvincias = $this->whereProvinciasLpa;
        $whereCond = $where;
        $totalCentreLpa = $this->icotSurvey->select(
            "select lime_answers.answer as centro ,COUNT(" . $this->surveyName . '.' . $campoCentroLpa . ") as total
                                                        from  " .  $this->surveyName .
                " JOIN lime_answers on lime_answers.code = " . $this->surveyName . '.' . $campoCentroLpa . " and lime_answers.qid = " . $qid .
                " where " .  $this->surveyName . "." . $this->fields[env('PARAM_DATE')]['name'] . " between ? and ? "
                . $whereCond
                . " group by 1",
            $this->periodTime
        );

        $totalCentLpa = [];
        foreach ($centreLpa as $cLpa) {
            $e = 0;


            foreach ($totalCentreLpa as $totCentLpa) {
                if ($cLpa->centro ==  $totCentLpa->centro) {
                    $e = 1;
                    $totalCentLpa[] = (object) array('centro' => $totCentLpa->centro, 'total' => $totCentLpa->total);
                }
            }
        }

        $this->whereProvincias = $this->whereProvinciasTfe;
        $whereCond = $where;
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')][0]['name'];

        $qid = substr($campoCentroTfe, -2);

        $centreTfe =  $this->icotSurvey->select('select distinct lime_answers.answer as centro
                                    from lime_answers
                                    where qid = ' . $qid);

        $totalCentreTfe = $this->icotSurvey->select("select replace(lime_answers.answer, 'ICOT', '') as centro ,COUNT(" . $this->surveyName . '.' . $campoCentroTfe . ") as total
                                        from  " .  $this->surveyName .
            " LEFT JOIN lime_answers on lime_answers.code= " . $this->surveyName . '.' . $campoCentroTfe . " and lime_answers.qid= " . $qid .
            " where " .  $this->surveyName . "." . $this->fields[env('PARAM_DATE')]['name'] . " between ? and ? "
            . $whereCond .
            " group by 1", $this->periodTime);

        $totalServices = [];
        foreach ($centreTfe as $service) {
            $e = 0;
            foreach ($totalCentreTfe as $totCenTfe) {
                if ($service->centro == 'ICOT' . $totCenTfe->centro) {
                    $e = 1;
                    $totalServices[] = (object) array('centro' => 'ICOT' . $totCenTfe->centro, 'total' => $totCenTfe->total);
                }
            }
            if ($e == 0) {
                $totalServices[] = (object) array('centro' => $service->centro, 'total' => 0);
            }
        }
        $this->whereProvincias = $auxWhere;
        return ['Tenerife' => $totalServices, 'Las Palmas' => $totalCentLpa];
    }

    function queryTipoPaciente($params)
    {

        $this->whereTipoPaciente = '';
        if ($params['patient_id'] != -1) {
            //var_dump($this->fields[env('PARAM_TYPECLIENT')]); die();
            $campoTPaciente = $this->fields[env('PARAM_TYPECLIENT')][0]['name'];

            // $this->whereTipoPaciente = 'and lime_answers.answer = \'' . $params['patient_id'] .'\' and ' .    $this->surveyName .'.'
            // .$campoTPaciente
            // . ' =  lime_answers.code';
            $valueTPaciente = '';
            foreach ($this->fields[env('PARAM_TYPECLIENT')] as $fieldTPaci) {
                if (array_keys($fieldTPaci['type'])[0] == $params['patient_id']) {
                    $valueTPaciente = array_values($fieldTPaci['type'])[0];
                    break;
                }
            }

            $this->whereTipoPaciente = $this->surveyName . '.' . $campoTPaciente . ' = \'' . $valueTPaciente . '\'';
        }
    }

//     function queryEncuestados ($params) {
//         $totalEncuestados =[];
// $totalEncuestados= $th



//         return $totalEncuestados;
//     }


    /**
     * Método desde el que se hace la descarga del informe
     */

    public function download(Request $request)
    {
        try {

            $datos = $this->getData($request);
            $render = view('preview_data', $datos)->render();
            $renderCover = view('cover')->render();
            $header = view()->make('header')->render();            

            $pdf = new Pdf;
            $pdf->addCover($renderCover);
            $pdf->setOptions([
                'javascript-delay' => 7000,
                'header-html' => $header,
                'header-line',
                'footer-right'     => "[page]",
                'footer-left'     => $datos['title'],
                'footer-font-size' => 10,
                'footer-line',
                'margin-top' => 25,
                'margin-bottom' => 15,

            ]);
            $pdf->addPage($render);
            $dataPDF = public_path('report.pdf');
            if (!$pdf->saveAs($dataPDF)) {
                $error = $pdf->getError();
                return response()->json([
                    'success' => false, 'mensaje' => $error
                ], 404);
            } else {
                $pdf->saveAs($dataPDF);
                return response()->download($dataPDF);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Método desde el que se carga la preview del informe
     */
    public function preview(Request $request)
    {
        try {
            $datos = $this->getData($request);
            $render = view('preview_data', $datos)->render();
            // return view('preview_data', $datos);
            return $render;
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Método que obtiene todos los datos que se pasan a la preview/download del informe estadístico
     */
    public function getData(Request $request)
    {
        try {

            $params = $request->all();
            /**
             * Obtener parametros según ID de encuesta
             */
            $surveyFields = DB::select('select *
                                        from params
                                        where survey_id = ?', [$params['survey_id']]);

            $this->icotSurvey = DB::connection('icotsurvey'); //Forzamos conexion con survey (datos encuestas)
            $this->surveyName = 'lime_survey_' . $params['survey_id'];
            $this->fields = $this->getFieldsSurvey($surveyFields);
            $this->queryTipoPaciente($params);

            // $where = $this->getWhere();


            // Filtros que recibimos
            /**
             * Encuesta
             * Fecha
             * Provincia
             * Tipo de paciente
             *
             */
            $orgPeriod = $params['startDate'] . ' al ' . $params['endDate'];
            $params['startDate'] = str_replace('/', '-', $params['startDate']);
            $params['endDate'] = str_replace('/', '-', $params['endDate']);
            $params['startDate'] = date("Y-m-d", strtotime($params['startDate']));
            $params['endDate'] = date("Y-m-d", strtotime($params['endDate']));

            $this->periodTime = [$params['startDate'],  $params['endDate'], $params['startDate'],  $params['endDate']];

            //Parametros:
            // -- Provincia (C1 Tenerife / C2 Las Palmas)
            $totalProvincias = $this->queryProvincias($params);
            $totalProvincia = [];
            foreach ($totalProvincias as $tProv) {
                if (is_array($tProv)) {
                    foreach ($tProv as $prov) {
                        $totalProvincia[$prov->provincia] = $prov->total;
                    }
                } else {
                    $totalProvincia[$tProv->provincia] = $tProv->total;
                }
            }

            $totalEncuestados = 0;
            if ($params['province_id'] == 'Tenerife' && isset($totalProvincia['Provincia de Tenerife'])) {
                $totalEncuestados = $totalProvincia['Provincia de Tenerife'];
            }
            if ($params['province_id'] == 'Las Palmas' && isset($totalProvincia['Provincia de Las Palmas'])) {
                $totalEncuestados = $totalProvincia['Provincia de Las Palmas'];
            }
            if ($params['province_id'] == 'TODAS') {
                $totalEncuestados += isset($totalProvincia['Provincia de Las Palmas']) ? $totalProvincia['Provincia de Las Palmas']  : $totalEncuestados;
                $totalEncuestados += isset($totalProvincia['Provincia de Tenerife']) ? $totalProvincia['Provincia de Tenerife']  : $totalEncuestados;
            }
            if ($totalEncuestados == 0) {
                return response()->json([
                    'success' => 'false',
                    'mensaje'  => 'SIN RESULTADOS'
                ], 400);
            }

            $where = $this->getWhere();

            $totalSexos = $this->querySexos($where);
            $totalEdades = $this->queryEdades($where);
            $totalExperiencia = $this->queryExperiencia($where);
            $totalCentros = $this->queryCentros($where);
            $totalServicios = $this->queryServicios($where);
            $totalSatisfaccion = $this->queryNetPromoterScore($where);

            $preguntas = $this->icotSurvey->select('select distinct concat( \'PREGUNTA\', \'  \', substr(q.title, 5, 1), \':\') AS n_pregunta, q.question,  substr(q.title, 5, 1) as pregunta
                                                    from lime_questions q
                                                    where q.title like \'SQ%\' and q.sid = ' . $params['survey_id'] . '
                                                    and q.title not like \'SQ006%\'
                                                    order by q.qid
                                                    ');


            foreach ($preguntas as $pregunta) { 
                $campoPregunta =   $this->fields[env('PARAM_QUESTION' . $pregunta->pregunta)]['name'];
                $qid = substr($campoPregunta, 9, 2);
                $whereCond = $where;
                $porcentPreg[$pregunta->pregunta] = $this->icotSurvey->select("select lime_answers.code, COUNT(lime_survey_" . $params['survey_id'] . ".`" . $campoPregunta . "`) as total
                    , COUNT(lime_survey_" . $params['survey_id'] . ".`" . $campoPregunta . "`) * 100 /  " . $totalEncuestados . " as percent_total
                                        from lime_survey_" . $params['survey_id'] . "
                                        JOIN lime_answers on lime_answers.code=lime_survey_" . $params['survey_id'] . ".`" . $campoPregunta . "` and lime_answers.qid= " . $qid .
                    " where " .  $this->surveyName . "." . $this->fields[env('PARAM_DATE')]['name'] . " between ? and ?
                                        and (lime_answers.code = 'A4' or lime_answers.code = 'A5')"
                    . $whereCond .
                    " group by 1", $this->periodTime);
            }
            foreach ($preguntas as $pregunta) {
                $total = 0;
                if (isset($porcentPreg[$pregunta->pregunta][0])) {
                    $total += $porcentPreg[$pregunta->pregunta][0]->percent_total;
                }
                if (isset($porcentPreg[$pregunta->pregunta][1])) {
                    $total += $porcentPreg[$pregunta->pregunta][1]->percent_total;
                }
                $porcentPreg[$pregunta->pregunta] = round($total, 1);
            }

            $data = array(
                'title'               => 'INFORME ENCUESTAS',
                'period'              => $orgPeriod,
                'totalProvincia'      => $totalProvincia,
                'totalProvincias'      => $totalProvincias,
                'totalSexo'           => $totalSexos,
                'totalEdad'           => $totalEdades,
                'totalExp'            => $totalExperiencia,
                'totalSatisfaccion'   => $totalSatisfaccion,
                'totalServiciosTF'    => $totalServicios['Tenerife'],
                'totalServiciosLPA'   => $totalServicios['Las Palmas'],
                'preguntas'           => $preguntas,
                'params'              => $params,
                'totalCentreLpa'      => $totalCentros['Las Palmas'],
                'totalCentreTfe'      => $totalCentros['Tenerife'],
                'porcentPreg'         => $porcentPreg,
                'totalEncuestados'    => $totalEncuestados

            );

            return $data;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }


}

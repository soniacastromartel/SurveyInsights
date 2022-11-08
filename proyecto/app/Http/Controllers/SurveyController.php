<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use mikehaertl\wkhtmlto\Pdf;
use mikehaertl\tmp\File;
use Mail;
use App\Mail\ReportDelivered;
use PhpParser\Node\Stmt\TryCatch;
// use PDF;

class SurveyController extends BaseController
{
    protected $icotSurvey;
    protected $surveyName;
    protected $periodTime;
    protected $totalEncuestados;
    protected $fields;
    protected $whereProvincias;
    protected $whereProvinciasLpa;
    protected $whereProvinciasTfe;
    protected $whereTipoPaciente;
    protected $preguntas;

    public function index()
    {
        $this->icotSurvey = DB::connection('icotsurvey');
        $surveys = $this->icotSurvey->select('select sid, name
                                    from lime_surveys
                                    where expires is null and name is not null');
        // $patientsType = [
        //     "Servicio Canario de la Salud", "Laboral/Diversos", "Seguro Médico", "Accidente Tráfico", "Privado"
        // ];

        $patientsType = $this->getPatientsTypeName
();
        $servicesType = $this->getServices();

        return view('surveys', [
            'title'        => 'ENCUESTAS DE SATISFACCIÓN', 'provinces'    => ['Las Palmas', 'Tenerife'], 'surveys'      => $surveys, 'patientsType' => $patientsType, 'servicesType' => $servicesType
        ]);
    }

    /**Function que coge parametros de la encuesta de icotSurvey 
     * param $surveyFields (parámetros de la tabla 'params' de la base de datos local)
     */
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

    /**
     * Obtener nombres de los servicios
     */
    public function getServices()
    {
        try {
            $servicesNames = [];
            $service = $this->icotSurvey->select(
                'select lime_answers.answer as servicio from  lime_answers where lime_answers.qid = ' . '72'
            );
            foreach ($service as $s) {
                $servicesNames[] = $s->servicio;
            }
            return $servicesNames;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }
    /**
     * Obtener nombres de los tipos de asistencia
     */
    public function getPatientsTypeName()
    {
        try {
            $patientTypes = $this->icotSurvey->select(
                'select lime_answers.answer as type, lime_answers.code as code from  lime_answers where lime_answers.qid = ' . '310'
            );
            return $patientTypes;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Obtener nombres de las compañías
     * param $request
     */
    public function getCompanies(Request $request)
    {
        $params = $request->all();
        $companyNames = [];
        $this->icotSurvey = DB::connection('icotsurvey'); //Forzamos conexion con survey (datos encuestas)
        try {
            $company = $this->icotSurvey->select(
                'select lime_answers.answer as name from  lime_answers where lime_answers.qid = ' . $params['code'] . ''

            );
            foreach ($company as $c) {
                $companyNames[] = $c->name;
            }
            return $companyNames;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener cláusula where dependiendo 
     * de parámetros seleccionados en los 
     * selectores
     */
    public function getWhere()
    {
        $where = '';

        if (!empty($this->whereTipoPaciente) && !empty($this->whereProvincias)) { // si,si
            $where .= " and " . $this->whereTipoPaciente;
            if (strpos(substr($where, 0, 5), 'and') === false || strpos(substr($this->whereProvincias, 0, 5), 'and') === false) {
                $where .= " and " . $this->whereProvincias;
            } else {
                $where .=  $this->whereProvincias;
            }
        } else if (empty($this->whereTipoPaciente) && empty($this->whereProvincias)) { // no, no
            $where .=  " ";
        }
        // else if (!empty($this->whereTipoPaciente) && empty($this->whereProvincias)){//si,no
        //     $where .= " and " . $this->whereTipoPaciente;
        // }
        else if (empty($this->whereTipoPaciente) && !empty($this->whereProvincias)) { //si,no
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

    //TODO por implementar -> obtener total de encuestas por tipo de asistencia
    function queryPatientsTypeTotal($where)
    {
        try {
            // 'select ls.`891295X38X310` as tipo , count(ls.`891295X38X310`) as total from icotsurvey.lime_survey_891295 ls where ls.`891295X38X310`  is not null  group by ls.`891295X38X310`'
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
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
        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoProvincia, -3);
        } else {
            $qid = substr($campoProvincia, -2);
        }

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

    /**
     * Obtener datos por género
     * param $where (cláusula where)
     */
    function querySexos($where)
    {
        $whereCond = $where;
        $campoSexo = $this->fields[env('PARAM_SEX')]['name'];
        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoSexo, -3);
        } else {
            $qid = substr($campoSexo, -2);
        }
        try {
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
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener datos de edad
     * param $where (cláusula where)
     */
    function queryEdades($where)
    {
        $whereCond = $where;
        $campoEdad = $this->fields[env('PARAM_AGE')]['name'];
        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoEdad, -3);
        } else {
            $qid = substr($campoEdad, -2);
        }

        try {
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
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    function queryCompanies ($where){
        try {



        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener datos de servicios requeridos
     * param $where (cláusula where)
     */
    function queryServicios($where)
    {
        $servicesType = $this->getServices();

        if ($this->surveyName == 'lime_survey_891295') {
            $surveyCode = '891295X38X';
            $codetf = '309';
            $codelp = ['314', '315', '316'];
        } else {
            $surveyCode = '285213X7X';
            $codetf = '72';
            $codelp = ['77', '78', '79'];
        }

        $whereCond = $where;
        $totalServiciosTF = $this->icotSurvey->select(
            'select distinct lime_answers.answer as servicio, count(' . $this->surveyName . '.' . $surveyCode . $codetf . ') as total
                                                    from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . $surveyCode . $codetf . ' and lime_answers.qid = ' . $codetf .
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
            'select distinct lime_answers.answer as servicio, count(' . $this->surveyName . '.' . $surveyCode . $codelp[2] . ') as total
                                                    from  ' .  $this->surveyName .
                '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . $surveyCode . $codelp[2] . ' and lime_answers.qid = ' . $codelp[2] .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '  . $whereCond
                . ' group by 1',
            $this->periodTime
        );

        $totalOtrosCentros = $this->icotSurvey->select(
            'select(select count(' . $this->surveyName . '.' . $surveyCode . $codelp[0] . ')
            from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . '  between ? and ?)+(select count(' . $this->surveyName . '.' . $surveyCode . $codelp[1] . ')
from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . '  between ? and ?)as otros',
            $this->periodTime
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
            if ($tservicelp->servicio == 'Otros') {
                $tservicelp->total +=  $totalOtrosCentros[0]->otros;
            }
        }

        // $totalServicesLP[] = (object) array('servicio' => 'Otros', 'total' => $totalOtrosCentros[0]->otros);



        return ['Tenerife' => $totalServicesTF, 'Las Palmas' => $totalServicesLP];
    }


    /**
     * Obtener datos de antigüedad
     * param $where (cláusula where)
     */
    function queryExperiencia($where)
    {
        $whereCond = $where;
        $campoExperiencia = $this->fields[env('PARAM_EXPERIENCE')]['name'];
        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoExperiencia, -3);
        } else {
            $qid = substr($campoExperiencia, -2);
        }

        try {
            $totalExperiencia = $this->icotSurvey->select(
                'select distinct lime_answers.answer as experiencia, count(' . $this->surveyName . '.' . $campoExperiencia . ') as total
                                                                from  ' .  $this->surveyName .
                    '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . $campoExperiencia . ' and lime_answers.qid = ' . $qid .
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
         } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }

        
    }

    /**
     * Obtener Net Promoter Score
     * param $where (cláusula where)
     */
    function queryNetPromoterScore($where)
    {
        $whereCond = $where;
        $respuestas = ['A3', 'A2', 'A1'];
        $totalSatisfaccion['promotores'] = 0;
        $totalSatisfaccion['pasivos'] = 0;
        $totalSatisfaccion['detractores'] = 0;
        $totalSatisfaccion['nps'] = 0;
        $question = 'Q005';

        if ($this->surveyName == 'lime_survey_891295') {
            $surveyCode = '891295X39X313S';
        } else {
            $surveyCode = '285213X8X76S';
        }

        $promotores = $this->icotSurvey->select(
            'select count(' . $this->surveyName . '.' . $surveyCode . $question . ') as promotores
                                                            from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' and ' .  $surveyCode . $question . '="A5"',
            $this->periodTime
        );
        $pasivos = $this->icotSurvey->select(
            'select count(' . $this->surveyName . '.' .  $surveyCode . $question . ') as pasivos
                from  ' .  $this->surveyName .
                ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                . $whereCond .
                ' and ' .  $surveyCode . $question . '="A4"',
            $this->periodTime
        );

        $detractores = 0;
        foreach ($respuestas as $rp) {
            $totaldetractores =  $this->icotSurvey->select(
                'select count(' . $this->surveyName . '.' . $surveyCode . $question . ') as detractores
                    from  ' .  $this->surveyName .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between :date1 and :date2 '
                    . $whereCond .
                    ' and ' .  $surveyCode . $question . '= :respuesta',
                ["date1" => $this->periodTime[0], "date2" => $this->periodTime[1], "respuesta" => $rp]
            );

            $detractores += $totaldetractores[0]->detractores;
        }


        $totalSatisfaccion['promotores'] = $promotores[0]->promotores;
        $totalSatisfaccion['pasivos'] = $pasivos[0]->pasivos;
        $totalSatisfaccion['detractores'] = $detractores;
        $totalSatisfaccion['nps'] = round(($promotores[0]->promotores - $detractores) / ($promotores[0]->promotores + $pasivos[0]->pasivos + $detractores) * 100, 1);

        return $totalSatisfaccion;
    }

    function queryCentros($where)
    {
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')][0]['name'];
        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoCentroLpa, -3);
        } else {
            $qid = substr($campoCentroLpa, -2);
        }

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

        if ($this->surveyName == 'lime_survey_891295') {
            $qid = substr($campoCentroTfe, -3);
        } else {
            $qid = substr($campoCentroTfe, -2);
        }

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

    /**
     * Obtener tipo de paciente/asistencia
     * param $params (parámetros de la Request)
     */
    function queryPatientType($params)
    {

        $this->whereTipoPaciente = '';
        if ($params['patient_id'] != -1) {
            $campoTPaciente = $this->fields[env('PARAM_TYPECLIENT')][0]['name'];

            // $this->whereTipoPaciente = 'and lime_answers.answer = \'' . $params['patient_id'] .'\' and ' .    $this->surveyName .'.'
            // .$campoTPaciente
            // . ' =  lime_answers.code';
            $valueTPaciente = '';
            foreach ($this->fields[env('PARAM_TYPECLIENT')] as $fieldTPaci) {
                if (array_values($fieldTPaci['type'])[0] == $params['patient_id']) {
                    $valueTPaciente = array_values($fieldTPaci['type'])[0];
                    break;
                }
            }

            $this->whereTipoPaciente = $this->surveyName . '.' . $campoTPaciente . ' = \'' . $valueTPaciente . '\'';
        }
    }

    /**
     * Obtener cantidad de encuestas por mes
     * param $where (cláusula where)
     */
    function querySurveyQuantity($where)
    {
        try {
            $whereCond = $where;

            $totalSurveys = $this->icotSurvey->select(
                'select distinct month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') as fecha, count(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') as total,
                CASE 
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 1 then "enero"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 2 then "febrero"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 3 then "marzo"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 4 then "abril"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 5 then "mayo"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 6 then "junio"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 7 then "juliio"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 8 then "agosto"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 9 then "septiembre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 10 then "octubre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 11 then "noviembre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 12 then "diciembre"
                end as mes
                from  ' .  $this->surveyName .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                    . $whereCond .
                    ' group by 1',
                $this->periodTime
            );
            return $totalSurveys;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener total de encuestados
     * param $provinceId (parámetro provinceId de la Request)
     * param $totalProvincia (total de encuestas por provincia)
     */
    function queryEncuestados($provinceId, $totalProvincia)
    {
        $totalEncuestados = 0;
        if ($provinceId == 'Tenerife' && isset($totalProvincia['Provincia de Tenerife'])) {
            $totalEncuestados = $totalProvincia['Provincia de Tenerife'];
        }
        if ($provinceId == 'Las Palmas' && isset($totalProvincia['Provincia de Las Palmas'])) {
            $totalEncuestados = $totalProvincia['Provincia de Las Palmas'];
        }
        if ($provinceId == 'TODAS') {
            $totalEncuestados += isset($totalProvincia['Provincia de Las Palmas']) ? $totalProvincia['Provincia de Las Palmas']  : $totalEncuestados;
            $totalEncuestados += isset($totalProvincia['Provincia de Tenerife']) ? $totalProvincia['Provincia de Tenerife']  : $totalEncuestados;
        }
        if ($totalEncuestados == 0) {
            return response()->json([
                'success' => 'false',
                'mensaje'  => 'SIN RESULTADOS'
            ], 400);
        }

        return $totalEncuestados;
    }


    /**
     * Obtener epígrafes de las preguntas
     * param $id (parámetro surveyId de la Request)
     */
    function queryPreguntas($id)
    {
        try {
            $preguntas = $this->icotSurvey->select('select distinct concat( \'PREGUNTA\', \'  \', substr(q.title, 5, 1), \':\') AS n_pregunta, q.question,  substr(q.title, 5, 1) as pregunta
            from lime_questions q
            where q.title like \'SQ%\' and q.sid = ' . $id . '
            and q.title not like \'SQ006%\'
            order by q.qid
            ');

            return $preguntas;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener Porcentajes de satisfacción por pregunta
     * param $where (cláusula where)
     * param $id (parámetro surveyId de la Request)
     */
    function queryPercents($where, $id)
    {
        $preguntas = $this->queryPreguntas($id);
        // $totalEncuestados= 

        foreach ($preguntas as $pregunta) {
            $campoPregunta =   $this->fields[env('PARAM_QUESTION' . $pregunta->pregunta)]['name'];

            if ($this->surveyName == 'lime_survey_891295') {
                $qid = substr($campoPregunta, 10, 3);
            } else {
                $qid = substr($campoPregunta, 9, 2);
            }

            $porcentPreg[$pregunta->pregunta] = $this->icotSurvey->select("select lime_answers.code, COUNT(lime_survey_" . $id . ".`" . $campoPregunta . "`) as total
                , COUNT(lime_survey_" . $id . ".`" . $campoPregunta . "`) * 100 /  " . $this->totalEncuestados . " as percent_total
                                    from lime_survey_" . $id . "
                                    JOIN lime_answers on lime_answers.code=lime_survey_" . $id . ".`" . $campoPregunta . "` and lime_answers.qid= " . $qid .
                " where " .  $this->surveyName . "." . $this->fields[env('PARAM_DATE')]['name'] . " between ? and ?
                                    and (lime_answers.code = 'A4' or lime_answers.code = 'A5')"
                . $where .
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

        return $porcentPreg;
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
            $this->queryPatientType($params);

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

            $this->totalEncuestados = $this->queryEncuestados($params['province_id'],  $totalProvincia);

            $where = $this->getWhere();

            $totalSexos = $this->querySexos($where);
            $totalEdades = $this->queryEdades($where);
            $totalExperiencia = $this->queryExperiencia($where);
            $totalCentros = $this->queryCentros($where);
            $totalServicios = $this->queryServicios($where);
            $totalSatisfaccion = $this->queryNetPromoterScore($where);
            $totalSurveys = $this->querySurveyQuantity($where);
            $preguntas = $this->queryPreguntas($params['survey_id']);
            $porcentPreg = $this->queryPercents($where, $params['survey_id']);

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
                'totalEncuestados'    => $this->totalEncuestados,
                'totalEncuestas'        => $totalSurveys

            );

            return $data;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Método que genera el PDF del informe
     */
    public function generatePDF(Request $request)
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
                // 'header-spacing' => 10,
                'header-line',
                'footer-right'     => "[page]",
                'footer-left'     => $datos['title'],
                'footer-font-size' => 10,
                // 'footer-spacing' => 10,
                'footer-line',
                'margin-top' => 25,
                'margin-bottom' => 15,

            ]);
            $pdf->addPage($render);

            return $pdf;
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }


    /**
     * Método desde el que se hace la descarga del informe
     */

    public function download(Request $request)
    {
        try {
            $pdf = $this->generatePDF($request);
            $dataPDF = env('PUBLIC_PATH');
            if (!$pdf->saveAs($dataPDF)) {
                $error = $pdf->getError();
                return response()->json([
                    'success' => false, 'mensaje' => '$error'
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
     * @param Request $request
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
     * Método desde el que se envía el informe
     * @param Request $request
     */
    public function mail(Request $request)
    {
        try {
            $file = $this->generatePDF($request)->toString();

            // $message->to(...$emails)

            $emailData = $request->all();
            $emailData['view']    = 'emails.delivered_report';
            $emailsTo = explode(';', $emailData['to']);
            $emailData['file'] = $file;



            /**
             * ENVIAR CORREO
             */

            //  foreach($emailsTo as $email){
            //     Mail::to($email)
            //     ->send(new ReportDelivered($emailData));
            //  }
            Mail::to($emailsTo)
                // ->cc($emailData['to'])
                ->send(new ReportDelivered($emailData));
            return $this->sendResponse([], env('REQUESTED'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use mikehaertl\wkhtmlto\Pdf;
use mikehaertl\tmp\File;
use Mail;
use App\Mail\ReportDelivered;
use App\Models\LimeAnswer;
use Illuminate\Support\Carbon;

use App\Models\LimeSurvey;
use App\Models\CurrentSurvey;
use App\Models\LimeQuestions;
use App\Models\Params;
use Exception;
use Hamcrest\Arrays\IsArray;

// use PDF;

class SurveyController extends BaseController
{
    // protected $icotSurvey;
    // protected $encuestas;
    protected $surveyName;
    protected $periodTime;
    protected $totalEncuestados;
    protected $fields;
    protected $whereProvincias;
    protected $whereProvinciasLpa;
    protected $whereProvinciasTfe;
    protected $whereTipoPaciente;
    protected $whereCentre;
    protected $whereCompany;
    protected $preguntas;
    protected $questions;

    public function index()
    {
        try {
            $surveys = LimeSurvey::getLastSurvey();
            $surveyFields = CurrentSurvey::getCurrentSurveyFields();
            $this->questions =  LimeQuestions::getQuestionsBySurveyId($surveys->sid);
            $this->fields = $this->getFieldsSurvey($surveyFields);
            $campoTrafico = $this->fields[env('PARAM_TRA')]['field'];
            $campoDiverso = $this->fields[env('PARAM_DIV')]['field'];
            $campoSalud = $this->fields[env('PARAM_SAL')]['field'];
            $centreTfe = $this->fields[env('PARAM_CENTRE_TFE')]['field'];
            $centreLpa = $this->fields[env('PARAM_CENTRE_LPA')]['field'];
            $allCompanies = [$campoTrafico, $campoDiverso, $campoSalud];
            $allCompaniesCodes =  [$this->fields[env('PARAM_TRA')]['code'], $this->fields[env('PARAM_DIV')]['code'], $this->fields[env('PARAM_SAL')]['code']];
            $allCentres = [$centreLpa, $centreTfe];
            $allCentresCodes = [$this->fields[env('PARAM_CENTRE_TFE')]['code'], $this->fields[env('PARAM_CENTRE_LPA')]['code']];
            $patientsType = LimeAnswer::getNames($this->fields[env('PARAM_TYPECLIENT')]['code']);
            $servicesType = $this->queryServicesNames($this->fields[env('PARAM_SERVICE')]['code']);
            $provinces = LimeAnswer::getNames($this->fields[env('PARAM_PROVINCE')]['code']);
            $companies = $this->getAll($allCompanies, $allCompaniesCodes);
            $centres = $this->getAll($allCentres, $allCentresCodes);
            return view('surveys', [
                'title'        => 'ESTADÍSTICAS ICOT',
                'provinces'    => $provinces,
                'surveys'      => $surveys,
                'patientsType' => $patientsType,
                'servicesType' => $servicesType,
                'companies' => $companies,
                'allCompanies' => $allCompanies,
                'allCentres' => $allCentres,
                'centres' => $centres,
            ]);
        } catch (Exception $e) {
            return response()->json(
                [
                    'success' => 'false',
                    'errors'  => $e->getMessage(),
                ],
                400
            );
        }
    }


    /**
     * Obtener tipo de paciente/asistencia para claúsula where
     * param $params (parámetros de la Request)
     */
    function getPatientType($params)
    {
        $this->whereTipoPaciente = '';
        if ($params['patient_id'] != -1) {
            $campoTPaciente = $this->fields[env('PARAM_TYPECLIENT')]['field'];
            $valueTPaciente = $params['patient_id'];
            // foreach ($this->fields[env('PARAM_TYPECLIENT')] as $fieldTPaci) {
            //     if (array_values($fieldTPaci['code'])[0] == $params['patient_id']) {
            //         $valueTPaciente = array_values($fieldTPaci['code'])[0];
            //         break;
            //     }
            // }
            $this->whereTipoPaciente = $this->surveyName . '.' . $campoTPaciente . ' = \'' . $valueTPaciente . '\'';
        }
    }

    /**
     * Obtener el campo que filtra por centro dependiendo del valor del selector de provincia
     */
    function getCampoCentre($params)
    {
        if ($params['province_id'] == 'C1') {
            $campoCentre = $this->fields[env('PARAM_CENTRE_TFE')]['field'];
        } else if ($params['province_id'] == 'C2') {
            $campoCentre = $this->fields[env('PARAM_CENTRE_LPA')]['field'];
        }
        return $campoCentre;
    }

    /**
     * Obtener la línea el parámetro que filtra por linea de asistencia
     */
    function getCampoCompany($params)
    {
        switch ($params['patient_id']) {
            case 'T1':
                $campoCompany = $this->fields[env('PARAM_TRA')]['field'];
                break;
            case 'T2':
                $campoCompany = $this->fields[env('PARAM_SAL')]['field'];
                break;
            case 'T3':
                $campoCompany = $this->fields[env('PARAM_DIV')]['field'];
                break;
            default:
                break;
        }
        if ($params['company'] == '0') {
            $campoCompany = $campoCompany . 'other';
        }
        return $campoCompany;
    }

    // function setCampoCompany($params)
    // {
    //     $input_string = $params['company'];
    //     $prefixes = ['TRA', 'DIV', 'SAL'];
    //     $prefix_value =$this-> check_string_prefix($input_string, $prefixes);

    //     if ($prefix_value === 'TRA') {
    //         $campoCompany = $this->fields[env('PARAM_TRA')]['name'];
    //     } elseif ($prefix_value === 'DIV') {
    //         $campoCompany = $this->fields[env('PARAM_DIV')]['name'];
    //     } elseif ($prefix_value === 'SAL') {
    //         $campoCompany = $this->fields[env('PARAM_SAL')]['name'];

    //     }

    //     return $campoCompany;
    // }


    /*Obtener compañía para claúsula Where
    * param $params (parámetros de la Request)
    */
    function getCompanies($params)
    {
        try {
            $this->whereCompany = '';
            if ($params['company'] != -1) {
                $campoCompany = $this->getCampoCompany($params);
                if ($params['company'] != '0') {
                    if (is_array($campoCompany)) {
                        foreach ($campoCompany as $company) {
                            $where[] = $this->surveyName . '.' . $company['column'] . ' = \'' . $company['code'] . '\'';
                        }

                        if (is_array($where)) {
                            $this->whereCompany = implode(' or ', $where);
                            $this->whereCompany = '(' . $this->whereCompany . ')';
                        }
                    } else {
                        $this->whereCompany = $this->surveyName . '.' . $campoCompany . ' = \'' . $params['company'] . '\'';
                    }
                } else if ($params['company'] == '0') {
                    $this->whereCompany = $this->surveyName . '.' . $campoCompany . ' is not null';
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /*Obtener centro para claúsula Where
    * param $params (parámetros de la Request)
    */
    function getCentre($params)
    {
        try {
            $this->whereCentre = '';
            if ($params['centre'] != -1) {
                $campoCentre = $this->getCampoCentre($params);
                $this->whereCentre = $this->surveyName . '.' . $campoCentre . ' = \'' . $params['centre'] . '\'';
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }


    /**Function que coge parametros de la encuesta de icotSurvey
     * param $surveyFields (parámetros de la tabla 'params' de la base de datos local)
     */
    public function getFieldsSurvey($surveyFields)
    {
        $fields = [];
        $fields[env('PARAM_DATE')]   = ['name' =>  env('PARAM_DATE')];
        foreach ($surveyFields as $key => $sf) {
            $fields[$sf->question]   = [
                'field' =>  $sf->field,
                'code' =>  $sf->code
            ];
            // if (!empty($sf->type)) {
            //     $fields[$sf->name][] = ['question' =>  $sf->question, 'type' => [$sf->field]];
            // } else {
            //     $fields[$sf->question]   = ['field' =>  $sf->field];
            // }
        }
        return $fields;
    }

    /**
     * Obtener cláusula where dependiendo
     * de parámetros seleccionados en los
     * selectores
     */
    public function getWhere()
    {
        $where = '';

        if (!empty($this->whereProvincias)) {
            if (!empty($where)) {
                $where .= " and ";
            }
            $where = $this->whereProvincias;
        }
        if (!empty($this->whereTipoPaciente)) {
            if (!empty($where)) {
                $where .= " and ";
            }
            $where .= $this->whereTipoPaciente;
        }
        if (!empty($this->whereCompany)) {
            if (!empty($where)) {
                $where .= " and ";
            }
            $where .= $this->whereCompany;
        }
        if (!empty($this->whereCentre)) {
            if (!empty($where)) {
                $where .= " and ";
            }
            $where .= $this->whereCentre;
        }
        $where = $this->checkRepeatedWords($where, 'and and', ' and');
        return $where;
    }

    /**
     * Obtener total de encuestados
     * param $provinceId (parámetro provinceId de la Request)
     * param $totalProvincia (total de encuestas por provincia)
     */
    function getEncuestados($provinceId, $totalProvincia)
    {
        $totalEncuestados = 0;
        if ($provinceId == 'Provincia de Tenerife' && isset($totalProvincia['Provincia de Tenerife'])) {
            $totalEncuestados = $totalProvincia['Provincia de Tenerife'];
        }
        if ($provinceId == 'Provincia de Las Palmas' && isset($totalProvincia['Provincia de Las Palmas'])) {
            $totalEncuestados = $totalProvincia['Provincia de Las Palmas'];
        }
        if ($provinceId == 'TODAS') {
            $totalEncuestados += isset($totalProvincia['Provincia de Las Palmas']) ? $totalProvincia['Provincia de Las Palmas']  : $totalEncuestados;
            $totalEncuestados = isset($totalProvincia['Provincia de Tenerife']) ? $totalEncuestados + $totalProvincia['Provincia de Tenerife']  : $totalEncuestados;
        }
        if ($totalEncuestados == 0) {
            return response()->json([
                'success' => 'false',
                'mensaje'  => 'SIN RESULTADOS'
            ], 400);
        }

        return $totalEncuestados;
    }


    //METODOS QUERY

    /**
     * Obtener nombres de los servicios
     */
    public function queryServicesNames($qid)
    {
        try {
            $servicesNames = [];
            $service = LimeAnswer::getNames($qid);

            foreach ($service as $s) {
                $servicesNames[] = $s->answer;
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
     * Obtener nombres de las compañías
     * param $request
     */
    public function queryCompaniesNames(Request $request)
    {
        $params = $request->all();
        try {
            foreach ($params as $param) {
                $company[] = LimeAnswer::getNames($param)->toArray();
            }
            $companiesList = [];

            foreach ($company as $eachCompany) {
                foreach ($eachCompany as $each) {
                    $companiesList[] = $each;
                }
            }
            // $company = LimeAnswer::getNames($params['code']);
            $companiesList = collect($companiesList)
                ->unique('answer')
                ->sortBy('answer')
                ->values()
                ->all();
            return $companiesList;
            // return $company;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener planes de asistencia y total de encuesta por plan
     * params $where - cláusula where
     * */
    function queryCarePlan($where)
    {
        $campoTipoCliente = $this->fields[env('PARAM_TYPECLIENT')]['field'];
        $qid = $this->fields[env('PARAM_TYPECLIENT')]['code'];
        $alias = 'tipo';
        try {
            $totalCarePlans = CurrentSurvey::getTotalResults($qid, $campoTipoCliente, $alias, $where, $this->periodTime);
            return $totalCarePlans;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener nombre y total de las companías recogidas al seleccionar 'Otros' en el selector de companías
     * */
    function queryOtherCompanies($params, $where)
    {
        try {
            if ($params['company'] == 0 && $params['patient_id'] == -1) {
                $otherCompaniesCodes = $this->getCampoCompany($params);
                foreach ($otherCompaniesCodes as $other) {
                    $campoOtherCompanies = $this->surveyName . '.' . $other;
                    $otherCompanies[] = CurrentSurvey::getOtherCompanies($campoOtherCompanies, $where, $this->periodTime);
                }
            } else {
                $campoOtherCompanies = $this->surveyName . '.' . $this->getCampoCompany($params);
                $otherCompanies = CurrentSurvey::getOtherCompanies($campoOtherCompanies, $where, $this->periodTime);
            }

            return $otherCompanies;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener las 10 compañias con más resultados filtrando por línea de negocios
     * params $params parametros de la request
     */
    function queryCompaniesTotal($params, $where)
    {
        $isCompany = true;
        $campoCompany = $this->getCampoCompany($params);
        $alias = 'company';
        $qid = substr($campoCompany, -3);
        try {
            $companiesTotal =  CurrentSurvey::getTotalResults($qid, $campoCompany, $alias, $where, $this->periodTime, $isCompany);
            return $companiesTotal;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }


    function queryProvincias($params)
    {
        $campoProvincia = $this->fields[env('PARAM_PROVINCE')]['field'];
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')]['field'];
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')]['field'];
        $provinces = LimeAnswer::getNames($this->fields[env('PARAM_PROVINCE')]['code']);
        if ($params['province_name'] == 'Provincia de Tenerife' || $params['province_name'] == 'Provincia de Las Palmas') {
            foreach ($provinces as $pProv) {
                if ($pProv->answer == $params['province_name']) {
                    // $this->whereProvincias =   $this->surveyName . '.'
                    //     . $pProv['name']
                    //     . ' = \'' . $pProv['type'][$prov] . '\'';
                    $this->whereProvincias =   $this->surveyName . '.'
                        . $campoProvincia
                        . ' = \'' . $pProv->code . '\'';

                    if ($params['province_name'] == 'Provincia de Las Palmas') {
                        $this->whereProvincias .= " and " . $this->surveyName . '.' . $campoCentroLpa . ' is not null';
                    }
                    if ($params['province_name'] == 'Provincia de Tenerife') {
                        $this->whereProvincias .= " and " . $this->surveyName . '.' . $campoCentroTfe . ' is not null';
                    }
                }
            }
        }

        $qid = $this->fields[env('PARAM_PROVINCE')]['code'];
        $alias = 'provincia';
        $whereCond = $this->getWhere();

        if ($params['province_name'] != 'TODAS') {
            $totalProvincias = CurrentSurvey::getTotalResults($qid, $campoProvincia, $alias, $whereCond, $this->periodTime);
        } else {
            $auxWhere = $this->whereProvincias;
            $whereLpa = $this->surveyName . '.' . $campoCentroLpa . ' is not null';

            $this->whereProvinciasLpa = $auxWhere . $whereLpa;

            $this->whereProvincias = $auxWhere .  $this->whereProvinciasLpa;
            $whereCond = $this->getWhere();

            $totalProvinciasLpa = CurrentSurvey::getTotalResults($qid, $campoProvincia, $alias, $whereCond,  $this->periodTime);

            $whereTfe  = $this->surveyName . '.' . $campoCentroTfe . ' is not null';
            $this->whereProvinciasTfe = $auxWhere . $whereTfe;
            $this->whereProvincias = $auxWhere .  $this->whereProvinciasTfe;

            $whereCond = $this->getWhere();

            $totalProvinciasTfe = CurrentSurvey::getTotalResults($qid, $campoProvincia, $alias, $whereCond,  $this->periodTime);

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

        $alias = 'sexo';
        $campoSexo = $this->fields[env('PARAM_SEX')]['field'];
        $qid = $this->fields[env('PARAM_SEX')]['code'];

        try {
            $totalSexos = CurrentSurvey::getTotalResults($qid, $campoSexo, $alias, $where,  $this->periodTime);
            return $totalSexos;
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
        $alias = 'edad';
        $campoEdad = $this->fields[env('PARAM_AGE')]['field'];
        $qid = $this->fields[env('PARAM_AGE')]['code'];


        try {
            $totalEdades = CurrentSurvey::getTotalResults($qid, $campoEdad, $alias, $where, $this->periodTime);
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

    /**
     * Obtener datos de servicios requeridos
     * param $where (cláusula where)
     */
    function queryServices($where, $params)
    {
        $surveyCode = $this->fields[env('PARAM_SERVICE')]['code'];
        // $codelp = [substr($this->fields['servicios_hct']['name'], -3), substrp($this->fields['servicios_pol']['name'], -3)];
        $totalServicesLP = [];
        $totalServicesTF = [];

        if ($params['province_id'] == '-1') {
            $whereCondTF = $where . ' and ' . $this->whereProvinciasTfe;
            $whereCondLPA = $where . ' and ' . $this->whereProvinciasLpa;
            $totalServicesLP = $this->getServices($whereCondLPA);
            $totalServicesTF = $this->getServices($whereCondTF);
        } else if ($params['province_id'] == 'C1') {
            $totalServicesTF = $this->getServices($where);
        } else if ($params['province_id'] == 'C2') {
            $totalServicesLP = $this->getServices($where);
        }
        // $totalOtrosCentros = DB::select(
        //     'select(' . CurrentSurvey::getIntegerResults($surveyCode . $codelp[0], 'otros', $where, $this->periodTime) . ') + (' . CurrentSurvey::getIntegerResults($surveyCode . $codelp[1], 'otros', $where, $this->periodTime) . ')as otros'
        // );
        foreach ($totalServicesLP as $tservicelp) {
            // if ($tservicelp->servicio == 'Otros') {
            //     $tservicelp->total +=  $totalOtrosCentros[0]->otros;
            // }
        }

        return ['Tenerife' => $totalServicesTF, 'Las Palmas' => $totalServicesLP];
    }

    function getServices($whereCond)
    {
        $alias = 'servicio';
        $servicesType = $this->queryServicesNames($this->fields[env('PARAM_SERVICE')]['code']);
        $servicesType[4] = 'Otros';
        $serviceField = $this->fields[env('PARAM_SERVICE')]['field'];
        $codeService = $this->fields[env('PARAM_SERVICE')]['code'];

        $totalServicios = CurrentSurvey::getTotalResults($codeService, $serviceField, $alias, $whereCond,  $this->periodTime);

        $totalServices = [];
        foreach ($servicesType as $service) {
            $e = 0;
            foreach ($totalServicios as $tservice) {
                if ($service == $tservice->servicio) {
                    $e = 1;
                    $totalServices[] = (object) array('servicio' => $tservice->servicio, 'total' => $tservice->total);
                }
            }
            if ($e == 0) {
                $totalServices[] = (object) array('servicio' => $service, 'total' => 0);
            }
        }
        return $totalServices;
    }

    /**
     * Function to get the services of Policlinico Las Palmas & HCT
     */
    function queryOtherServices($where, $centreCode)
    {
        $surveyCode = substr($this->fields[env('PARAM_SERVICE')]['name'], 0, -3);
        $code = $centreCode;
        $alias = 'servicios';
        $totalServicios = CurrentSurvey::getTotalResults($code, $surveyCode . $code, $alias, $where,  $this->periodTime);
        foreach ($totalServicios as $ts) {
            $totalServices[] = (object) array('servicio' => strpos($ts->servicios, '(') !== false ? substr($ts->servicios, 0, strpos($ts->servicios, '(')) : $ts->servicios, 'total' => $ts->total);
        }
        return $totalServices;
    }


    /**
     * Obtener datos de antigüedad
     * param $where (cláusula where)
     */
    function queryExperiencia($where)
    {
        $alias = 'experiencia';
        $whereCond = $where;
        $campoExperiencia = $this->fields[env('PARAM_EXPERIENCE')]['field'];
        $qid = $this->fields[env('PARAM_EXPERIENCE')]['code'];

        try {
            $totalExperiencia = CurrentSurvey::getTotalResults($qid, $campoExperiencia, $alias, $whereCond, $this->periodTime);

            $totalExp['1º vez'] = 0;
            $totalExp['Ya ha estado'] = 0;
            if (!empty($totalExperiencia)) {
                $totalExp['1º vez'] = $totalExperiencia[0]->total;
                $totalExp['Ya ha estado']  = isset($totalExperiencia[1]) ? $totalExperiencia[1]->total : 0;
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
        if (!empty($where)) {
            $where = $where . ' and ';
        }
        $respuestasNeutro = ['A4', 'A3', 'A2'];
        $totalSatisfaccion['promotores'] = 0;
        $totalSatisfaccion['pasivos'] = 0;
        $totalSatisfaccion['detractores'] = 0;
        $totalSatisfaccion['nps'] = 0;
        $question = $this->fields[env('PARAM_QUESTION5')]['field'];

        $pasivos = 0;
        foreach ($respuestasNeutro as $rp) {
            $whereCond = $where  . $question . '="' . $rp . '"';
            $totalNeutros =  CurrentSurvey::getResults($question, 'pasivos', $whereCond, $this->periodTime);
            $pasivos += $totalNeutros[0]->pasivos;
        }

        $whereCond = $where . $question . '="A1"';
        $promotores = CurrentSurvey::getResults($question, 'promotores', $whereCond, $this->periodTime);

        $whereCond = $where . $question . '="A5"';
        $detractores = CurrentSurvey::getResults($question, 'detractores', $whereCond, $this->periodTime);
        $promoters = ($promotores[0]->promotores * 100) / ($promotores[0]->promotores + $detractores[0]->detractores + $pasivos);
        if ($promoters == 0) {
            $promoters = ($pasivos[0]->pasivos * 100) / ($promotores + $detractores + $pasivos[0]->pasivos);
            $totalSatisfaccion['promotores'] = $pasivos;
            $totalSatisfaccion['pasivos'] = 0;
        } else {
            $totalSatisfaccion['promotores'] = $promotores[0]->promotores;
            $totalSatisfaccion['pasivos'] = $pasivos;
        }
        $detractors = ($detractores[0]->detractores * 100) / ($promotores[0]->promotores + $detractores[0]->detractores + $pasivos);

        $totalSatisfaccion['detractores'] = $detractores[0]->detractores;
        $totalSatisfaccion['nps'] = round($promoters - $detractors, 1);

        return $totalSatisfaccion;
    }


    /**
     * Resultados de servicios por centro
     */
    function  queryCentros($where)
    {
        $alias = 'centro';
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')]['field'];
        $campoPregunta= $this->fields[env('PARAM_QUESTION5')]['field'];
        $qid = $this->fields[env('PARAM_CENTRE_LPA')]['code'];
        $centreLpa = LimeAnswer::getNames($qid);
        $auxWhere = $this->whereProvincias;
        $this->whereProvincias = $this->whereProvinciasLpa;
        $whereCond = $where;
        $totalCentreLpa =  CurrentSurvey::getTotalResults($qid, $campoCentroLpa, $alias, $whereCond, $this->periodTime);
        $totalCentLpa = [];
        foreach ($centreLpa as $cLpa) {
            $e = 0;
            foreach ($totalCentreLpa as $totCentLpa) {
                if ($cLpa->answer ==  $totCentLpa->centro) {
                    $satisfaction= CurrentSurvey:: getPercents($campoPregunta,$totCentLpa->total, 'satisfaccion', $whereCond,$this->periodTime, true, $campoCentroLpa,$cLpa->code );
                    $e = 1;
                    $totalCentLpa[] = (object) array(
                        'centro' => $totCentLpa->centro, 
                        'total' => $totCentLpa->total,
                        'satisfaction' => $satisfaction
                    
                    );
                }
            }
            if ($e == 0) {
                $totalCentLpa[] = (object) array(
                    'centro' => $cLpa->answer, 
                    'total' => 0,
                    'satisfaction' => 0
                );
            }
        }
        $this->whereProvincias = $this->whereProvinciasTfe;
        $whereCond = $where;
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')]['field'];
        $qid = $this->fields[env('PARAM_CENTRE_TFE')]['code'];
        $centreTfe = LimeAnswer::getNames($qid);
        $totalCentreTfe = CurrentSurvey::getTotalResults($qid, $campoCentroTfe, $alias, $whereCond,  $this->periodTime);
        $totalServices = [];
        foreach ($centreTfe as $service) {
            $e = 0;
            foreach ($totalCentreTfe as $totCenTfe) {
                if ($service->answer == $totCenTfe->centro) {
                    $satisfaction= CurrentSurvey:: getPercents($campoPregunta,$totCenTfe->total, 'satisfaccion', $whereCond,$this->periodTime, true, $campoCentroTfe,$service->code );
                    $e = 1;
                    $totalServices[] = (object) array(
                        'centro' => $totCenTfe->centro, 
                        'total' => $totCenTfe->total,
                        'satisfaction' => $satisfaction,                    
                    );
                }
            }
            if ($e == 0) {
                $totalServices[] = (object) array('centro' => $service->answer, 'total' => 0,
                'satisfaction' => 0);
            }
        }
        $this->whereProvincias = $auxWhere;
        return ['Tenerife' => $totalServices, 'Las Palmas' => $totalCentLpa];
    }


    // function querySatisfaction( $where, $totalCentros){
    //     foreach ($totalCentros as $totalCentro) {
    //         foreach ($totalCentro as $total) {
    //             $total= $total;
    //         }
    //     }
    // }

    /**
     * Obtener cantidad de encuestas por mes
     * param $where (cláusula where)
     */
    function querySurveyQuantity($where = null)
    {
        try {
            if (!empty($where)) {
                $where = ' and ' . $where;
            }
            $totalSurveys = DB::select(
                'select distinct month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') as fecha, count(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') as total,
                CASE
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 1 then "enero"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 2 then "febrero"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 3 then "marzo"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 4 then "abril"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 5 then "mayo"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 6 then "junio"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 7 then "julio"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 8 then "agosto"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 9 then "septiembre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 10 then "octubre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 11 then "noviembre"
                when month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ') = 12 then "diciembre"
                END as mes
                from  ' .  $this->surveyName .
                    ' where ' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                    . $where .
                    ' group by month(' .  $this->surveyName . '.' . $this->fields[env('PARAM_DATE')]['name'] . ')',
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
     * Obtener epígrafes de las preguntas
     * param $id (parámetro surveyId de la Request)
     */
    function queryPreguntas($id)
    {
        try {

            $gid = env('PARAM_QUESTIONS_GID');
            // $questions = Params::getFilteredFields('pregunta', $id);
            $preguntas = LimeQuestions::getFieldsBySidAndGid($id, $gid);

            return $preguntas;
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    //Resultados de satisfacción por centro (ejemplo para Parque TFE)
//     select la.answer as centro, COUNT(ls.989931X50X425) * 100 / (select COUNT(ls.989931X49X416)  from lime_survey_989931 ls inner join lime_answers la 
// on ls.989931X49X416 = la.code 
// and la.qid = 416  
// where ls.989931X50X425 is not null and ls.submitdate is not NULL 
// and ls.submitdate  between '2023-07-01' and '2024-01-12'
// and ls.989931X49X416 = 'TF8'
// group by la.answer ) as satisfaccion
// from lime_survey_989931 ls inner join lime_answers la 
// on ls.989931X49X416 = la.code 
// and la.qid = 416  
// where ls.989931X50X425 is not null and ls.submitdate is not NULL 
// and ls.submitdate  between '2023-07-01' and '2024-01-12'
// and ls.989931X49X416 = 'TF8'
// and (ls.989931X50X425 = 'A1' OR ls.989931X50X425 = 'A2')
// group by la.answer 

    /**
     * Obtener Porcentajes de satisfacción por pregunta
     * param $where (cláusula where)
     * param $id (parámetro surveyId de la Request)
     */
    function queryPercents($where = null, $id, $preguntas)
    {
        // $preguntas = $this->queryPreguntas($id);

        if (!empty($where)) {
            // $where = ' and ' . $where;
        }

        foreach ($preguntas as $i => $pregunta) {
            $campoPregunta =   $this->fields[env('PARAM_QUESTION' . ($i + 1))]['field'];
            $porcentPreg[($i + 1)] = CurrentSurvey :: getPercents($campoPregunta, $this->totalEncuestados,'percent_total',$where, $this->periodTime, false );
            
            // DB::table('lime_survey_' . $id)
            //     ->selectRaw('COUNT(`' . $campoPregunta . '`) * 100 / ' . $this->totalEncuestados . ' as percent_total')
            //     ->whereBetween($this->fields[env('PARAM_DATE')]['name'], [$this->periodTime[0], $this->periodTime[1]])
            //     ->whereIn($campoPregunta, ['A1', 'A2'])
            //     ->whereRaw($where)
            //     ->value('percent_total');
        }
        foreach ($preguntas as $i => $pregunta) {
            $total = 0;
            if (isset($porcentPreg[($i + 1)])) {
                $total += $porcentPreg[($i + 1)];
            }
            $total = round($total, 1);

            if ($total > 100) {
                $porcentPreg[($i + 1)] = 100;
            } else {
                $porcentPreg[($i + 1)] = $total;
            }
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
            $surveyFields = CurrentSurvey::getCurrentSurveyFields();
            $this->surveyName = 'lime_survey_' . $params['survey_id'];
            $this->fields = $this->getFieldsSurvey($surveyFields);
            $this->getPatientType($params);
            $this->getCompanies($params);
            $this->getCentre($params);

            // Filtros que recibimos
            /**
             * Encuesta
             * Fecha
             * Provincia
             * Tipo de paciente
             *
             */
            $params['startDate'] = str_replace('/', '-', $params['startDate']);
            $params['endDate'] = str_replace('/', '-', $params['endDate']);
            $params['startDate'] = date("Y-m-d", strtotime($params['startDate']));
            $params['endDate'] = date("Y-m-d", strtotime($params['endDate']));
            $this->periodTime = [$params['startDate'],  $params['endDate']];
            $params['startDate'] = date("d M Y", strtotime($params['startDate']));
            $params['endDate'] = date("d M Y", strtotime($params['endDate']));
            $fechaInicio =  Carbon::parse($params['startDate'])->isoFormat('DD MMMM YYYY');
            $fechaFin =  Carbon::parse($params['endDate'])->isoFormat('DD MMMM YYYY');
            $orgPeriod = $fechaInicio . ' a ' . $fechaFin;

            //Parametros:
            // -- Provincia (C1 Tenerife / C2 Las Palmas)
            $totalProvincias = $this->queryProvincias($params);
            $totalProvincia = [];
            foreach ($totalProvincias as $tProv) {
                if (is_array($tProv)) {
                    foreach ($tProv as $prov) {
                        if (isset($prov->provincia) && isset($prov->total)) {
                            $totalProvincia[$prov->provincia] = $prov->total;
                        }
                    }
                } else  if (is_object($tProv) && isset($tProv->provincia) && isset($tProv->total)) {
                    $totalProvincia[$tProv->provincia] = $tProv->total;
                } else if (isset($tProv[0]->provincia) && isset($tProv[0]->total)) {
                    $totalProvincia[$tProv[0]->provincia] = $tProv[0]->total;
                }
            }

            $this->totalEncuestados = $this->getEncuestados($params['province_name'],  $totalProvincia);

            $where = $this->getWhere();
            $totalSexos = $this->querySexos($where);
            $totalEdades = $this->queryEdades($where);
            $totalExperiencia = $this->queryExperiencia($where);
            $totalCentros = $this->queryCentros($where);
            // $totalCentrosSatisfaction= $this->querySatisfaction( $where, $totalCentros);
            $totalServicios = $this->queryServices($where, $params);
            $totalSatisfaccion = $this->queryNetPromoterScore($where);
            $totalSurveys = $this->querySurveyQuantity($where);
            $preguntas = $this->queryPreguntas($params['survey_id']);
            $porcentPreg = $this->queryPercents($where, $params['survey_id'], $preguntas);
            $tiposAsistencia = $this->queryCarePlan($where);
            if ($params['patient_id'] == 'T1' || $params['patient_id'] == 'T2' || $params['patient_id'] == 'T3') {
                if ($params['company'] != 0) {
                    $totalCompanies = $this->queryCompaniesTotal($params, $where);
                }
            }

            if ($params['company'] == 0) {
                $totalOtherCompanies = $this->queryOtherCompanies($params, $where);
            }

            $data = array(
                'title'               => 'INFORME ENCUESTAS DE SATISFACCION',
                'period'              => $orgPeriod,
                'totalProvincia'      => $totalProvincia,
                'totalProvincias'     => $totalProvincias,
                'totalSexo'           => $totalSexos,
                'totalEdad'           => $totalEdades,
                'totalExp'            => $totalExperiencia,
                'totalSatisfaccion'   => $totalSatisfaccion,
                'tiposAsistencia'     => $tiposAsistencia,
                'totalServiciosTF'    => $totalServicios['Tenerife'],
                'totalServiciosLPA'   => $totalServicios['Las Palmas'],
                'preguntas'           => $preguntas,
                'params'              => $params,
                'totalCentreLpa'      => $totalCentros['Las Palmas'],
                'totalCentreTfe'      => $totalCentros['Tenerife'],
                'porcentPreg'         => $porcentPreg,
                'totalEncuestados'    => $this->totalEncuestados,
                'totalEncuestas'      => $totalSurveys
            );
            if (isset($totalCompanies)) {
                $data['totalCompanies'] = $totalCompanies;
            } else {
                $data['totalCompanies'] = [];
            }
            if (isset($totalOtherCompanies)) {
                $data['totalOtherCompanies'] = $totalOtherCompanies;
            } else {
                $data['totalOtherCompanies'] = [];
            }

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
     * @param Request $request
     */
    public function generatePDF(Request $request)
    {
        try {
            $datos = $this->getData($request);
            $render = view('preview_data', $datos)->render();
            $renderCover = view('cover', $datos)->render();
            $header = view()->make('header')->render();

            $pdf = new Pdf;
            $pdf->setOptions([
                // 'enable-javascript' => true,
                'javascript-delay' => 2000,
                'header-html' => $header,
                // 'header-spacing' => 10,
                'header-line',
                'footer-right'     => "[page]",
                'footer-left'     => $datos['title'],
                'footer-font-size' => 8,
                // 'footer-spacing' => 10,
                'footer-line',
                'margin-top' => 25,
                'margin-bottom' => 15,
                'margin-left' => 20,
                'margin-right' => 15

            ]);
            $pdf->addCover($renderCover);
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
     * @param Request $request
     */

    public function download(Request $request)
    {
        try {


            $pdf = $this->generatePDF($request);
            $dataPDF = env('PUBLIC_PATH');
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
     * @param Request $request
     */
    public function preview(Request $request)
    {
        try {
            $datos = $this->getData($request);
            $render = view('preview_data', $datos)->render();
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

    public function getAll($items = [], $codes = [])
    {
        $allItems = [];
        foreach ($items as $item) {
            foreach ($codes as $code) {
                $allItems[] = LimeAnswer::getAllCompanies($item, $code)->toArray();
            }
        }

        $itemsList = [];
        foreach ($allItems as $i => $eachItemGroup) {
            foreach ($eachItemGroup as $eachItem) {
                $itemsList[] = $eachItem;
            }
        }

        $itemsList = collect($itemsList)
            ->unique('answer')
            ->sortBy('answer')
            ->values()
            ->all();

        return $itemsList;
    }


    /**
     * Checks if there are repeated words inside a string
     *
     * @param $wordChain $string The string to check.
     * @param  $repeated the chain of words you want to check for
     * @param  $remove the word/chain of words you want to remove
     * @return string|null the new string
     */
    public function checkRepeatedWords($wordChain, $repeated, $remove)
    {
        $string = $wordChain;
        $chain = $repeated;
        $words = explode(' ', $chain); // convert the chain into an array of words
        $containsChain = true; // assume the string contains the chain
        foreach ($words as $word) {
            if (!str_contains($string, $word)) {
                $containsChain = false; // set flag to false if a word is not found
                break;
            }
        }
        if ($containsChain) {
            // remove a word from the chain
            $newChain = str_replace($remove, "", $chain);
            $newString = str_replace($chain, $newChain, $string);
            return $newString;
        } else {
            return $string;
        }
    }

    /**
     * Checks if a string starts with a certain prefix using strpos.
     *
     * @param string $string The string to check.
     * @param array $prefixes The prefixes to check for.
     * @return string|null The prefix that the string starts with, or null if it doesn't start with any of the prefixes.
     */
    function check_string_prefix($string, $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (strpos($string, $prefix) === 0) {
                return $prefix;
            }
        }

        return null;
    }
}

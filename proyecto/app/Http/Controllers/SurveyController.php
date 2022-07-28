<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use mikehaertl\wkhtmlto\Pdf;

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

    public function index(Request $request){
        $this->icotSurvey = DB::connection('icotsurvey');
        $surveys = $this->icotSurvey->select('select sid, name
                                    from lime_surveys
                                    where expires is null and name is not null');
        $patientsType = ["Servicio Canario de la Salud"
                        , "Laboral / diversos"
                        , "Seguro médico"
                        , "Accidente tráfico"
                        , "Privado"
        ];

        return view('surveys', ['title'        => 'ENCUESTAS'
                               ,'provinces'    => ['Las Palmas' , 'Tenerife']
                               ,'surveys'      => $surveys
                               ,'patientsType' => $patientsType
        ]);
    }

    /**Function que coge parametros de la encuesta de icotSurvey */
    public function getFieldsSurvey($surveyFields) {
        $fields = [];
        foreach ($surveyFields as $key => $sf) {
            if (!empty($sf->type)){
                $fields[$sf->name][] = ['name' =>  $sf->field , 'type' => [$sf->type => $sf->value]];
            } else {
                $fields[$sf->name]   = ['name' =>  $sf->field ];
            }
        }

        return $fields;
    }


    public function getWhere() {
        $where = '';
        if (!empty($this->whereTipoPaciente)) {
            $where .= "  and " . $this->whereTipoPaciente;
        }

        if (!empty($this->whereProvincias)) {
            if (!empty($this->whereTipoPaciente)) {
                $where .= "  and " . $this->whereProvincias;
            } else {
                if (strpos(substr($where,0,5), 'and') === false && strpos(substr($this->whereProvincias,0,5), 'and') === false) {
                    $where .=  " and " . $this->whereProvincias;
                } else {
                    $where .=  " " . $this->whereProvincias;
                }
            }
        }
        //var_dump($where); die();
        return $where;
    }

    function queryProvincias($params) {

        $campoProvincia = $this->fields[env('PARAM_PROVINCE')][0]['name'];
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')][0]['name'];
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')][0]['name'];
        if ($params['province_id'] == 'Tenerife' || $params['province_id'] == 'Las Palmas' ) {
            foreach( $this->fields[env('PARAM_PROVINCE')] as $pProv ) {
                    foreach (array_keys($pProv['type']) as $prov) {
                        if ($prov == $params['province_id'] ) {
                            $this->whereProvincias =   $this->surveyName .'.'
                                                .$pProv['name']
                                                . ' = \''. $pProv['type'][$prov] . '\'';

                            if ($params['province_id'] == 'Las Palmas') {
                                $this->whereProvincias .= " and " . $this->surveyName .'.' . $campoCentroLpa . ' is not null';
                            }
                            if ($params['province_id'] == 'Tenerife') {
                                $this->whereProvincias .= " and " . $this->surveyName .'.' . $campoCentroTfe . ' is not null';
                            }
                        }
                    }
            }
        }
        $qid = substr($campoProvincia,-2);
        $whereCond = $this->getWhere();

        if ($params['province_id'] != 'TODAS') {

            $totalProvincias = $this->icotSurvey->select('select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total' .
                                ' from ' .$this->surveyName .
                                ' join lime_answers on lime_answers.code = ' . $this->surveyName .'.' . $campoProvincia . ' and lime_answers.qid = ' . $qid .
                               ' where ' .  $this->surveyName .'.'.$this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                                . $whereCond .
                                ' group by 1'
                                ,$this->periodTime);

        } else {
            $auxWhere = $this->whereProvincias;

            $whereLpa = ' and ' .$this->surveyName .'.' . $campoCentroLpa . ' is not null';

            $this->whereProvinciasLpa = $auxWhere . $whereLpa;

            $this->whereProvincias = $auxWhere .  $this->whereProvinciasLpa ;
            $whereCond = $this->getWhere();

            $totalProvinciasLpa = $this->icotSurvey->select('select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total ' .
                                                            'from ' .$this->surveyName .
                                                            ' join lime_answers on lime_answers.code = ' . $this->surveyName .'.' . $campoProvincia . ' and lime_answers.qid  = ' . $qid .
                                                           ' where ' .  $this->surveyName .'.'.$this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                                                            . $whereCond .
                                                            ' group by 1'
                                                            ,$this->periodTime);


            $whereTfe  = ' and ' .$this->surveyName .'.' . $campoCentroTfe . ' is not null';
            $this->whereProvinciasTfe = $auxWhere . $whereTfe;
            $this->whereProvincias = $auxWhere .  $this->whereProvinciasTfe ;

            $whereCond = $this->getWhere();
            $totalProvinciasTfe = $this->icotSurvey->select('select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total '.
                                                            'from ' .$this->surveyName .
                                                            ' join lime_answers on lime_answers.code = ' . $this->surveyName .'.' . $campoProvincia . ' and lime_answers.qid  = ' . $qid .
                                                            ' where ' .  $this->surveyName .'.'.$this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                                                            . $whereCond .
                                                            ' group by 1'
                                                            ,$this->periodTime);

            $totalProvincias[] = $totalProvinciasLpa;
            $totalProvincias[] = $totalProvinciasTfe;
        }
        return $totalProvincias;
    }

    function querySexos(){
        $whereCond = $this->getWhere();
        $campoSexo = $this->fields[env('PARAM_SEX')]['name'];
        $qid = substr($campoSexo,-2);


        $totalSexos = $this->icotSurvey->select('select distinct lime_answers.answer as sexo, count('. $this->surveyName . '.' . $campoSexo.') as total ' .
                                                'from ' . $this->surveyName .
                                                ' join lime_answers on lime_answers.code = ' . $this->surveyName . '.' . $campoSexo .' and lime_answers.qid = ' . $qid .
                                                ' where ' .  $this->surveyName .'.'.$this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                                                . $whereCond .
                                                ' group by 1'
                                                ,$this->periodTime);
        $totalSexo['Hombre'] = 0;
        $totalSexo['Mujer'] = 0;
        if (!empty($totalSexos)) {
            $totalSexo['Hombre'] = $totalSexos[0]->total;
            $totalSexo['Mujer']  = $totalSexos[1]->total;
        }
        return $totalSexo;
    }

    function queryEdades(){
        $whereCond = $this->getWhere();
        $campoEdad = $this->fields[env('PARAM_AGE')]['name'];
        $qid = substr($campoEdad,-2);

        $totalEdades = $this->icotSurvey->select('select distinct lime_answers.answer as edad, count('. $this->surveyName . '.' . $campoEdad.') as total
                                                    from  ' .  $this->surveyName .
                                                    '   join lime_answers on lime_answers.code = ' .   $this->surveyName . '.' . $campoEdad .' and lime_answers.qid = ' . $qid .
                                                      ' where ' .  $this->surveyName .'.'.$this->fields[env('PARAM_DATE')]['name'] . ' between ? and ? '
                                                    . $whereCond .
                                                    ' group by 1'
                                                    ,$this->periodTime);

        $tEdades = [];
        foreach ($totalEdades as $toEdad) {
            $colEdad = htmlentities($toEdad->edad);
            if (strpos($toEdad->edad, '<') !== false) {
                $colEdad =  'menor de ' . substr($toEdad->edad,1);
            }
            if (strpos($toEdad->edad, '>') !== false) {
                $colEdad =  'mayor de ' . substr($toEdad->edad,1);
            }
            $tEdades[] = (object) [ 'edad' => $colEdad, 'total' => $toEdad->total];
        }
        return $tEdades;
    }

    function queryCentros() {
        $campoCentroLpa = $this->fields[env('PARAM_CENTRE_LPA')][0]['name'];
        $qid = substr($campoCentroLpa,-2);

        $centreLpa =  $this->icotSurvey->select('select distinct lime_answers.answer as centro
                                                    from lime_answers
                                                    where qid = ' . $qid );

        $auxWhere = $this->whereProvincias ;
        $this->whereProvincias = $this->whereProvinciasLpa ;
        $whereCond = $this->getWhere();
        $totalCentreLpa = $this->icotSurvey->select("select lime_answers.answer as centro ,COUNT(". $this->surveyName . '.' . $campoCentroLpa.") as total
                                                        from  " .  $this->surveyName .
                                                        " JOIN lime_answers on lime_answers.code = " . $this->surveyName . '.' .$campoCentroLpa. " and lime_answers.qid = " . $qid .
                                                        " where " .  $this->surveyName .".".$this->fields[env('PARAM_DATE')]['name'] . " between ? and ? "
                                                        . $whereCond
                                                        . " group by 1"
                                                        , $this->periodTime);

        $totalCentLpa = [];
        foreach ( $centreLpa as $cLpa) {
            $e = 0;


            foreach($totalCentreLpa as $totCentLpa) {
                if ($cLpa->centro ==  $totCentLpa->centro) {
                    $e = 1;
                    $totalCentLpa[] = (object) array('centro' => $totCentLpa->centro , 'total' => $totCentLpa->total);
                }
            }
        }

        $this->whereProvincias = $this->whereProvinciasTfe ;
        $whereCond = $this->getWhere();
        $campoCentroTfe = $this->fields[env('PARAM_CENTRE_TFE')][0]['name'];

        $qid = substr($campoCentroTfe,-2);

        $centreTfe =  $this->icotSurvey->select('select distinct lime_answers.answer as centro
                                    from lime_answers
                                    where qid = ' . $qid );

        $totalCentreTfe = $this->icotSurvey->select("select replace(lime_answers.answer, 'ICOT', '') as centro ,COUNT(". $this->surveyName . '.' . $campoCentroTfe.") as total
                                        from  " .  $this->surveyName .
                                        " LEFT JOIN lime_answers on lime_answers.code= " . $this->surveyName . '.' .$campoCentroTfe. " and lime_answers.qid= " . $qid .
                                        " where " .  $this->surveyName .".".$this->fields[env('PARAM_DATE')]['name'] . " between ? and ? "
                                        . $whereCond .
                                        " group by 1", $this->periodTime);

        $totalCentTfe = [];
        foreach ( $centreTfe as $cTfe) {
            $e = 0;
            foreach($totalCentreTfe as $totCenTfe) {
                if ($cTfe->centro == 'ICOT' . $totCenTfe->centro) {
                    $e = 1;
                    $totalCentTfe[] = (object) array('centro' => 'ICOT' . $totCenTfe->centro , 'total' => $totCenTfe->total);
                }
            }
            if ($e == 0) {
                $totalCentTfe[] = (object) array('centro' => $cTfe->centro , 'total' => 0);
            }
        }
        $this->whereProvincias = $auxWhere;
        return ['Tenerife' => $totalCentTfe , 'Las Palmas' => $totalCentLpa];
    }

    function queryTipoPaciente($params){

        $this->whereTipoPaciente = '';
        if ($params['patient_id'] != -1) {
             //var_dump($this->fields[env('PARAM_TYPECLIENT')]); die();
            $campoTPaciente = $this->fields[env('PARAM_TYPECLIENT')][0]['name'];

            // $this->whereTipoPaciente = 'and lime_answers.answer = \'' . $params['patient_id'] .'\' and ' .    $this->surveyName .'.'
            // .$campoTPaciente
            // . ' =  lime_answers.code';
            $valueTPaciente = '';
            foreach($this->fields[env('PARAM_TYPECLIENT')] as $fieldTPaci) {
                if (array_keys($fieldTPaci['type'])[0] == $params['patient_id']) {
                    $valueTPaciente = array_values($fieldTPaci['type'])[0];
                    break;
                }
            }

            $this->whereTipoPaciente = $this->surveyName .'.' .$campoTPaciente . ' = \'' . $valueTPaciente . '\'' ;
        }
    }

    public function download(Request $request)
    {
        $params = $request->all();
        /**
         * Obtener parametros según ID de encuesta
         */
        $surveyFields = DB::select('select *
                                    from params
                                    where survey_id = ?' , [$params['survey_id']]);

        $this->icotSurvey = DB::connection('icotsurvey'); //Forzamos conexion con survey (datos encuestas)
        $this->surveyName = 'lime_survey_' .$params['survey_id'];
        $this->fields = $this->getFieldsSurvey($surveyFields);
        $this->queryTipoPaciente($params);

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
        $params['endDate'] = date("Y-m-d",strtotime($params['endDate']));

        $this->periodTime = [ $params['startDate'],  $params['endDate'] , $params['startDate'],  $params['endDate']  ];

        //Parametros:
        //-- 952748X5X50 -- Provincia (C1 Tenerife / C2 Las Palmas)
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
        if ($params['province_id'] == 'TODAS'  ) {
            $totalEncuestados += isset ($totalProvincia['Provincia de Las Palmas'] ) ? $totalProvincia['Provincia de Las Palmas']  : $totalEncuestados;
            $totalEncuestados += isset ($totalProvincia['Provincia de Tenerife'] ) ? $totalProvincia['Provincia de Tenerife']  : $totalEncuestados;
        }
        if ($totalEncuestados == 0) {
            return response()->json([
                'success' => 'false',
                'errors'  => 'SIN RESULTADOS'
            ],400);
        }

        $totalSexos = $this->querySexos();
        $totalEdades = $this->queryEdades();
        $totalCentros = $this->queryCentros();

        $preguntas = $this->icotSurvey->select('select distinct concat( \'PREGUNTA\', \'  \', substr(q.title, 5, 1), \':\') AS n_pregunta, q.question,  substr(q.title, 5, 1) as pregunta
                                                from lime_questions q
                                                where q.title like \'SQ%\' and q.sid = ' . $params['survey_id'] .'
                                                order by q.qid
                                                ');


        foreach($preguntas as $pregunta) {
            $campoPregunta =   $this->fields[env('PARAM_QUESTION'.$pregunta->pregunta)]['name'];
            $qid = substr($campoPregunta,9,2);
            $whereCond = $this->getWhere();
            $porcentPreg[$pregunta->pregunta] = $this->icotSurvey->select("select lime_answers.code, COUNT(lime_survey_".$params['survey_id'] .".`" . $campoPregunta. "`) as total
                , COUNT(lime_survey_".$params['survey_id'] .".`" . $campoPregunta. "`) * 100 /  " . $totalEncuestados ." as percent_total
                                    from lime_survey_" . $params['survey_id'] ."
                                    JOIN lime_answers on lime_answers.code=lime_survey_".$params['survey_id'] .".`" . $campoPregunta. "` and lime_answers.qid= " . $qid .
                                    " where " .  $this->surveyName .".".$this->fields[env('PARAM_DATE')]['name'] . " between ? and ?
                                    and (lime_answers.code = 'A4' or lime_answers.code = 'A5')"
                                    . $whereCond .
                                    " group by 1", $this->periodTime);
        }
        foreach($preguntas as $pregunta) {
            $total = 0;
            if (isset($porcentPreg[$pregunta->pregunta][0])) {
                $total += $porcentPreg[$pregunta->pregunta][0]->percent_total;
            }
            if (isset($porcentPreg[$pregunta->pregunta][1])) {
                $total += $porcentPreg[$pregunta->pregunta][1]->percent_total;
            }
            $porcentPreg[$pregunta->pregunta] = $total;
        }
        //var_dump($porcentPreg); die();
        // return view('preview_data',  [ 'title'              => 'ENCUESTAS'
        //                                 ,'period'           => $params['startDate'] . ' al ' . $params['endDate']
        //                                 ,'totalProvincia'   => $totalProvincia
        //                                 ,'totalSexo'        => $totalSexos
        //                                 ,'totalEdad'        => $totalEdades
        //                                 ,'preguntas'        => $preguntas
        //                                 ,'params'           => $params
        //                                 ,'totalCentreLpa'   => $totalCentros['Las Palmas']
        //                                 ,'totalCentreTfe'   => $totalCentros['Tenerife']
        //                                 ,'porcentPreg'      => $porcentPreg
        //                                 ,'totalEncuestados' => $totalEncuestados
        // ]);

        $render = view('preview_data', [ 'title'               => 'ENCUESTAS'
                                        ,'period'              => $orgPeriod
                                        ,'totalProvincia'      => $totalProvincia
                                        ,'totalSexo'           => $totalSexos
                                        ,'totalEdad'           => $totalEdades
                                        ,'preguntas'           => $preguntas
                                        ,'params'              => $params
                                        ,'totalCentreLpa'      => $totalCentros['Las Palmas']
                                        ,'totalCentreTfe'      => $totalCentros['Tenerife']
                                        ,'porcentPreg'         => $porcentPreg
                                        ,'totalEncuestados'    => $totalEncuestados
                                    ])->render();

        $pdf = new Pdf;
        $pdf->addPage($render);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $dataPDF = public_path('report.pdf');
        $pdf->saveAs($dataPDF);
        return response()->download($dataPDF);
    }
}

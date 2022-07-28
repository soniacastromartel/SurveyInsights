<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use mikehaertl\wkhtmlto\Pdf;

class SurveyController extends Controller
{

    public function index(Request $request){
        return view('surveys', ['title'      => 'ENCUESTAS'
                               ,'provinces'  => ['Las Palmas' , 'Tenerife']

        ]);
    }

    public function preview(Request $request) {
        $total = DB::table('lime_survey_952748')
        ->whereBetween('submitdate', ['2020-01-01', '2021-01-01'])
        ->count();
        return view('preview_data', ['total' => $total]);
    }


    public function download(Request $request)
    {
        $params = $request->all();
        //var_dump($params);
        $params['startDate'] = str_replace('/', '-', $params['startDate']);
        $params['endDate'] = str_replace('/', '-', $params['endDate']);
        $params['startDate'] = date("Y-m-d", strtotime($params['startDate']));
        $params['endDate'] = date("Y-m-d",strtotime($params['endDate']));


        $periodTime = [ $params['startDate'],  $params['endDate'] , $params['startDate'],  $params['endDate']  ];
        //var_dump($periodTime);
        //TODO.. Aplicar filtro submitdate
        $totalProvincias = DB::select('select distinct lime_answers.answer as provincia, count(lime_answers.answer) as total
                             from lime_survey_952748
                             join lime_answers on lime_answers.code = lime_survey_952748.952748X5X50 and lime_answers.qid = 1
                             where lime_survey_952748.submitdate between ? and ? and lime_survey_952748.952748X5X50 = \'C2\'
                             group by 1'
                             ,$periodTime);
                             //,['2020-05-17', '2020-05-19']);
                             //,[ "'".$params['startDate']. "'",  "'".$params['endDate'] . "'"]);

          // TODO.. Aplicar filtro de provincia
        $totalProvincia['LasPalmas'] = 0;
        $totalProvincia['Tenerife'] = 0;
        if (!empty($totalProvincias)) {
            $totalProvincia['LasPalmas'] = $totalProvincias[0]->total;
            //$totalProvincia['Tenerife']  = $totalProvincias[1]->total; //TODO... Isset
        }
        //var_dump($totalProvincia);

        //TODO... Get sex values
        $totalSexos = DB::select('select distinct lime_answers.answer as sexo, count(lime_survey_952748.952748X5X53) as total
                             from lime_survey_952748
                             join lime_answers on lime_answers.code = lime_survey_952748.952748X5X53 and lime_answers.qid = 4
                             where lime_survey_952748.submitdate between ? and ?
                             group by 1'
                             ,$periodTime);
                             //,['2020-01-01', '2021-01-01']);


        //var_dump($totalSexos);die();
        $totalSexo['Hombre'] = 0;
        $totalSexo['Mujer'] = 0;
        if (!empty($totalSexos)) {

            $totalSexo['Hombre'] = $totalSexos[0]->total;
            $totalSexo['Mujer']  = $totalSexos[1]->total;

        }

        //TODO... Get edad values
        $totalEdades = DB::select('select distinct lime_answers.answer as edad, count(lime_survey_952748.952748X5X51) as total
                             from lime_survey_952748
                             join lime_answers on lime_answers.code = lime_survey_952748.952748X5X51 and lime_answers.qid = 2
                             where lime_survey_952748.submitdate between ? and ? and lime_survey_952748.`952748X5X57` is not null
                             group by 1
                             order by lime_answers.sortorder',$periodTime);
                             //,['2020-01-01', '2021-01-01']);

        $tEdades = [];
        foreach ($totalEdades as $toEdad) {
            //$colEdad = htmlentities($toEdad->edad);
            $colEdad = htmlentities($toEdad->edad);
            if (strpos($toEdad->edad, '<') !== false) {
                $colEdad =  'menor de ' . substr($toEdad->edad,1);
            }
            if (strpos($toEdad->edad, '>') !== false) {
                $colEdad =  'mayor de ' . substr($toEdad->edad,1);
            }
            $tEdades[] = (object) [ 'edad' => $colEdad, 'total' => $toEdad->total];
        }
        //var_dump($tEdades);die();
                             //var_dump($totalEdades); die();
        //var_dump($totalSexos);die();
        // $totalEdad['<18']    = 0;
        // $totalEdad['>65']    = 0;
        // $totalEdad['18-30']  = 0;
        // $totalEdad['30-50']  = 0;
        // $totalEdad['50-65']  = 0;

        $preguntas = DB::select('select distinct concat( \'PREGUNTA\', \'  \', substr(q.title, 5, 1), \':\') AS n_pregunta, q.question,  substr(q.title, 5, 1) as pregunta
                    from lime_questions q
                    where q.title like \'SQ%\' and q.sid = 952748
                    order by q.qid
                    ');

        $centreLpa =  DB::select('select distinct lime_answers.answer as centro
                                    from lime_answers
                                    where qid = 57');


        $totalCentreLpa = DB::select("select replace(lime_answers.answer, 'ICOT', '') as centro ,COUNT(lime_survey_952748.`952748X5X57`) as total
                                        from lime_survey_952748
                                         JOIN lime_answers on lime_answers.code=lime_survey_952748.`952748X5X57` and lime_answers.qid=22
                                         where lime_survey_952748.submitdate between ? and ?
                                         and lime_survey_952748.`952748X5X57` in ('LP10', 'LP11', 'LP8', 'LP7', 'LP6', 'LP5', 'LP4', 'LP3', 'LP2')
                                         group by 1", $periodTime);

        $totalProvincia['LasPalmas'] = 0;
        foreach($totalCentreLpa as $totCentLpa) {
            $totalProvincia['LasPalmas'] += $totCentLpa->total;
        }

        //var_dump($totalProvincia['LasPalmas']); die();

        $totalCentLpa = [];
        foreach ( $centreLpa as $cLpa) {
            $e = 0;
            foreach($totalCentreLpa as $totCentLpa) {
                //var_dump('ICOT'.$totCentLpa->centro);
                //var_dump($cLpa->centro);
                if ($cLpa->centro == 'ICOT' . $totCentLpa->centro) {
                    //var_dump($totCentLpa);
                    $e = 1;
                    $totalCentLpa[] = (object) array('centro' => 'ICOT' . $totCentLpa->centro , 'total' => $totCentLpa->total);
                }
            }
            // if ($e == 0) {
            //     $totalCentLpa[] = (object) array('centro' => $cLpa->centro , 'total' => 0);
            // }
        }

        //  var_dump($totalCentLpa);
        //  die();

        $totalCentreTfe = DB::select("select replace(lime_answers.answer, 'ICOT', '') as centro ,COUNT(lime_survey_952748.`952748X5X56`) as total
                                        from lime_survey_952748
                                        LEFT JOIN lime_answers on lime_answers.code=lime_survey_952748.`952748X5X56` and lime_answers.qid=21
                                        where lime_survey_952748.submitdate between ? and ?
                                        group by 1", $periodTime);

        $centreTfe =  DB::select('select distinct lime_answers.answer as centro
                                    from lime_answers
                                    where qid = 56');


        $totalCentTfe = [];
        foreach ( $centreTfe as $cTfe) {
            $e = 0;
            foreach($totalCentreTfe as $totCenTfe) {
                //var_dump('ICOT'.$totCentLpa->centro);
                //var_dump($cLpa->centro);
                if ($cTfe->centro == 'ICOT' . $totCenTfe->centro) {
                    //var_dump($totCentLpa);
                    $e = 1;
                    $totalCentTfe[] = (object) array('centro' => 'ICOT' . $totCenTfe->centro , 'total' => $totCenTfe->total);
                }
            }
            if ($e == 0) {
                $totalCentTfe[] = (object) array('centro' => $cTfe->centro , 'total' => 0);
            }
        }

        $totalEncuestados = $totalProvincia['LasPalmas']  + $totalProvincia['Tenerife'];
        foreach($preguntas as $pregunta) {

            //FIXME..... Error query
            $porcentPreg[$pregunta->pregunta] = DB::select("select lime_answers.code, COUNT(lime_survey_952748.`952748X6X58SQ00" . $pregunta->pregunta. "`) as total
                , COUNT(lime_survey_952748.`952748X6X58SQ00" . $pregunta->pregunta. "`) * 100 /  " . $totalEncuestados ." as percent_total
                                    from lime_survey_952748
                                    JOIN lime_answers on lime_answers.code=lime_survey_952748.`952748X6X58SQ00" . $pregunta->pregunta. "` and lime_answers.qid=38
                                    where lime_survey_952748.submitdate between ? and ?
                                    and (lime_answers.code = 'A4' or lime_answers.code = 'A5')
                                    and lime_survey_952748.`952748X5X57` in ('LP10', 'LP11', 'LP8', 'LP7', 'LP6', 'LP5', 'LP4', 'LP3', 'LP2')
                                    group by 1", $periodTime);
                                    //group by lime_survey_952748.`952748X6X58SQ00" . $pregunta->pregunta. "`", $periodTime);
        }

        foreach($preguntas as $pregunta) {
            $porcentPreg[$pregunta->pregunta] = $porcentPreg[$pregunta->pregunta][0]->percent_total
                                                                  + $porcentPreg[$pregunta->pregunta][1]->percent_total;
        }

        //var_dump($porcentPreg); die();


        //var_dump($totalCentreLpa);
        //var_dump($totalEdad); die();


        // return view('preview_data', [ 'title'               => 'ENCUESTAS'
        //                             ,'period'              => $params['startDate'] . ' al ' . $params['endDate']
        //                             ,'totalProvincia'      => $totalProvincia
        //                             ,'totalSexo'           => $totalSexo
        //                             ,'totalEdad'           => $totalEdades
        //                             ,'preguntas'           => $preguntas
        //                             ,'params'              => $params
        //                             ,'totalCentreLpa'      => $totalCentLpa
        //                             ,'totalCentreTfe'      => $totalCentreTfe
        //                             ,'porcentPreg'         => $porcentPreg
        //                             ]);


        //var_dump($params); die();

        $render = view('preview_data', [ 'title'               => 'ENCUESTAS'
                                        ,'period'              => $params['startDate'] . ' al ' . $params['endDate']
                                        ,'totalProvincia'      => $totalProvincia
                                        ,'totalSexo'           => $totalSexo
                                        ,'totalEdad'           => $tEdades
                                        ,'preguntas'           => $preguntas
                                        ,'params'              => $params
                                        ,'totalCentreLpa'      => $totalCentLpa
                                        ,'totalCentreTfe'      => $totalCentTfe
                                        ,'porcentPreg'         => $porcentPreg
                                        ])->render();


        $pdf = new Pdf;

        $pdf->addPage($render);

        $pdf->setOptions(['javascript-delay' => 5000]);

        $pdf->saveAs(public_path('report.pdf'));

        return response()->download(public_path('report.pdf'));

    }


    public function calculate(Request $request) {
        try {

            //var_dump($request->all());

            //TODO... Call db
            $total = DB::table('lime_survey_952748')
            ->whereBetween('submitdate', ['2020-01-01', '2021-01-01'])
            ->count();

            var_dump($total);

            //TODO... Export to pdf


        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'errors'  => $e->getMessage(),
            ],400);
        }
    }
    //TODO.... metodo lanzar informe

}

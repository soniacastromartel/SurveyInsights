<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\LimeSurvey;
use App\Models\LimeQuestions;
class CurrentSurvey extends Model
{
    use HasFactory;

    // protected $table = 'lime_survey_891295';
    protected $table = 'lime_survey_989931';

    protected $fillable = [
        'id',
        'submitdate',
        "989931X48X412",//edad
        "989931X48X413",//sexo
        "989931X48X414",//experiencia
        "989931X49X415",//provincia
        "989931X49X416",//centrotf
        "989931X49X417",//centrolp
        "989931X49X427",//servicios
        "989931X49X427other",//servicios_otros
        "989931X49X429",//servicios_POL
        "989931X49X428",//servicios_HCT
        "989931X49X418",//tipo_asistencia
        "989931X49X419",// trafico
        "989931X49X419other",//trafico_otros
        "989931X49X420",//diversos
        "989931X49X420other",//diversos_otros
        "989931X49X421",//salud
        "989931X49X421other",//salud_otros
        "989931X50X422",//pregunta1
        "989931X50X423",//pregunta2
        "989931X50X424",//pregunta3
        "989931X50X425",//pregunta4
        "989931X50X426"//pregunta5

    ];



    public function scopeGetTotalResults($query, $qid, $code, $alias, $whereCond = null, $periodTime, $isCompany = false)
    {

        $total = CurrentSurvey::selectRaw('lime_answers.answer as ' . $alias . ', count(' . $code . ') as total')
            ->whereBetween('submitdate', [$periodTime])
            ->whereNotNull('submitdate')
            ->join('lime_answers', function ($join) use ($code, $qid) {
                $join->on('lime_answers.code', '=', $code)
                    ->where('lime_answers.qid', '=', $qid);
            });

        if (!empty($whereCond)) {
            $total->whereRaw($whereCond);
        }

        $total = $total->groupBy('lime_answers.answer');

        if ($isCompany) {
            $total = $total->orderByDesc('total')->take(10)->get();
        } else {
            $total = $total->get();
        }

        return $total;
    }



    public function scopeGetResults($query, $code, $alias, $whereCond = null, $periodTime)
    {

        $total = CurrentSurvey::selectRaw('count(' . $code . ') as ' . $alias)
            ->whereBetween('submitdate', [$periodTime])
            ->whereNotNull('submitdate');

        if (!empty($whereCond)) {
            $total->whereRaw($whereCond);
        }

        $results = $total->get();

        return $results;
    }

    public function scopeGetIntegerResults($query, $code, $alias, $whereCond = null, $periodTime)
    {
        $total = CurrentSurvey::selectRaw('count(' . $code . ') as ' . $alias)
            ->whereBetween('submitdate', [$periodTime])
            ->whereNotNull('submitdate');

        if (!empty($whereCond)) {
            $total->whereRaw($whereCond);
        }
        $results = $total->value($alias); // Return the count value directly

        return $results;
    }

    public  function scopeGetOther($query, $code, $whereCond, $periodTime)
    {
        $other = CurrentSurvey::selectRaw('count(' . $code . ')')
            ->whereBetween('submitdate', [$periodTime])
            ->whereRaw($whereCond)
            ->get();

        return $other;
    }

    public function scopeGetOtherCompanies($query, $code, $whereCond = null, $periodTime)
    {
        $total = CurrentSurvey::selectRaw($code . ' as company, count(' . $code . ') as total')
            ->whereBetween('submitdate', [$periodTime])
            ->whereNotNull('submitdate')
            ->whereNotNull($code);

        if (!empty($whereCond)) {
            $total->whereRaw($whereCond);
        }
        $total->groupBy($code);

        $otherCompanies = $total->get();

        return $otherCompanies;
    }

    public function scopeGetCurrentSurveyFields($query){
        $survey = LimeSurvey::getLastSurvey();
        $questionList=  LimeQuestions::getQuestionsBySurveyId($survey->sid);
        $surveyFieldsList= [];

        foreach ($questionList as $questions) {
            $surveyField= $survey->sid .'X'.$questions->gid.'X'.$questions->qid;
            $surveyFieldsList[] = (object) [
                'question' =>$questions->title, 
                'field' => $surveyField,
                'code' => $questions->qid
            ]; 
        }
        return $surveyFieldsList;
    }
}

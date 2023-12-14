<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;

class LimeSurvey extends Model
{
    use HasFactory;

    protected $table = 'lime_surveys';
    protected $fillable = [
        'sid',
        'name',
        'expires',
        'startdate',
        'datecreated'
    ];

    public function scopeGetLastSurveyCreated()
    {
        $survey = LimeSurvey::select('sid', 'name')
            ->where('datecreated', DB::raw("(SELECT MAX(datecreated) FROM lime_surveys)"))
            ->first();

        return $survey;
    }

    public function scopeGetLastSurvey()
    {
        $survey = LimeSurvey::select('sid', 'name')
            ->where('sid', env('PARAM_LASTSURVEY_SID'))
            ->first();

        return $survey;
    }

    public function scopeGetActiveSurveys()
    {
        $surveys = LimeSurvey::select('sid', 'name')
            ->whereNull('expires')
            ->whereNotNull('name')
            ->get();

        return $surveys;
    }


    // public function scopeGetTotalResults($query, $qid, $code, $alias, $whereCond = null, $periodTime, $isCompany = false)
    // {
    //     $surveys = $this->scopeGetLastSurvey();
    //     // $survey = $surveys->first();
    //     $currentTable = 'lime_survey_' . $surveys->sid;

    //     $total = DB::table($currentTable)
    //         ->selectRaw('lime_answers.answer as ' . $alias . ', count(' . $code . ') as total')
    //         ->whereBetween($currentTable . '.submitdate', [$periodTime])
    //         ->whereNotNull($currentTable . '.submitdate')
    //         ->join('lime_answers', function ($join) use ($code, $qid) {
    //             $join->on('lime_answers.code', '=', $code)
    //                 ->where('lime_answers.qid', '=', $qid);
    //         });

    //     if (!empty($whereCond)) {
    //         $total->whereRaw($whereCond);
    //     }

    //     $total = $total->groupBy('lime_answers.answer');

    //     if ($isCompany) {
    //         $total = $total->orderByDesc('total')->take(10)->get();
    //     } else {
    //         $total = $total->get();
    //     }

    //     return $total;
    // }



    // public function scopeGetResults($query, $code, $alias, $whereCond = null, $periodTime)
    // {
    //     $surveys = $this->scopeGetLastSurvey();
    //     // $survey = $surveys->first();
    //     $currentTable = 'lime_survey_' . $surveys->sid;

    //     $total = DB::table($currentTable)
    //         ->selectRaw('count(' . $code . ') as ' . $alias)
    //         ->whereBetween($currentTable . '.submitdate', [$periodTime])
    //         ->whereNotNull($currentTable . '.submitdate');

    //     if (!empty($whereCond)) {
    //         $total->whereRaw($whereCond);
    //     }

    //     $results = $total->get();

    //     return $results;
    // }

    // public function scopeGetIntegerResults($query, $code, $alias, $whereCond = null, $periodTime)
    // {
    //     $surveys = $this->scopeGetLastSurvey();
    //     // $survey = $surveys->first();
    //     $currentTable = 'lime_survey_' . $surveys->sid;

    //     $total = DB::table($currentTable)
    //         ->selectRaw('count(' . $code . ') as ' . $alias)
    //         ->whereBetween($currentTable . '.submitdate', [$periodTime])
    //         ->whereNotNull($currentTable . '.submitdate');

    //     if (!empty($whereCond)) {
    //         $total->whereRaw($whereCond);
    //     }
    //     $results = $total->value($alias); // Return the count value directly

    //     return $results;
    // }

    // public  function scopeGetOther($query, $code, $whereCond, $periodTime)
    // {
    //     $surveys = $this->scopeGetLastSurvey();
    //     // $survey = $surveys->first();
    //     $currentTable = 'lime_survey_' . $surveys->sid;

    //     $other = DB::table($currentTable)
    //         ->selectRaw('count(' . $code . ')')
    //         ->whereBetween($currentTable . '.submitdate', [$periodTime])
    //         ->whereRaw($whereCond)
    //         ->get();

    //     return $other;
    // }

    // public function scopeGetOtherCompanies($query, $code, $whereCond = null, $periodTime)
    // {
    //     $surveys = $this->scopeGetLastSurvey();
    //     $currentTable = 'lime_survey_' . $surveys->sid;

    //     $total = DB::table($currentTable)
    //         ->selectRaw($code . ' as company, count(' . $code . ') as total')
    //         ->whereBetween($currentTable . '.submitdate', [$periodTime])
    //         ->whereNotNull($currentTable . '.submitdate')
    //         ->whereNotNull($code);

    //     if (!empty($whereCond)) {
    //         $total->whereRaw($whereCond);
    //     }
    //     $total->groupBy($code);

    //     $otherCompanies = $total->get();

    //     return $otherCompanies;
    // }



}

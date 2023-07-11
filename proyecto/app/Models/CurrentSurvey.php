<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CurrentSurvey extends Model
{
    use HasFactory;

    protected $table = 'lime_survey_891295';

    protected $fillable = [
        'id',
        'submitdate',
        '891295X38X305',
        '891295X38X306',
        '891295X38X307',
        '891295X38X308',
        '891295X38X309',
        '891295X38X310',
        '891295X38X311',
        '891295X38X312',
        '891295X38X314',
        '891295X38X315',
        '891295X38X316',
        '891295X38X323',
        '891295X38X324',
        '891295X38X325',
        '891295X38X326',
        '891295X39X313SQ001',
        '891295X39X313SQ002',
        '891295X39X313SQ003',
        '891295X39X313SQ004',
        '891295X39X313SQ005'

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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;


class LimeAnswer extends Model
{
    use HasFactory;

    protected $table = 'lime_answers';
    protected $fillable = [
        'qid',
        'code',
        'answer',
        'sortorder',
        'assessment_value'
    ];

    public static function getNames($qid)
    {
        $names = LimeAnswer::select('answer', 'code')
            ->where('qid', '=', $qid)
            ->groupBy('lime_answers.answer')
            ->get();
        return $names;
    }

    public static function getAllCompanies($code,$qid)
    {
        $surveys = LimeSurvey::getLastSurvey();
        $companies = LimeAnswer::select('answer', 'code')
            ->distinct()
            ->join('lime_survey_'.$surveys->sid.' as ls', function ($join) use ($code,$qid) {
                $join->on('lime_answers.code', '=', $code);
                $join ->where('lime_answers.qid', '=', $qid); // Add this line;
            })
            ->groupBy('lime_answers.answer')
            ->get();

        return $companies;
    }

    public function getQid($answer)
    {
        $codes = LimeAnswer::select('qid','code')
            ->where('answer', '=', $answer)
            ->get();
        return $codes;
    }
}

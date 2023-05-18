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

    public static function getAllCompanies($code)
    {
        $companies = LimeAnswer::select('answer', 'code')
            ->distinct()
            ->join('lime_survey_891295 as ls', function ($join) use ($code) {
                $join->on('lime_answers.code', '=', $code);
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

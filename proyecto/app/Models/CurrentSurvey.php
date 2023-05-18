<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CurrentSurvey extends Model
{
    use HasFactory;


    public function getSurveyResults($surveyCode, $codetf, $whereCond, $periodTime)
    {

        return DB::table($currentTable)
            ->select(DB::raw('DISTINCT lime_answers.answer as servicio, COUNT('.$currentTable.'.'.$surveyCode.$codetf.') as total'))
            ->join('lime_answers', function($join) use ($surveyCode, $codetf) {
                $join->on('lime_answers.code', '=', $currentTable .'.'.$surveyCode.$codetf)
                    ->on('lime_answers.qid', '=', $codetf);
            })
            ->whereBetween($currentTable.'.'.$this->fields[env('PARAM_DATE')]['name'], $periodTime)
            ->whereRaw($whereCond)
            ->groupBy('lime_answers.answer')
            ->get();
    }
    



}

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


}

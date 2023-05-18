<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Params extends Model
{
    use HasFactory;

    protected $connection= 'encuestas';
    protected $table= 'params';
    protected $fillable=[
        'id',
        'field',
        'name',
        'type',
        'value',
        'survey_id'
    ];

    public function getParams ($surveyId){
        $params= Params::where('survey_id', $surveyId)->get();
        return $params;
    }

    public function getField ($value){
        $field = Params:: where ('field', '=', $value)-> value('field');
        return $field;
    }

    public function getFields ($field){
        $fields = Params::select('type','value')
        ->where('name','=', $field)
        ->distinct()
        ->get();

        return $fields;
    }


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Parametro extends Model
{

    protected $fillable = [
        'field'
        ,'name'
        ,'type'
        ,'value'
        ,'survey_id'
    ];

}

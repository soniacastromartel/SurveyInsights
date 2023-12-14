<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimeQuestions extends Model
{
    use HasFactory;

    protected $table = 'lime_questions';
    protected $fillable = [
        'qid',
        'sid',
        'gid',
        'title'
    ];

    
    public function scopeGetQuestionsBySurveyId($query, $surveyId)
    {
        $fillableFields = $this->fillable;
        $questions = $query->select($fillableFields)
            ->where('sid', $surveyId)
            ->get();

            $resultArray = [];
            foreach ($questions as $question) {
                $questionArray = [];
                foreach ($fillableFields as $field) {
                    $questionArray[$field] = $question->$field;
                }
                $resultArray[] = (object) $questionArray;
            }
    
            return $resultArray;    }
}

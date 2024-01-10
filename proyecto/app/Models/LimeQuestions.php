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
        'title',
        'question'
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

        return $resultArray;
    }


    public function scopeGetFieldsBySidAndGid($query, $sid, $gid)
    {
        $questions = $query->select('question')
            ->where('sid', $sid)
            ->where('gid', $gid)
            ->get();

        $resultArray = [];
        foreach ($questions as $question) {
            $normalizedQuestion = $this->normalizeString($question->question);
            $resultArray[] = (object)['question' => $normalizedQuestion];
        }

        return $resultArray;
    }


   public function normalizeString($htmlString)
    {
        $plainText = strip_tags($htmlString);
        $decodedText = html_entity_decode($plainText);
        $normalizedText = trim($decodedText);

        return $normalizedText;
    }
}

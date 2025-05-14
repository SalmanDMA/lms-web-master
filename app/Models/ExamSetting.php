<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exam_settings';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'exam_id',
        'school_exam_id',
        'start_time',
        'end_time',
        'token',
        'token_expiration',
        'duration',
        'repeat_chance',
        'device',
        'maximum_user',
        'is_random_question',
        'is_random_answer',
        'is_show_score',
        'is_show_result',
    ];

    public function class_exam()
    {
        return $this->belongsTo(ClassExam::class, 'exam_id', 'id');
    }

    public function school_exam()
    {
        return $this->belongsTo(SchoolExam::class, 'school_exam_id', 'id');
    }
}

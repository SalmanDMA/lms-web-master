<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';


    protected $fillable = [
        'id',
        'exam_id',
        'school_exam_id',
        'category_id',
        'section_id',
        'question_text',
        'question_type',
        'point',
        'grade_method',
        'difficult',
    ];


    public function class_exam()
    {
        return $this->belongsTo(ClassExam::class, 'exam_id', 'id');
    }

    public function school_exam()
    {
        return $this->belongsTo(SchoolExam::class, 'school_exam_id', 'id');
    }

    public function exam_sections()
    {
        return $this->belongsTo(ExamSection::class, 'section_id', 'id');
    }

    public function question_category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id', 'id');
    }

    public function choices()
    {
        return $this->hasMany(Choices::class, 'question_id', 'id');
    }

    public function question_attachments()
    {
        return $this->hasMany(QuestionAttachment::class, 'question_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id', 'id');
    }
}

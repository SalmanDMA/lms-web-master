<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'school_exams';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'title',
        'description',
        'type',
        'instruction',
        'course',
        'status',
        'publication_status',
        'class_level',
        'academic_year',
        'semester',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course', 'id');
    }

    public function exam_sections()
    {
        return $this->hasMany(ExamSection::class, 'exam_id', 'id');
    }

    public function exam_settings()
    {
        return $this->hasMany(ExamSetting::class, 'school_exam_id', 'id');
    }

    public function exam_setting()
    {
        return $this->hasOne(ExamSetting::class, 'school_exam_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'school_exam_id', 'id');
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'school_exam_id', 'id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'exam_teachers', 'exam_id', 'teacher_id')->withPivot('role')->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollment_exams', 'exam_id', 'student_id')->withPivot('do_exam')->withTimestamps();
    }
}

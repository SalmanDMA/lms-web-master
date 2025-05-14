<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Response extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $table = 'responses';

    protected $fillable = [
        'id',
        'student_id',
        'exam_id',
        'school_exam_id',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function class_exam()
    {
        return $this->belongsTo(ClassExam::class, 'exam_id', 'id');
    }

    public function school_exam()
    {
        return $this->belongsTo(SchoolExam::class, 'school_exam_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'response_id', 'id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'response_id', 'id');
    }
}

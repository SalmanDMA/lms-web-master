<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrollmentExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enrollment_exams';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'exam_id',
        'do_exam'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function exam()
    {
        return $this->belongsTo(SchoolExam::class, 'exam_id', 'id');
    }
}

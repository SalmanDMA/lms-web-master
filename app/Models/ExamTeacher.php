<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamTeacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exam_teachers';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'exam_id',
        'teacher_id',
        'role',
    ];

    public function exam()
    {
        return $this->belongsTo(SchoolExam::class, 'exam_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}

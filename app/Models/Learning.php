<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Learning extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "learnings";

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'status',
        'course',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'learning_id', 'id');
    }

    public function class_exams()
    {
        return $this->hasMany(ClassExam::class, 'learning_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'courses';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'created_by',
        'courses_title',
        'courses_description',
        'type',
        'curriculum',
        'course_code',
    ];

    public function is_created_by()
    {
        return $this->belongsTo(School::class, 'created_by', 'id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'course_id', 'id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'course_teacher', 'course_id', 'teacher_id')->withPivot('status')->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollment', 'course_id', 'student_id')->withPivot('enrollment_date')->withTimestamps();
    }
}

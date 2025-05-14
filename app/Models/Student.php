<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = 'students';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'sub_class_id',
        'nisn',
        'major',
        'type',
        'year'
    ];

    protected $with = ['sub_classes'];

    public function sub_classes()
    {
        return $this->belongsTo(SubClasses::class, 'sub_class_id', 'id');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollment', 'student_id', 'course_id')->withPivot('enrollment_date')->withTimestamps();
    }

    public function school_exams()
    {
        return $this->belongsToMany(SchoolExam::class, 'enrollment_exams', 'student_id', 'exam_id')->withPivot('do_exam')->withTimestamps();
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'student_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'student_id', 'id');
    }
}

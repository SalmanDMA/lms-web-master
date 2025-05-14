<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Teacher extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = 'teachers';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'nip',
        'is_wali'
    ];

    protected $guarded = [];

    protected $with = ['sub_classes'];

    public function sub_classes()
    {
        return $this->belongsToMany(SubClasses::class, 'teacher_sub_class', 'teacher_id', 'sub_class_id')
            ->withPivot('course')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function material_banks()
    {
        return $this->hasMany(MaterialBank::class, 'created_by', 'id');
    }

    public function learnings()
    {
        return $this->hasMany(Learning::class, 'teacher_id', 'id');
    }

    public function assignment_banks()
    {
        return $this->hasMany(AssignmentBank::class, 'created_by', 'id');
    }

    public function rpps()
    {
        return $this->hasMany(Rpp::class, 'teacher_id', 'id');
    }

    public function rpp_banks()
    {
        return $this->hasMany(RppBank::class, 'teacher_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_teacher', 'teacher_id', 'course_id')->withPivot('status')->withTimestamps();
    }

    public function school_exams()
    {
        return $this->belongsToMany(SchoolExam::class, 'exam_teachers', 'teacher_id', 'exam_id')->withPivot('role')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'teacher_id', 'id');
    }
}

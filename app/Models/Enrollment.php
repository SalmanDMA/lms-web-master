<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enrollment';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';


    protected $fillable = [
        'id',
        'student_id',
        'course_id',
        'enrollment_date',
    ];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}

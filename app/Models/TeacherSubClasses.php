<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSubClasses extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_sub_class';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'sub_class_id',
        'course',
        'learning_id'
    ];

    public function teachers()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function sub_classes()
    {
        return $this->belongsTo(SubClasses::class, 'sub_class_id', 'id');
    }
}

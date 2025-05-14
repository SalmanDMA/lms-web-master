<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class_exams';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'learning_id',
        'title',
        'description',
        'type',
        'instruction',
        'is_active',
        'status',
    ];


    public function learning()
    {
        return $this->belongsTo(Learning::class, 'learning_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_id', 'id');
    }

    public function exam_setting()
    {
        return $this->hasOne(ExamSetting::class, 'exam_id', 'id');
    }
}

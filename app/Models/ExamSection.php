<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exam_sections';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'exam_id',
        'name',
        'description',
    ];

    public function school_exams()
    {
        return $this->belongsTo(SchoolExam::class, 'exam_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'section_id', 'id');
    }
}

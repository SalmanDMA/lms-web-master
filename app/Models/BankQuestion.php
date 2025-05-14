<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankQuestion extends Model
{
    use HasFactory;

    protected $table = "bank_questions";

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'category_id',
        'question_text',
        'question_type',
        'point',
        'grade_method',
        'course',
        'class_level',
        'is_required',
        'shared_at',
        'shared_count',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function question_category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id', 'id');
    }

    public function choices_banks()
    {
        return $this->hasMany(ChoicesQuestionBank::class, 'question_id', 'id');
    }

    public function question_attachment_banks()
    {
        return $this->hasMany(QuestionAttachmentBank::class, 'question_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentBank extends Model
{
    use HasFactory;

    protected $table = 'assignment_banks';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'created_by',
        'courses_name',
        'assignment_title',
        'assignment_description',
        'instruction',
        'class_level',
        'due_date',
        'limit_submit',
        'is_visibleGrade',
        'max_attach'
    ];

    protected $with = ['course', 'created_by'];

    public function created_by()
    {
        return $this->belongsTo(Teacher::class, 'created_by', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courses_name', 'id');
    }

    public function assignment_attachments()
    {
        return $this->hasMany(AssignmentAttachment::class, 'assignment_bank_id', 'id');
    }
}

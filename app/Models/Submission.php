<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submissions';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'assignment_id',
        'student_id',
        'submission_content',
        'submission_note',
        'submitted_at',
        'feedback',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'submission_id', 'id');
    }

    public function submission_attachments()
    {
        return $this->hasMany(SubmissionAttachment::class, 'submission_id', 'id');
    }
}

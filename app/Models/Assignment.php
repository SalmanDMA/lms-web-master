<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'learning_id',
        'assignment_title',
        'assignment_description',
        'instruction',
        'due_date',
        'end_time',
        'collection_type',
        'limit_submit',
        'class_level',
        'is_visibleGrade',
        'publication_status',
        'max_attach',
    ];

    protected $with = [
        'learning',
    ];

    public function learning()
    {
        return $this->belongsTo(Learning::class, 'learning_id', 'id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id', 'id');
    }

    public function assignment_attachments()
    {
        return $this->hasMany(AssignmentAttachment::class, 'assignment_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grades';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'submission_id',
        'response_id',
        'knowledge',
        'skills',
        'class_exam',
        'exam',
        'graded_at',
        'is_main',
        'status',
        'publication_status',
    ];

    protected $with = [
        'submission'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    public function response()
    {
        return $this->belongsTo(Response::class, 'response_id', 'id');
    }
}

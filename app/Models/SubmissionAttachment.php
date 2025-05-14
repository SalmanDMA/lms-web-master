<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionAttachment extends Model
{
    use HasFactory;

    public $table = 'submission_attachments';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'submission_id',
        'file_name',
        'file_type',
        'file_url',
        'file_size',
        'file_extension',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }
}

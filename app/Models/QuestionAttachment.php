<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAttachment extends Model
{
    use HasFactory;

    protected $table = 'question_attachments';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_id',
        'file_name',
        'file_url',
        'file_type',
        'file_extension',
        'file_size',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
}

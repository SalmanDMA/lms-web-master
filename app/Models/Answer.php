<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'answers';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_id',
        'response_id',
        'choice_id',
        'answer_text',
        'is_graded',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function response()
    {
        return $this->belongsTo(Response::class, 'response_id', 'id');
    }

    public function choice()
    {
        return $this->belongsTo(Choices::class, 'choice_id', 'id');
    }
}

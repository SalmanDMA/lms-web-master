<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choices extends Model
{
    use HasFactory;

    protected $table = 'choices';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_id',
        'choice_text',
        'is_true',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'choice_id', 'id');
    }
}

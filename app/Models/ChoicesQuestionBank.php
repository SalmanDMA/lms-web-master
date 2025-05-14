<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChoicesQuestionBank extends Model
{
    use HasFactory;

    protected $table = 'choices_question_banks';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'question_id',
        'choice_text',
        'is_true',
    ];

    public function bank_question()
    {
        return $this->belongsTo(BankQuestion::class, 'question_id', 'id');
    }
}

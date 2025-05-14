<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'question_categories';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['id', 'name'];

    public function question_banks()
    {
        return $this->hasMany(ChoicesQuestionBank::class, 'category_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id', 'id');
    }
}

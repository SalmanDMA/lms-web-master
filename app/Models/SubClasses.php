<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubClasses extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sub_class';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',  'school_id', 'class_id', 'name', 'guardian'
    ];

    protected $with = ['class'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_sub_class', 'sub_class_id', 'teacher_id')
            ->withPivot('course')
            ->withTimestamps();
    }
}

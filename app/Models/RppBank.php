<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RppBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rpp_bank';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'teacher_id',
        'courses',
        'class_level',
        'draft_name',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function rpp_draft()
    {
        return $this->hasMany(RppDraft::class, 'rpp_bank_id', 'id');
    }

    public function subject_matters()
    {
        return $this->hasMany(SubjectMatter::class, 'rpp_bank_id', 'id');
    }
}

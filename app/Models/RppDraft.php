<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RppDraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rpp_draft';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'rpp_id',
        'rpp_bank_id',
        'teacher_id',
        'courses',
        'class_level',
        'draft_name',
        'status',
        'academic_year',
        'semester'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function subject_matters()
    {
        return $this->hasMany(SubjectMatter::class, 'rpp_draft_id', 'id');
    }

    public function rpp()
    {
        return $this->belongsTo(Rpp::class, 'rpp_id', 'id');
    }

    public function rpp_bank()
    {
        return $this->belongsTo(RppBank::class, 'rpp_bank_id', 'id');
    }
}

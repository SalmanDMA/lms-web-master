<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectMatter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subject_matters';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'rpp_id',
        'rpp_bank_id',
        'rpp_draft_id',
        'title',
        'time_allocation',
        'learning_goals',
        'learning_activity',
        'grading',
    ];

    public function rpp()
    {
        return $this->belongsTo(Rpp::class, 'rpp_id', 'id');
    }

    public function rppBank()
    {
        return $this->belongsTo(RppBank::class, 'rpp_bank_id', 'id');
    }

    public function rppDraft()
    {
        return $this->belongsTo(RppDraft::class, 'rpp_draft_id', 'id');
    }
}

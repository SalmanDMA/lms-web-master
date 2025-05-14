<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentAttachment extends Model
{
    use HasFactory;

    protected $table = 'assignment_attachments';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'assignment_id',
        'assignment_bank_id',
        'file_name',
        'file_url',
        'file_type',
        'file_extension',
        'file_size',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }

    public function assignment_bank()
    {
        return $this->belongsTo(AssignmentBank::class, 'assignment_bank_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'teacher_id',
        'type',
        'title',
        'message',
        'is_read',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}

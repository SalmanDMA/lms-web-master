<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialBank extends Model
{
    use HasFactory;

    public $table = 'material_banks';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'created_by',
        'course_title',
        'material_title',
        'material_description',
        'class_level',
        'shared_at',
        'max_attach',
    ];

    protected $with = ['course', 'created_by', 'material_resources'];

    public function created_by()
    {
        return $this->belongsTo(Teacher::class, 'created_by', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_title', 'id');
    }

    public function material_resources()
    {
        return $this->hasMany(MaterialResource::class, 'material_bank_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory;

    public $table = 'materials';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'learning_id',
        'material_title',
        'material_description',
        'class_level',
        'shared_at',
        'publication_status',
        'status',
        'max_file',
    ];

    protected $with = ['learning', 'material_resources'];

    public function learning()
    {
        return $this->belongsTo(Learning::class, 'learning_id', 'id');
    }

    public function material_resources()
    {
        return $this->hasMany(MaterialResource::class, 'material_id', 'id');
    }
}

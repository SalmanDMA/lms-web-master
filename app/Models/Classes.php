<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'class';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name'
    ];

    public function sub_classes()
    {
        return $this->hasMany(SubClasses::class, 'class_id', 'id');
    }
}

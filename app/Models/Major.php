<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'majors';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'school_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = 'staffs';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'nip',
        'placement',
        'authority',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class School extends Model
{
    use HasFactory, HasApiTokens, SoftDeletes;

    protected $table = 'schools';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'admin_email',
        'admin_password',
        'admin_name',
        'admin_phone',
        'admin_address',
        'logo',
        'school_image',
        'structure',
        'phone_number',
        'email',
        'website',
        'name',
        'another_name',
        'type',
        'status',
        'acreditation',
        'vision',
        'mission',
        'description',
        'country',
        'province',
        'city',
        'district',
        'neighborhood',
        'rw',
        'latitude',
        'longitude',
        'address',
        'pos',
        'is_premium',
        'premium_expired_date',
    ];

    protected $hidden = [
        'admin_password',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function academic_years()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function sub_classes()
    {
        return $this->hasMany(SubClasses::class);
    }

    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}

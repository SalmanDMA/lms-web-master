<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'email',
        'password',
        'status',
        'fullname',
        'phone',
        'gender',
        'religion',
        'address',
        'role',
        'image_path',
        'is_premium',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $with = ['is_student', 'is_teacher', 'is_staff', 'is_school'];

    public function is_student()
    {
        return $this->belongsTo(Student::class, 'id', 'user_id');
    }

    public function is_teacher()
    {
        return $this->belongsTo(Teacher::class, 'id', 'user_id');
    }

    public function is_staff()
    {
        return $this->belongsTo(Staff::class, 'id', 'user_id');
    }

    public function is_school()
    {
        return $this->belongsTo(School::class);
    }
}

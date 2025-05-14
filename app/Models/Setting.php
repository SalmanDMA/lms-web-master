<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'splash_logo',
        'splash_title',
        'login_image_student',
        'login_image_teacher',
        'title',
        'logo',
        'logo_thumbnail',
        'primary_color',
        'secondary_color',
        'accent_color',
        'white_color',
        'black_color',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}

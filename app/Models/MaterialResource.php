<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialResource extends Model
{
    use HasFactory;

    protected $table = 'material_resources';

    public $incrementing = false;
    public $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'material_bank_id',
        'material_id',
        'resource_name',
        'resource_type',
        'resource_url',
        'resource_extension',
        'resource_size'
    ];

    public function material_bank()
    {
        return $this->belongsTo(MaterialBank::class, 'material_bank_id', 'id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Watch extends Model
{
    use HasFactory, HasNeighbors;

    protected $casts = ['embedding' => Vector::class];

    protected $fillable = [
        'brand',
        'model',
        'case_material',
        'strap_material',
        'movement_type',
        'water_resistance',
        'case_diameter_mm',
        'case_thickness_mm',
        'band_width_mm',
        'dial_color',
        'crystal_material',
        'complications',
        'power_reserve',
        'price_usd',
    ];
}

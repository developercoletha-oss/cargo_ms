<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'cargo_id',
        'description',
        'cargo_type',
        'weight_kg',
        'volume_cbm',
        'quantity',
        'package_count',
        'estimated_value',
        'is_fragile',
        'is_hazardous',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'volume_cbm' => 'decimal:2',
            'estimated_value' => 'decimal:2',
            'is_fragile' => 'boolean',
            'is_hazardous' => 'boolean',
        ];
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}

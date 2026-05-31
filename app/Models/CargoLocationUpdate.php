<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoLocationUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'cargo_id',
        'reported_by',
        'location_name',
        'latitude',
        'longitude',
        'source',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'recorded_at' => 'datetime',
        ];
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

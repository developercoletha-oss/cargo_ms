<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoCheckpoint extends Model
{
    use HasFactory;

    protected $table = 'cargo_checkpoints';

    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_ACTIVE_CURRENT = 'ACTIVE_CURRENT';
    public const STATUS_PENDING = 'PENDING';

    protected $fillable = [
        'cargo_id',
        'name',
        'status',
        'sequence',
        'timestamp',
        'latitude',
        'longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'timestamp' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}

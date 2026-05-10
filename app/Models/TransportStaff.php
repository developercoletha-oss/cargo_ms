<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportStaff extends Model
{
    use HasFactory;

    protected $table = 'transport_staff';

    protected $fillable = [
        'user_id',
        'staff_code',
        'vehicle_type',
        'vehicle_plate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedCargoes()
    {
        return $this->hasMany(Cargo::class);
    }
}

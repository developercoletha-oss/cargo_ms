<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    protected $fillable = [
        'customer_id',
        'transport_staff_id',
        'origin_country',
        'origin_city',
        'origin_address',
        'destination_country',
        'destination_city',
        'destination_address',
        'pickup_date',
        'delivery_date',
        'status',
        'approval_note',
        'approved_by',
        'approved_at',
        'disapproved_by',
        'disapproved_at',
        'signed_by_transporter',
        'signed_at',
        'handover_confirmed_by',
        'handover_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'delivery_date' => 'date',
            'approved_at' => 'datetime',
            'disapproved_at' => 'datetime',
            'signed_at' => 'datetime',
            'handover_confirmed_at' => 'datetime',
        ];
    }
//relationship
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function detail()
    {
        return $this->hasOne(CargoDetail::class);
    }

    public function transportStaff()
    {
        return $this->belongsTo(TransportStaff::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function signedTransporter()
    {
        return $this->belongsTo(User::class, 'signed_by_transporter');
    }

    public function handoverConfirmer()
    {
        return $this->belongsTo(User::class, 'handover_confirmed_by');
    }
}

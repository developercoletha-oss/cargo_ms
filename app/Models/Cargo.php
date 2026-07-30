<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DISAPPROVED = 'disapproved';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_ARRIVED_REGIONAL_HUB = 'arrived_regional_hub';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_DELIVERED = 'delivered';

    public const TRANSPORT_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_IN_TRANSIT,
        self::STATUS_ARRIVED_REGIONAL_HUB,
        self::STATUS_ARRIVED,
        self::STATUS_DELIVERED,
    ];

    protected $fillable = [
        'tracking_number',
        'customer_id',
        'transport_staff_id',
        'origin_country',
        'origin_city',
        'origin_address',
        'destination_country',
        'destination_city',
        'destination_address',
        'current_location_city',
        'current_location_lat',
        'current_location_lng',
        'current_location_updated_at',
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
            'current_location_lat' => 'decimal:7',
            'current_location_lng' => 'decimal:7',
            'current_location_updated_at' => 'datetime',
            'approved_at' => 'datetime',
            'disapproved_at' => 'datetime',
            'signed_at' => 'datetime',
            'handover_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cargo $cargo) {
            if (! $cargo->tracking_number) {
                $cargo->tracking_number = self::generateTrackingNumber();
            }
        });
    }

    public static function generateTrackingNumber(): string
    {
        do {
            $trackingNumber = 'CFTMS-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (self::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DISAPPROVED => 'Disapproved',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_ARRIVED_REGIONAL_HUB => 'Arrived at Regional Hub',
            self::STATUS_ARRIVED => 'Arrived',
            self::STATUS_DELIVERED => 'Delivered',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED, self::STATUS_IN_TRANSIT => 'success',
            self::STATUS_ARRIVED_REGIONAL_HUB => 'info',
            self::STATUS_DELIVERED => 'primary',
            self::STATUS_ARRIVED => 'info',
            self::STATUS_DISAPPROVED => 'danger',
            default => 'warning',
        };
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

    public function locationUpdates()
    {
        return $this->hasMany(CargoLocationUpdate::class);
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

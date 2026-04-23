<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'service_id',
        'patient_id',
        'date',
        'hour',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->reference_id = self::generateUniqueReference();
        });
    }

    private static function generateUniqueReference(): string
    {
        do {
            $code = 'SB-' . mt_rand(10000000, 99999999);
        } while (self::where('reference_id', $code)->exists());

        return $code;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

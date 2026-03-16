<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    
    protected $fillable = ['reference_id', 'patient_id', 'specialist_id', 'date', 'hour', 'reason', 'status'];

    protected $casts = [
        'date' => 'date',
    ];

    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function specialist() {
        return $this->belongsTo(Specialist::class);
    }
    protected static function boot()
    {
        parent::boot();

        // Se ejecuta justo antes de crear el registro en la BD
        static::creating(function ($appointment) {
            $appointment->reference_id = self::generateUniqueReference();
        });
    }

    private static function generateUniqueReference()
    {
        do {
            // Genera: A- y 8 números aleatorios (Ej: A-58293041)
            $code = 'A-' . mt_rand(10000000, 99999999);
        } while (self::where('reference_id', $code)->exists()); // Verifica que no se repita

        return $code;
    }
}

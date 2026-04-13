<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistCustomAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialist_id',
        'date',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }
}
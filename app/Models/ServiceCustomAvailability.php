<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCustomAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'date',
        'start_time',
        'end_time',
        'capacity_override',
        'is_available',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_available' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

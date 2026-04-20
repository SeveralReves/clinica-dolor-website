<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'day', 'start_time', 'end_time', 'capacity'];

    protected $casts = ['capacity' => 'integer'];

    protected $appends = ['day_label'];

    public function getDayLabelAttribute(): string
    {
        return [
            'monday'    => 'Lunes',
            'tuesday'   => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday'  => 'Jueves',
            'friday'    => 'Viernes',
            'saturday'  => 'Sábado',
            'sunday'    => 'Domingo',
        ][$this->day] ?? $this->day;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

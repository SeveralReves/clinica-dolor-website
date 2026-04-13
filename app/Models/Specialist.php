<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'specialty', 'email', 'phone', 'description', 'photo_path', 'is_active'];
    protected $appends = ['photo_url'];
    public function getPhotoUrlAttribute()
{
    if (!$this->photo_path) {
        return asset('images/placeholder-doctor.png'); // Una imagen por defecto
    }
    
    // Si la ruta ya es una URL (por el seeder viejo), la devuelve, 
    // si no, construye la ruta al storage.
    return str_starts_with($this->photo_path, 'http') 
        ? $this->photo_path 
        : asset('storage/' . $this->photo_path);
}
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($specialist) {
            $specialist->slug = Str::slug($specialist->name);
        });
    }

    public function schedules()
    {
        return $this->hasMany(SpecialistSchedule::class);
    }
    /**
     * Horarios semanales recurrentes (Lunes, Martes...)
     */
    public function regularSchedules()
    {
        return $this->hasMany(SpecialistSchedule::class);
    }

    /**
     * Horarios o bloqueos para fechas específicas
     */
    public function customAvailabilities()
    {
        return $this->hasMany(SpecialistCustomAvailability::class);
    }

    /**
     * Método de ayuda para obtener la disponibilidad de un día concreto
     */
    public function getAvailabilityForDate($date)
    {
        // 1. Prioridad: ¿Hay algo específico para esta fecha?
        $custom = $this->customAvailabilities()->where('date', $date)->get();
        
        if ($custom->isNotEmpty()) {
            // Si el primer registro dice que no está disponible, retornamos vacío
            if (!$custom->first()->is_available) return collect();
            return $custom;
        }

        // 2. Si no hay nada específico, buscamos el horario regular
        $dayName = strtolower(date('l', strtotime($date))); // 'monday', 'tuesday'...
        return $this->regularSchedules()->where('day', $dayName)->get();
    }
}

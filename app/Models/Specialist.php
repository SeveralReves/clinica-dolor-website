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
}

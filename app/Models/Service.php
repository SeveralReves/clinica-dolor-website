<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'photo_path', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected $appends = ['photo_url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($service) {
            $service->slug = Str::slug($service->title);
        });
    }

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo_path) {
            return asset('images/placeholder-doctor.png');
        }
        return str_starts_with($this->photo_path, 'http')
            ? $this->photo_path
            : asset('storage/' . $this->photo_path);
    }

    public function schedules()
    {
        return $this->hasMany(ServiceSchedule::class)->orderBy('day')->orderBy('start_time');
    }
}

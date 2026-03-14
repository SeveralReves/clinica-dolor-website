<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'specialty', 'email', 'phone', 'description', 'photo_path', 'is_active'];

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

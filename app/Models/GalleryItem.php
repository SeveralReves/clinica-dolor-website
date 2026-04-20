<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'type',
        'file_path', 'video_url', 'thumbnail_path',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['media_url', 'thumb_url'];

    public function getMediaUrlAttribute(): string
    {
        if ($this->type === 'video') {
            return $this->video_url ?? '';
        }
        return $this->file_path ? asset('storage/' . $this->file_path) : '';
    }

    public function getThumbUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        if ($this->type === 'video' && $this->video_url) {
            return $this->extractYouTubeThumbnail($this->video_url) ?? '';
        }
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return '';
    }

    // Extract YouTube embed URL for GLightbox
    public function getEmbedUrlAttribute(): string
    {
        if ($this->type !== 'video' || !$this->video_url) return '';
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $this->video_url, $m);
        return isset($m[1]) ? "https://www.youtube.com/embed/{$m[1]}" : $this->video_url;
    }

    private function extractYouTubeThumbnail(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $m);
        return isset($m[1]) ? "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg" : null;
    }
}

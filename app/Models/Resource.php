<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'type',
        'description',
        'category',
        'tags',
        'uploaded_by',
        'download_count',
        'last_accessed_at'
    ];

    protected $casts = [
        'tags' => 'array',
        'last_accessed_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper to get full URL
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    // Helper to format file size
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

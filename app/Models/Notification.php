<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'upgrade_shipped' => 'zap',
            'component_on_fire' => 'flame',
            'streak_milestone' => 'trophy',
            'weekly_reminder' => 'calendar',
            default => 'bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->type) {
            'upgrade_shipped' => 'success',
            'component_on_fire' => 'danger',
            'streak_milestone' => 'warning',
            'weekly_reminder' => 'info',
            default => 'default',
        };
    }
}

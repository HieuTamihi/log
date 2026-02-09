<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'subsystem_id',
        'name',
        'slug',
        'description',
        'icon',
        'health_status',
        'current_issue',
        'metric_value',
        'metric_label',
        'order',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subsystem()
    {
        return $this->belongsTo(Subsystem::class);
    }

    public function upgrades()
    {
        return $this->hasMany(Upgrade::class)->orderBy('created_at', 'desc');
    }

    public function activeUpgrades()
    {
        return $this->hasMany(Upgrade::class)->where('status', 'active');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->health_status) {
            'smooth' => '#10b981',
            'on_fire' => '#ef4444',
            'needs_love' => '#fbbf24',
            default => '#6b7280',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->health_status) {
            'smooth' => '✅',
            'on_fire' => '🔥',
            'needs_love' => '💛',
            default => '⚪',
        };
    }
}

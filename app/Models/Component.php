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
        'content',
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

    public function getHealthStatusAttribute($value): string
    {
        return match($value) {
            'smooth', 'green' => 'green',
            'on_fire', 'red' => 'red',
            'needs_love', 'yellow' => 'yellow',
            default => 'green',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->health_status) {
            'green', 'smooth' => '#10b981',
            'red', 'on_fire' => '#ef4444',
            'yellow', 'needs_love' => '#fbbf24',
            default => '#6b7280',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->health_status) {
            'green', 'smooth' => '🟢', // Green Circle
            'red', 'on_fire' => '🔴', // Red Circle
            'yellow', 'needs_love' => '🟡', // Yellow Circle
            default => '⚪',
        };
    }
}

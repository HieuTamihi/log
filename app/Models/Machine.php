<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'header',
        'sub_header',
        'description',
        'detail_description',
        'icon',
        'color',
        'health_status',
        'order',
        'user_id',
        'position_x',
        'position_y',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subsystems()
    {
        return $this->hasMany(Subsystem::class)->orderBy('order');
    }

    public function components()
    {
        return $this->hasManyThrough(Component::class, Subsystem::class);
    }

    public function getHealthStatusAttribute(): string
    {
        // Check for manual status first
        if (!empty($this->attributes['health_status']) && $this->attributes['health_status'] !== 'auto') {
            return $this->attributes['health_status'];
        }

        $components = $this->components;
        
        if ($components->isEmpty()) {
            return 'yellow'; // Default to warning/needs_love if empty
        }

        // Check for critical statuses first
        $redCount = $components->filter(function ($component) {
            return in_array($component->health_status, ['red', 'on_fire']);
        })->count();

        if ($redCount > 0) {
            return 'red';
        }

        // Check for warning statuses
        $yellowCount = $components->filter(function ($component) {
            return in_array($component->health_status, ['yellow', 'needs_love']);
        })->count();

        if ($yellowCount > 0) {
            return 'yellow';
        }

        return 'green';
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
            'green', 'smooth' => '🟢',
            'red', 'on_fire' => '🔴',
            'yellow', 'needs_love' => '🟡',
            default => '⚪',
        };
    }
}

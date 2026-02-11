<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'health_status',
        'order',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function components()
    {
        return $this->hasMany(Component::class)->orderBy('order');
    }

    public function getHealthStatusAttribute(): string
    {
        // Check for manual status first
        if (!empty($this->attributes['health_status']) && $this->attributes['health_status'] !== 'auto') {
            return $this->attributes['health_status'];
        }

        $components = $this->components;
        
        if ($components->isEmpty()) {
            return 'yellow';
        }

        $redCount = $components->filter(function ($component) {
            return in_array($component->health_status, ['red', 'on_fire']);
        })->count();

        if ($redCount > 0) {
            return 'red';
        }

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

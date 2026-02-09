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
        'description',
        'icon',
        'color',
        'order',
        'user_id',
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
        $components = $this->components;
        
        if ($components->isEmpty()) {
            return 'needs_love';
        }

        $onFireCount = $components->where('health_status', 'on_fire')->count();
        $needsLoveCount = $components->where('health_status', 'needs_love')->count();

        if ($onFireCount > 0) {
            return 'on_fire';
        }

        if ($needsLoveCount > 0) {
            return 'needs_love';
        }

        return 'smooth';
    }
}

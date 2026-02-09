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

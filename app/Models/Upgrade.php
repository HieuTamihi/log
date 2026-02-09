<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upgrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'user_id',
        'name',
        'purpose',
        'trigger',
        'steps',
        'definition_of_done',
        'status',
        'shipped_at',
    ];

    protected $casts = [
        'steps' => 'array',
        'shipped_at' => 'datetime',
    ];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isShipped(): bool
    {
        return $this->status === 'active' && $this->shipped_at !== null;
    }

    public function ship()
    {
        $this->update([
            'status' => 'active',
            'shipped_at' => now(),
        ]);

        $this->updateUserStreak();
        $this->createShipNotification();
    }

    protected function createShipNotification()
    {
        Notification::create([
            'user_id' => $this->user_id,
            'type' => 'upgrade_shipped',
            'title' => '🎉 Upgrade Shipped!',
            'message' => "You shipped \"{$this->name}\" for {$this->component->name}",
            'data' => [
                'upgrade_id' => $this->id,
                'component_id' => $this->component_id,
                'component_name' => $this->component->name,
            ],
        ]);
    }

    protected function updateUserStreak()
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $this->user_id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_upgrades_shipped' => 0,
            ]
        );

        $streak->increment('total_upgrades_shipped');

        if ($streak->last_ship_date && $streak->last_ship_date->diffInDays(now()) <= 7) {
            $streak->increment('current_streak');
        } else {
            $streak->current_streak = 1;
        }

        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_ship_date = now();
        $streak->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_ship_date',
        'total_upgrades_shipped',
    ];

    protected $casts = [
        'last_ship_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStreakTextAttribute(): string
    {
        $weeks = $this->current_streak;
        
        if ($weeks === 0) {
            return 'Start your streak!';
        }

        if ($weeks === 1) {
            return '1 week shipping upgrades';
        }

        return "{$weeks} weeks shipping upgrades";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'version',
        'status',
        'user_id',
    ];

    /**
     * Get the user who created this log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the solution for this log.
     */
    public function solution()
    {
        return $this->hasOne(Solution::class);
    }

    /**
     * Check if this log has a solution.
     */
    public function hasSolution(): bool
    {
        return $this->solution()->exists();
    }

    /**
     * Get status label in Vietnamese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed',
            default => $this->status,
        };
    }

    /**
     * Get status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'var(--danger-color)',
            'in_progress' => 'var(--warning-color)',
            'closed' => 'var(--success-color)',
            default => 'var(--text-muted)',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solution extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_id',
        'name',
        'content',
        'version',
        'status',
        'user_id',
    ];

    /**
     * Get the log this solution belongs to.
     */
    public function log()
    {
        return $this->belongsTo(Log::class);
    }

    /**
     * Get the user who created this solution.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the history of this solution.
     */
    public function history()
    {
        return $this->hasMany(SolutionHistory::class)->orderBy('changed_at', 'desc');
    }

    /**
     * Save current state to history before updating.
     */
    public function saveToHistory(): void
    {
        SolutionHistory::create([
            'solution_id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'version' => $this->version,
            'status' => $this->status,
            'user_id' => $this->user_id,
        ]);
    }

    /**
     * Get status label in Vietnamese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Bản nháp',
            'testing' => 'Đang kiểm tra',
            'done' => 'Hoàn thành',
            default => $this->status,
        };
    }
}

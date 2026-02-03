<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolutionHistory extends Model
{
    use HasFactory;

    protected $table = 'solution_history';
    
    public $timestamps = false;

    protected $fillable = [
        'solution_id',
        'name',
        'content',
        'version',
        'status',
        'user_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the solution this history belongs to.
     */
    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }

    /**
     * Get the user who made this change.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteTab extends Model
{
    protected $fillable = ['note_id', 'name', 'content', 'order'];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteAttachment extends Model
{
    protected $fillable = ['note_id', 'filename', 'original_filename', 'mime_type', 'size', 'path', 'type', 'uploaded_by'];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

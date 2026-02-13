<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteCard extends Model
{
    protected $fillable = ['note_id', 'folder_id', 'user_id', 'position_x', 'position_y', 'zoom_level'];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function linkedNotes()
    {
        return $this->belongsToMany(Note::class, 'card_links', 'card_id', 'linked_note_id');
    }

    public function linkedFolders()
    {
        return $this->belongsToMany(Folder::class, 'card_links', 'card_id', 'linked_folder_id');
    }
}

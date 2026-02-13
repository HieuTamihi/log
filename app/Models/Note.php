<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['name', 'content', 'folder_id', 'user_id', 'description', 'manager_id', 'status', 'current_version'];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function versions()
    {
        return $this->hasMany(NoteVersion::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function cards()
    {
        return $this->hasMany(NoteCard::class);
    }

    public function linkedFromCards()
    {
        return $this->belongsToMany(NoteCard::class, 'card_links', 'linked_note_id', 'card_id');
    }
}

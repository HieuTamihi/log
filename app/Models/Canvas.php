<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canvas extends Model
{
    protected $fillable = ['name', 'description', 'user_id', 'folder_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function cards()
    {
        return $this->hasMany(NoteCard::class);
    }
}

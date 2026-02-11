<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineConnection extends Model
{
    protected $fillable = [
        'from_machine_id',
        'to_machine_id',
        'user_id',
        'label',
        'color',
    ];

    public function fromMachine()
    {
        return $this->belongsTo(Machine::class, 'from_machine_id');
    }

    public function toMachine()
    {
        return $this->belongsTo(Machine::class, 'to_machine_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

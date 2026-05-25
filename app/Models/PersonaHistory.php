<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonaHistory extends Model
{
    protected $fillable = [
        'persona_id',
        'old_instruction',
        'new_instruction',
        'source_chat_id',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}

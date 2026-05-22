<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $fillable = [
        'name',
        'prompt_instruction',
    ];

    public function chatLanguages(): HasMany
    {
        return $this->hasMany(ChatLanguage::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatLanguage extends Model
{
    protected $fillable = [
        'chat_id',
        'chat_name',
        'language_code',
        'language_name',
        'is_manual',
        'address_form',
        'persona_id',
        'ai_enabled',
        'learning_enabled',
    ];

    protected $casts = [
        'chat_id' => 'integer',
        'is_manual' => 'boolean',
        'persona_id' => 'integer',
        'ai_enabled' => 'boolean',
        'learning_enabled' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public static function forChat(int|string $chatId): ?self
    {
        return static::where('chat_id', $chatId)->first();
    }

    public static function setForChat(
        int|string $chatId,
        string $code,
        string $name,
        bool $manual = false,
        ?string $chatName = null
    ): self {
        $attributes = ['language_code' => $code, 'language_name' => $name, 'is_manual' => $manual];

        if ($chatName !== null) {
            $attributes['chat_name'] = $chatName;
        }

        return static::updateOrCreate(['chat_id' => $chatId], $attributes);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessConnection extends Model
{
    protected $fillable = [
        'connection_id',
        'telegram_user_id',
        'user_chat_id',
        'can_reply',
        'is_enabled',
    ];

    protected $casts = [
        'can_reply' => 'boolean',
        'is_enabled' => 'boolean',
        'telegram_user_id' => 'integer',
        'user_chat_id' => 'integer',
    ];
}

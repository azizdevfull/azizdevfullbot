<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_id', 'role', 'content', 'is_manual'];

    protected $casts = [
        'chat_id' => 'integer',
        'is_manual' => 'boolean',
    ];

    /** @return Collection<int, self> */
    public static function getHistory(int|string $chatId, int $limit = 20): Collection
    {
        return static::where('chat_id', $chatId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public static function cleanupOld(int|string $chatId, int $keep = 30): void
    {
        $keepIds = static::where('chat_id', $chatId)
            ->orderByDesc('id')
            ->limit($keep)
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            static::where('chat_id', $chatId)
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['chat_id', 'role', 'content'];

    protected $casts = [
        'chat_id' => 'integer',
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
        $ids = static::where('chat_id', $chatId)
            ->orderByDesc('id')
            ->skip($keep)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            static::whereIn('id', $ids)->delete();
        }
    }
}

<?php

namespace App\Services;

use App\Ai\Agents\LanguageDetectionAgent;
use App\Models\ChatLanguage;
use Illuminate\Support\Facades\Log;

class LanguageDetector
{
    /** @return array{code: string, name: string} */
    public static function detect(string $text): array
    {
        $text = trim($text);

        if (mb_strlen($text) < 3) {
            return ['code' => 'uz', 'name' => "O'zbek"];
        }

        try {
            $agent = new LanguageDetectionAgent;
            $response = $agent->prompt($text);
            $result = json_decode($response->text, true);

            if (isset($result['code'], $result['name'])) {
                return ['code' => $result['code'], 'name' => $result['name']];
            }
        } catch (\Throwable $e) {
            Log::channel('telegram')->warning('Language detection failed', ['error' => $e->getMessage()]);
        }

        return ['code' => 'uz', 'name' => "O'zbek"];
    }

    public static function detectAndSave(
        int|string $chatId,
        string $text,
        ?string $chatName = null
    ): ChatLanguage {
        $existing = ChatLanguage::forChat($chatId);

        if ($chatName !== null && $existing) {
            $existing->update(['chat_name' => $chatName]);
        }

        if ($existing?->is_manual) {
            return $existing->fresh();
        }

        $detected = static::detect($text);

        return ChatLanguage::setForChat($chatId, $detected['code'], $detected['name'], false, $chatName);
    }
}

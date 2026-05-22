<?php

namespace App\Jobs;

use App\Ai\Agents\TelegramAssistant;
use App\Models\ChatMessage;
use App\Models\Persona;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefinePersonaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $personaId
    ) {}

    public function handle(): void
    {
        $persona = Persona::find($this->personaId);

        if (! $persona) {
            return;
        }

        // Get manual messages related to this persona
        $manualMessages = ChatMessage::where('is_manual', true)
            ->whereIn('chat_id', function ($query) {
                $query->select('chat_id')
                    ->from('chat_languages')
                    ->where('persona_id', $this->personaId);
            })
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        if ($manualMessages->count() < 5) {
            return; // Not enough data to refine
        }

        $examples = '';
        foreach ($manualMessages as $manualMsg) {
            // Find the message immediately preceding this manual reply in the same chat
            $previousMsg = ChatMessage::where('chat_id', $manualMsg->chat_id)
                ->where('id', '<', $manualMsg->id)
                ->where('role', 'user')
                ->orderByDesc('id')
                ->first();

            if ($previousMsg) {
                $examples .= "Mijoz: \"{$previousMsg->content}\"\n";
                $examples .= "Azizbek: \"{$manualMsg->content}\"\n";
                $examples .= "---\n";
            } else {
                $examples .= "Azizbek (kontekstsiz): \"{$manualMsg->content}\"\n";
                $examples .= "---\n";
            }
        }

        $prompt = "Siz Azizbek Isroilovning shaxsiy AI yordamchisisiz. 
Sizning vazifangiz Azizbekning quyidagi real yozishmalaridan namuna olib, uning '{$persona->name}' personasi uchun yo'riqnomani (system prompt) yangilash.

Azizbekning real yozishmalari (Mijoz savoli va Azizbekning javobi):
{$examples}

Hozirgi yo'riqnoma:
{$persona->prompt_instruction}

Yangi yo'riqnoma quyidagilarni o'z ichiga olishi kerak:
1. Azizbekning o'ziga xos so'z boyligi va iboralari.
2. Emojilardan foydalanish chastotasi va uslubi.
3. Mijozning savollariga qanday ohangda (rasmiy, do'stona, qisqa yoki batafsil) javob berishi.
4. Gap tuzilishi va punktuatsiyaga munosabati.

Faqat yangi yo'riqnoma matnini (system prompt) qaytaring. Hech qanday qo'shimcha tushuntirish yozmang.";

        try {
            $assistant = new TelegramAssistant(
                meModeEnabled: true,
                language: 'uzbek'
            );

            $response = $assistant->prompt($prompt);
            $newInstruction = $response->text;

            if (! empty($newInstruction)) {
                $persona->update(['prompt_instruction' => $newInstruction]);
                Log::channel('telegram')->info("Persona '{$persona->name}' refined successfully.");
            }
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('Persona refinement failed: '.$e->getMessage());
        }
    }
}

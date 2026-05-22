<?php

namespace App\Ai\Agents;

use App\Models\BotSetting;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-flash-lite-latest')]
#[Temperature(0.7)]
class TelegramAssistant implements Agent, Conversational
{
    use Promptable;

    public function __construct(
        public readonly bool $meModeEnabled = false,
        public readonly ?string $language = null,
        public readonly string $addressForm = 'siz',
        public readonly ?string $personaInstruction = null,
        public readonly array $conversationHistory = [],
    ) {}

    public function instructions(): Stringable|string
    {
        $base = $this->meModeEnabled
            ? BotSetting::get('me_mode_instructions', config('telegram.me_mode_instructions'))
            : BotSetting::get('ai_instructions', config('telegram.ai_instructions'));

        $extras = [];

        if ($this->language) {
            $extras[] = "Har doim faqat {$this->language} tilida javob ber. Boshqa tilda yozma.";
        }

        $form = $this->addressForm === 'sen' ? '"sen" shaklida' : '"siz" shaklida';
        $extras[] = "Suhbatdoshga {$form} murojaat qil.";

        if ($this->personaInstruction) {
            $extras[] = 'MUHIM QOIDA (Suhbatdosh xarakteri): '.$this->personaInstruction;
        }

        return $base."\n\nMUHIM:\n- ".implode("\n- ", $extras);
    }

    public function messages(): iterable
    {
        return array_map(
            fn ($m) => new Message($m['role'], $m['content']),
            $this->conversationHistory
        );
    }
}

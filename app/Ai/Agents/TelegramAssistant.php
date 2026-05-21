<?php

namespace App\Ai\Agents;

use App\Models\BotSetting;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-flash-lite-latest')]
#[Temperature(0.7)]
class TelegramAssistant implements Agent
{
    use Promptable;

    public function __construct(
        public readonly bool $meModeEnabled = false,
        public readonly ?string $language = null,
        public readonly string $addressForm = 'siz',
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

        return $base."\n\nMUHIM:\n- ".implode("\n- ", $extras);
    }
}
